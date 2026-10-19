import 'server-only';
import type { SupabaseClient } from '@supabase/supabase-js';
import type { RecordRow } from './types';
export type Filters = { class_id?: string; from?: string; to?: string };
export const formatTime = (value: string) => new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short', timeZone: 'Asia/Jakarta' }).format(new Date(value));
export function checked<T>(result: { data: T | null; error: { message: string } | null }): T {
  if (result.error) throw new Error(result.error.message);
  return result.data as T;
}
export async function records(db: SupabaseClient, filters: Filters = {}) {
  let query = db.from('attendance_report').select('*').order('starts_at', { ascending: false }).order('session_id').order('student_id');
  if (filters.class_id && /^\d+$/.test(filters.class_id)) query = query.eq('class_id', filters.class_id);
  if (filters.from && /^\d{4}-\d{2}-\d{2}$/.test(filters.from)) query = query.gte('starts_at', `${filters.from}T00:00:00+07:00`);
  if (filters.to && /^\d{4}-\d{2}-\d{2}$/.test(filters.to)) query = query.lte('starts_at', `${filters.to}T23:59:59.999+07:00`);
  // Supabase REST defaults to 1000 rows; paginate for complete exports.
  const rows: RecordRow[] = [];
  for (let offset = 0; ; offset += 1000) {
    const batch = checked(await query.range(offset, offset + 999)) as RecordRow[];
    rows.push(...batch);
    if (batch.length < 1000) return rows;
  }
}
