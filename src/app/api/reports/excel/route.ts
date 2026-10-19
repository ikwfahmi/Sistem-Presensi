import { NextResponse, type NextRequest } from 'next/server';
import { apiUser } from '@/lib/api';
import { records } from '@/lib/data';
import { excelReport } from '@/lib/excel';
export const runtime = 'nodejs';
export async function GET(request: NextRequest) {
  const auth = await apiUser();
  if (!auth || !['admin', 'baak', 'dosen'].includes(auth.role)) return NextResponse.json({ message: 'Akses ditolak' }, { status: 403 });
  const rows = await records(auth.db, Object.fromEntries(request.nextUrl.searchParams));
  return new Response(await excelReport(rows), { headers: { 'Content-Type': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'Content-Disposition': 'attachment; filename="laporan-presensi.xlsx"', 'Cache-Control': 'private, no-store' } });
}
