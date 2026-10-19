import { requireUser } from '@/lib/auth';
import { checked, records, type Filters } from '@/lib/data';
import type { Class } from '@/lib/types';
import { Heading, RecordsTable } from '@/components/ui';
import { PrintButton } from '@/components/live';
export default async function Reports({ searchParams }: { searchParams: Promise<Filters> }) {
  const { db, profile } = await requireUser();
  const filters = await searchParams;
  const [rows, cr] = await Promise.all([records(db, filters), db.from('classes').select('*').order('name')]);
  const classes = checked(cr) as Class[];
  const query = new URLSearchParams();
  for (const key of ['class_id', 'from', 'to'] as const) if (typeof filters[key] === 'string') query.set(key, filters[key]!);
  return <><Heading title={profile.role === 'mahasiswa' ? 'Riwayat presensi' : 'Laporan presensi'} subtitle="Rekap peserta termasuk yang belum presensi dan tidak hadir."/>
    <section className="panel"><form className="filters"><label>Kelas<select name="class_id" defaultValue={filters.class_id ?? ''}><option value="">Semua kelas</option>{classes.map(c => <option key={c.id} value={c.id}>{c.code} · {c.name}</option>)}</select></label><label>Dari tanggal (WIB)<input type="date" name="from" defaultValue={filters.from}/></label><label>Sampai tanggal (WIB)<input type="date" name="to" defaultValue={filters.to}/></label><button>Terapkan filter</button></form>
      <div className="toolbar">{profile.role !== 'mahasiswa' && <><a className="button secondary" href={`/api/reports/excel?${query}`}>Unduh Excel (.xlsx)</a><PrintButton/></>}<span className="muted">{rows.length} data · {rows.filter(r => r.status === 'Hadir').length} hadir</span></div><RecordsTable rows={rows}/>
    </section></>;
}
