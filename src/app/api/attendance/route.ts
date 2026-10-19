import { NextResponse, type NextRequest } from 'next/server';
import { apiUser, sameOrigin } from '@/lib/api';
import { gpsInput } from '@/lib/validation';
export async function POST(request: NextRequest) {
  if (!sameOrigin(request)) return NextResponse.json({ ok: false, message: 'Origin ditolak' }, { status: 403 });
  const auth = await apiUser();
  if (!auth || auth.role !== 'mahasiswa') return NextResponse.json({ ok: false, message: 'Akses ditolak' }, { status: 403 });
  const parsed = gpsInput.safeParse(await request.json().catch(() => null));
  if (!parsed.success) return NextResponse.json({ ok: false, message: 'Data lokasi tidak valid' }, { status: 400 });
  const input = parsed.data;
  const { data, error } = await auth.db.rpc('submit_attendance', { p_session: input.session_id, p_token: input.token, p_lat: input.latitude, p_lng: input.longitude, p_accuracy: input.accuracy, p_timestamp: input.timestamp, p_mock: input.mock ?? false });
  return NextResponse.json(error ? { ok: false, message: error.message } : data, { status: error || !data?.ok ? 400 : 200, headers: { 'Cache-Control': 'no-store' } });
}
