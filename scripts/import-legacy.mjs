// Run only against a new/empty Supabase project after 001_initial.sql.
// Import can be resumed; existing users are matched by identifier, rows by original ID.
import { readFile } from 'node:fs/promises';
import { randomBytes } from 'node:crypto';
import { createClient } from '@supabase/supabase-js';
const db = createClient(process.env.NEXT_PUBLIC_SUPABASE_URL, process.env.SUPABASE_SERVICE_ROLE_KEY, { auth: { persistSession: false, autoRefreshToken: false } });
const source = JSON.parse(await readFile(new URL('../migration-data.json', import.meta.url), 'utf8'));
const mapping = new Map();
function check(result) { if (result.error) throw result.error; return result.data; }
// Preflight all identifiers before creating any accounts.
const normalized = source.users.map(u => String(u.identifier).trim().toLowerCase());
if (normalized.some(v => !/^[a-z0-9._-]{1,80}$/.test(v)) || new Set(normalized).size !== normalized.length) throw new Error('Identitas lama tidak valid atau bentrok setelah diubah menjadi huruf kecil. Perbaiki JSON sebelum impor.');
for (const user of source.users) {
  const identifier = String(user.identifier).trim().toLowerCase();
  const existing = check(await db.from('profiles').select('id').eq('identifier', identifier).maybeSingle());
  if (existing) { mapping.set(user.id, existing.id); continue; }
  const password = randomBytes(32).toString('base64url');
  const { user: created } = check(await db.auth.admin.createUser({ email: `${identifier}@presensi.invalid`, password, email_confirm: true }));
  const result = await db.from('profiles').insert({ id: created.id, identifier, name: user.name, role: user.role });
  if (result.error) { await db.auth.admin.deleteUser(created.id); throw result.error; }
  mapping.set(user.id, created.id);
}
const wib = value => new Date(`${value.replace(' ', 'T')}+07:00`).toISOString();
async function insert(table, rows, conflict = 'id') {
  for (let i = 0; i < rows.length; i += 200) check(await db.from(table).upsert(rows.slice(i, i + 200), { onConflict: conflict }));
}
await insert('classes', source.classes.map(c => ({ ...c, lecturer_id: mapping.get(c.lecturer_id) })));
await insert('enrollments', source.enrollments.map(e => ({ ...e, student_id: mapping.get(e.student_id) })), 'class_id,student_id');
await insert('sessions', source.sessions.map(s => ({ ...s, closed: !!s.closed, starts_at: wib(s.starts_at), ends_at: wib(s.ends_at) })));
await insert('attendance', source.attendance.map(a => ({ ...a, student_id: mapping.get(a.student_id), recorded_at: wib(a.recorded_at) })));
await insert('attempts', source.attempts.filter(a => mapping.has(a.student_id) && source.sessions.some(s => s.id === a.session_id)).map(a => ({ ...a, student_id: mapping.get(a.student_id), occurred_at: wib(a.occurred_at) })));
console.log('Data berhasil diimpor. Jalankan supabase/reset-sequences.sql melalui SQL Editor.');
console.log('Password PHP tidak diimpor. Gunakan scripts/reset-password.mjs untuk mengatur password baru setiap akun, termasuk admin.');
