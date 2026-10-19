import { requireUser } from '@/lib/auth';
import { checked, formatTime } from '@/lib/data';
import type { Class, Session } from '@/lib/types';
import { createSession, closeSession } from '@/app/actions';
import { Heading, Notice } from '@/components/ui';
export default async function Sessions({ searchParams }: { searchParams: Promise<{ error?: string; message?: string }> }) {
  const { db } = await requireUser(['admin', 'dosen']);
  const [cr, sr] = await Promise.all([db.from('classes').select('*').order('name'), db.from('sessions').select('*').order('starts_at', { ascending: false })]);
  const classes = checked(cr) as Class[], sessions = checked(sr) as Session[];
  return <><Heading title="Sesi presensi" subtitle="Jadwal menggunakan WIB. Lokasi kelas disalin saat sesi dibuat."/><Notice params={await searchParams}/>
    <section className="panel"><h2>Buat sesi</h2><form action={createSession} className="form-grid">
      <label>Kelas<select name="class_id" required defaultValue=""><option value="" disabled>Pilih kelas</option>{classes.map(c => <option value={c.id} key={c.id}>{c.code} · {c.name}</option>)}</select></label>
      <label>Judul pertemuan<input name="title" required maxLength={160}/></label><label>Mulai (WIB)<input name="starts_at" type="datetime-local" required/></label><label>Selesai (WIB)<input name="ends_at" type="datetime-local" required/></label><div><button>Buat sesi</button></div>
    </form></section><section className="panel"><h2>Daftar sesi</h2><div className="table-wrap"><table><thead><tr><th>Kelas</th><th>Pertemuan</th><th>Jadwal WIB</th><th>Status</th><th>Aksi</th></tr></thead><tbody>{sessions.map(s => <tr key={s.id}><td>{classes.find(c => c.id === s.class_id)?.name}</td><td>{s.title}</td><td>{formatTime(s.starts_at)}<br/>{formatTime(s.ends_at)}</td><td>{s.closed ? 'Ditutup' : +new Date(s.ends_at) < Date.now() ? 'Berakhir' : +new Date(s.starts_at) > Date.now() ? 'Terjadwal' : 'Berlangsung'}</td><td>{!s.closed && <form action={closeSession}><input type="hidden" name="id" value={s.id}/><button className="secondary">Tutup sesi</button></form>}</td></tr>)}</tbody></table>{!sessions.length && <p className="empty">Belum ada sesi presensi.</p>}</div></section></>;
}
