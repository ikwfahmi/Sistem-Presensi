import { requireUser } from '@/lib/auth';
import { checked, formatTime } from '@/lib/data';
import { Heading } from '@/components/ui';
export default async function Security() {
  const { db } = await requireUser(['admin', 'dosen']);
  const rows = checked(await db.from('attempts').select('id,student_id,session_id,occurred_at,reason').order('occurred_at', { ascending: false }).limit(200));
  return <><Heading title="Log penolakan" subtitle="200 penolakan terakhir pada kelas yang dapat Anda kelola."/><section className="panel"><div className="table-wrap"><table><thead><tr><th>Waktu WIB</th><th>ID mahasiswa</th><th>Sesi</th><th>Alasan</th></tr></thead><tbody>{rows.map(r => <tr key={r.id}><td>{formatTime(r.occurred_at)}</td><td>{r.student_id}</td><td>{r.session_id}</td><td>{r.reason}</td></tr>)}</tbody></table>{!rows.length && <p className="empty">Belum ada penolakan tercatat.</p>}</div></section></>;
}
