import { NextResponse, type NextRequest } from 'next/server';
import { apiUser, sameOrigin } from '@/lib/api';
import { z } from 'zod';
export async function POST(request: NextRequest) {
  if (!sameOrigin(request)) return NextResponse.json({ message: 'Origin ditolak' }, { status: 403 });
  const auth = await apiUser();
  if (!auth || auth.role !== 'mahasiswa') return NextResponse.json({ message: 'Akses ditolak' }, { status: 403 });
  const parsed = z.object({ session_id: z.number().int().positive() }).safeParse(await request.json().catch(() => null));
  if (!parsed.success) return NextResponse.json({ message: 'Data sesi tidak valid' }, { status: 400 });
  const { data, error } = await auth.db.rpc('issue_challenge', { p_session: parsed.data.session_id });
  return NextResponse.json(error ? { message: error.message } : { token: data }, { status: error ? 400 : 200, headers: { 'Cache-Control': 'no-store' } });
}
