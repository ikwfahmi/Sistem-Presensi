import { formatTime } from '@/lib/data';
import type { RecordRow } from '@/lib/types';
export function Heading({ title, subtitle }: { title: string; subtitle?: string }) {
  return <header className="page-head"><div><p className="eyebrow">GEOPRESENSI / KAMPUS</p><h1>{title}</h1>{subtitle && <p className="muted">{subtitle}</p>}</div><span className="date">Waktu Indonesia Barat</span></header>;
}
export function Notice({ params }: { params: { error?: string; message?: string } }) {
  return params.error || params.message ? <div role="status" className={`notice ${params.error ? 'error' : ''}`}>{params.error || params.message}</div> : null;
}
export function RecordsTable({ rows }: { rows: RecordRow[] }) {
  return <div className="table-wrap"><table><thead><tr>{['Mahasiswa', 'Kelas / sesi', 'Jadwal (WIB)', 'Status', 'Waktu presensi', 'Jarak'].map(text => <th key={text}>{text}</th>)}</tr></thead>
    <tbody>{rows.map(row => <tr key={`${row.session_id}-${row.student_id}`}><td>{row.name}<br/><small className="muted">{row.identifier}</small></td><td>{row.code} · {row.class_name}<br/>{row.title}</td><td>{formatTime(row.starts_at)}</td><td><span className={`badge ${row.status !== 'Hadir' ? 'neutral' : ''}`}>{row.status}</span></td><td>{row.recorded_at ? formatTime(row.recorded_at) : '—'}</td><td>{row.distance === null ? '—' : `${Math.round(row.distance)} m`}</td></tr>)}</tbody></table>{!rows.length && <p className="empty">Belum ada data presensi.</p>}</div>;
}
