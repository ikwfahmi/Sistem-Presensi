'use server';

import { redirect } from 'next/navigation';
import { revalidatePath } from 'next/cache';
import { z } from 'zod';
import { requireUser } from '@/lib/auth';
import { supabase } from '@/lib/supabase/server';
import { adminClient } from '@/lib/supabase/admin';
import { classInput, loginEmail, roles, wibToIso } from '@/lib/validation';

const field = (form: FormData, key: string) => String(form.get(key) ?? '').trim();
const id = (value: string) => z.coerce.number().int().positive().parse(value);
function errorMessage(error: unknown) {
  if (error instanceof z.ZodError) return 'Data belum lengkap atau format tidak valid. Periksa kembali isian.';
  if (error && typeof error === 'object' && 'code' in error && error.code === '23505') return 'Kode atau identitas sudah digunakan.';
  if (error && typeof error === 'object' && 'message' in error && typeof error.message === 'string') return error.message;
  return 'Operasi gagal. Periksa data atau koneksi Supabase.';
}
function finish(path: string, message: string, failed = false): never {
  revalidatePath('/', 'layout');
  redirect(`${path}?${failed ? 'error' : 'message'}=${encodeURIComponent(message)}`);
}
export async function login(form: FormData) {
  let failed = false;
  try {
    const email = loginEmail(field(form, 'identifier'));
    const db = await supabase();
    const { error } = await db.auth.signInWithPassword({ email, password: String(form.get('password') ?? '') });
    failed = !!error;
  } catch { failed = true; }
  if (failed) redirect('/login?error=Identitas+atau+kata+sandi+salah,+atau+terlalu+banyak+percobaan.');
  redirect('/dashboard');
}
export async function logout() {
  const db = await supabase();
  await db.auth.signOut();
  redirect('/login');
}
export async function createUser(form: FormData) {
  await requireUser(['admin']);
  try {
    const name = z.string().min(1).max(160).parse(field(form, 'name'));
    const identifier = field(form, 'identifier').toLowerCase();
    const email = loginEmail(identifier);
    const role = z.enum(roles).parse(field(form, 'role'));
    const password = z.string().min(10).max(128).parse(String(form.get('password') ?? ''));
    const admin = adminClient();
    const { data, error } = await admin.auth.admin.createUser({ email, password, email_confirm: true });
    if (error) throw error;
    const { error: profileError } = await admin.from('profiles').insert({ id: data.user.id, name, identifier, role });
    if (profileError) {
      await admin.auth.admin.deleteUser(data.user.id);
      throw profileError;
    }
  } catch (error) { finish('/users', errorMessage(error), true); }
  finish('/users', 'Pengguna berhasil dibuat.');
}
export async function saveClass(form: FormData) {
  const { db, profile } = await requireUser(['admin', 'dosen']);
  try {
    const input = classInput.parse({ ...Object.fromEntries(form), lecturer_id: profile.role === 'dosen' ? profile.id : field(form, 'lecturer_id') });
    const { error } = await db.rpc('save_class', {
      p_id: field(form, 'id') ? id(field(form, 'id')) : null, p_code: input.code, p_name: input.name,
      p_lecturer: input.lecturer_id, p_room: input.room, p_lat: input.latitude, p_lng: input.longitude, p_radius: input.radius,
    });
    if (error) throw error;
  } catch (error) { finish('/classes', errorMessage(error), true); }
  finish('/classes', 'Kelas disimpan. Perubahan lokasi berlaku untuk sesi baru.');
}
export async function enrollStudent(form: FormData) {
  const { db } = await requireUser(['admin', 'dosen']);
  try {
    const { error } = await db.rpc('enroll_student', { p_class: id(field(form, 'class_id')), p_student: z.uuid().parse(field(form, 'student_id')) });
    if (error) throw error;
  } catch (error) { finish('/classes', errorMessage(error), true); }
  finish('/classes', 'Mahasiswa terdaftar.');
}
export async function createSession(form: FormData) {
  const { db } = await requireUser(['admin', 'dosen']);
  try {
    const start = wibToIso(field(form, 'starts_at'));
    const end = wibToIso(field(form, 'ends_at'));
    if (+new Date(end) <= +new Date(start) || +new Date(end) - +new Date(start) > 86400000) throw new Error('Durasi sesi harus lebih dari 0 dan maksimal 24 jam.');
    const { error } = await db.rpc('create_session', { p_class: id(field(form, 'class_id')), p_title: z.string().min(1).max(160).parse(field(form, 'title')), p_start: start, p_end: end });
    if (error) throw error;
  } catch (error) { finish('/sessions', errorMessage(error), true); }
  finish('/sessions', 'Sesi presensi dibuat.');
}
export async function closeSession(form: FormData) {
  const { db } = await requireUser(['admin', 'dosen']);
  try {
    const { error } = await db.rpc('close_session', { p_session: id(field(form, 'id')) });
    if (error) throw error;
  } catch (error) { finish('/sessions', errorMessage(error), true); }
  finish('/sessions', 'Sesi ditutup.');
}
export async function changePassword(form: FormData) {
  const { db, user } = await requireUser();
  try {
    const password = z.string().min(10).max(128).parse(String(form.get('password') ?? ''));
    const { error: verifyError } = await db.auth.signInWithPassword({ email: user.email!, password: String(form.get('old_password') ?? '') });
    if (verifyError) throw new Error('Kata sandi lama salah.');
    const { error } = await db.auth.updateUser({ password });
    if (error) throw error;
  } catch (error) { finish('/password', errorMessage(error), true); }
  finish('/password', 'Kata sandi diperbarui.');
}
