import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import { PGlite } from '@electric-sql/pglite';

test('PostgreSQL migration, RLS and attendance workflow', async t => {
  const db = new PGlite();
  const admin = '00000000-0000-4000-8000-000000000001';
  const lecturer = '00000000-0000-4000-8000-000000000002';
  const otherLecturer = '00000000-0000-4000-8000-000000000003';
  const student = '00000000-0000-4000-8000-000000000004';
  const otherStudent = '00000000-0000-4000-8000-000000000005';
  const baak = '00000000-0000-4000-8000-000000000006';
  const unprofiled = '00000000-0000-4000-8000-000000000007';
  const q = async <T extends Record<string, unknown>>(sql: string, params: unknown[] = []) => (await db.query<T>(sql, params)).rows;
  async function as(uid: string) {
    await db.exec('reset role');
    await q("select set_config('request.jwt.claim.sub',$1,false)", [uid]);
    await db.exec('set role authenticated');
  }
  async function challenge(session = 1) {
    return (await q<{ token: string }>('select public.issue_challenge($1) token', [session]))[0].token;
  }
  async function submit(token: string | null, options: { session?: number; lat?: number; accuracy?: number; timestamp?: number; mock?: boolean } = {}) {
    return (await q<{ result: { ok: boolean; message: string } }>('select public.submit_attendance($1,$2,$3,106.8,$4,$5,$6) result', [options.session ?? 1, token, options.lat ?? -6.2, options.accuracy ?? 5, options.timestamp ?? Date.now(), options.mock ?? false]))[0].result;
  }
  try {
    await db.exec(`
      create role anon; create role authenticated; create role service_role bypassrls;
      create schema auth; create table auth.users(id uuid primary key);
      create function auth.uid() returns uuid language sql stable as $$ select nullif(current_setting('request.jwt.claim.sub',true),'')::uuid $$;
      grant usage on schema public,auth to anon,authenticated,service_role;
    `);
    await db.exec(await readFile('supabase/migrations/001_initial.sql', 'utf8'));
    for (const [uid, name, role] of [[admin, 'Admin', 'admin'], [lecturer, 'Dosen', 'dosen'], [otherLecturer, 'Dosen lain', 'dosen'], [student, 'Mahasiswa', 'mahasiswa'], [otherStudent, 'Mahasiswa lain', 'mahasiswa'], [baak, 'BAAK', 'baak']]) {
      await q('insert into auth.users values($1)', [uid]);
      await q('insert into public.profiles values($1,$2,$3,$4)', [uid, name, role + uid.slice(-1), role]);
    }
    await q('insert into auth.users values($1)', [unprofiled]);
    await as(admin);
    await q("select public.save_class(null,'IF1','Informatika',$1,'A1',-6.2,106.8,50)", [lecturer]);
    await q("select public.save_class(null,'IF2','Kelas lain',$1,'A2',-6.2,106.8,50)", [otherLecturer]);
    await q('select public.enroll_student(1,$1)', [student]);
    await q('select public.enroll_student(1,$1)', [otherStudent]);
    await db.exec("select public.create_session(1,'Pertemuan 1',now()-interval '1 hour',now()+interval '1 hour'); select public.create_session(2,'Sesi lain',now()-interval '1 hour',now()+interval '1 hour');");

    await t.test('students cannot alter roles, write attendance directly, or read tokens', async () => {
      await as(student);
      await assert.rejects(() => db.exec("update public.profiles set role='admin'"), /permission denied/);
      await assert.rejects(() => db.exec('insert into public.attendance(session_id) values(1)'), /permission denied/);
      await assert.rejects(() => db.exec('select * from public.challenges'), /permission denied/);
      await assert.rejects(() => db.exec('select public.close_session(1)'), /Akses ditolak/);
      await assert.rejects(() => challenge(2), /bukan peserta/);
      assert.equal((await q('select * from public.classes')).length, 1);
      assert.equal((await q('select * from public.attendance_report')).length, 1);
    });
    await t.test('lecturers are isolated and session location is a snapshot', async () => {
      await as(otherLecturer);
      assert.equal((await q('select * from public.classes')).length, 1);
      await assert.rejects(() => db.exec('select public.close_session(1)'), /Akses ditolak/);
      await assert.rejects(() => q('select public.enroll_student(1,$1)', [student]), /Akses ditolak/);
      await as(lecturer);
      await q("select public.save_class(1,'IF1','Informatika',$1,'A1',-6.3,106.8,100)", [lecturer]);
      assert.equal((await q<{ latitude: number }>('select latitude from public.sessions where id=1'))[0].latitude, -6.2);
      await assert.rejects(() => q("select public.save_class(1,'IF1','X',$1,'A',-6.2,106.8,50)", [otherLecturer]), /kelas sendiri/);
    });
    await t.test('rejections consume token and persist audit log', async () => {
      await as(student);
      const token = await challenge();
      assert.equal((await submit(token, { lat: -6.21 })).ok, false);
      assert.match((await submit(token)).message, /Token/);
      assert.equal((await submit(await challenge(), { timestamp: Date.now() - 60000 })).ok, false);
      assert.equal((await submit(await challenge(), { accuracy: 51 })).ok, false);
      assert.equal((await submit(await challenge(), { mock: true })).ok, false);
      assert.equal((await submit(await challenge(), { lat: NaN })).ok, false);
      await challenge();
      assert.equal((await submit(null)).ok, false);
      const oldToken = await challenge();
      await db.exec('reset role');
      await db.exec("update public.challenges set created_at=now()-interval '61 seconds'");
      await as(student);
      assert.equal((await submit(oldToken)).ok, false);
      assert.equal((await q('select * from public.attendance')).length, 0);
      await as(lecturer);
      assert.equal((await q('select * from public.attempts')).length, 8);
    });
    await t.test('valid attendance, uniqueness, and private student history', async () => {
      await as(student);
      assert.equal((await submit(await challenge())).ok, true);
      assert.match((await submit(await challenge())).message, /sudah melakukan/);
      assert.equal((await q('select * from public.attendance')).length, 1);
      await as(otherStudent);
      assert.equal((await q('select * from public.attendance')).length, 0);
      const report = await q<{ status: string }>('select status from public.attendance_report');
      assert.equal(report.length, 1);
      assert.equal(report[0].status, 'Belum presensi');
    });
    await t.test('closed sessions reject existing token, reports include absences, BAAK is read only', async () => {
      await as(otherStudent);
      const token = await challenge();
      await as(lecturer);
      await db.exec('select public.close_session(1)');
      await as(otherStudent);
      assert.match((await submit(token)).message, /sudah berakhir/);
      await assert.rejects(() => challenge(), /sudah berakhir/);
      await as(baak);
      const rows = await q<{ status: string }>('select status from public.attendance_report order by status');
      assert.deepEqual(rows.map(r => r.status), ['Hadir', 'Tidak hadir']);
      await assert.rejects(() => db.exec('select public.close_session(2)'), /Akses ditolak/);
      await assert.rejects(() => q('select public.enroll_student(1,$1)', [student]), /Akses ditolak/);
    });
    await t.test('anonymous and unprovisioned auth users cannot access application data', async () => {
      await as(unprofiled);
      assert.equal((await q('select * from public.classes')).length, 0);
      await assert.rejects(() => challenge(), /Akses|bukan peserta/);
      await db.exec('reset role; set role anon');
      await assert.rejects(() => db.exec('select * from public.profiles'), /permission denied/);
      await assert.rejects(() => db.exec('select public.issue_challenge(1)'), /permission denied/);
    });
  } finally { await db.close(); }
});
