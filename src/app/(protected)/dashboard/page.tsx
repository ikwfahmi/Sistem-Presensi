import { requireUser } from '@/lib/auth';
import { checked, formatTime, records } from '@/lib/data';
import type { Class, Session } from '@/lib/types';
import { Heading, Notice, RecordsTable } from '@/components/ui';
import { AttendanceButton } from '@/components/gps';
import { LiveRefresh, Reminder } from '@/components/live';
export default async function Dashboard({ searchParams }: { searchParams: Promise<{ error?: string }> }) {
  const { db, profile } = await requireUser();
  const [classResult, sessionResult, rows] = await Promise.all([
    db.from('classes').select('*').order('name'), db.from('sessions').select('*').order('starts_at', { ascending: false }), records(db),
  ]);
  const classes = checked(classResult) as Class[];
  const sessions = checked(sessionResult) as Session[];
  const now = Date.now();
  const active = sessions.filter(s => !s.closed && +new Date(s.starts_at) <= now && +new Date(s.ends_at) >= now);
  const upcoming = sessions.filter(s => !s.closed && +new Date(s.starts_at) > now);
  return <><Heading title={`Halo, ${profile.name.split(' ')[0]}.`} subtitle="Pantau kelas dan catat kehadiran Anda di satu tempat."/><Notice params={await searchParams}/>
    <div className="stats">{[['Kelas Anda', classes.length], ['Sesi aktif', active.length], ['Presensi tercatat', rows.filter(r => r.status === 'Hadir').length], ['Sesi mendatang', upcoming.length]].map(([label, count]) => <article key={label}><span>{label}</span><strong>{count}</strong></article>)}</div>
    <section className="panel"><div className="section-head"><h2>Sesi berlangsung</h2><LiveRefresh/></div><div className="session-grid">
      {active.map(s => { const c = classes.find(c => c.id === s.class_id); const attended = rows.some(r => r.session_id === s.id && r.student_id === profile.id && r.recorded_at); return <article className="session-card" key={s.id}><span className="course-code">{c?.code} · {c?.room}</span><h3>{c?.name}</h3><p>{s.title}</p><p className="muted tiny">{formatTime(s.starts_at)} – {formatTime(s.ends_at)}</p><div className="card-footer">Radius {s.radius} m · <a target="_blank" rel="noreferrer" href={`https://www.google.com/maps?q=${s.latitude},${s.longitude}`}>Lihat lokasi ↗</a></div>{profile.role === 'mahasiswa' && (attended ? <p className="badge">Anda sudah hadir</p> : <AttendanceButton sessionId={s.id}/>)}</article>; })}
    </div>{!active.length && <p className="empty">Tidak ada sesi yang sedang berlangsung.</p>}</section>
    <section className="panel"><div className="section-head"><h2>Sesi mendatang</h2><Reminder sessions={upcoming}/></div>{upcoming.slice(0, 10).map(s => <p key={s.id}>{classes.find(c => c.id === s.class_id)?.name} · {s.title} <span className="muted">{formatTime(s.starts_at)}</span></p>)}{!upcoming.length && <p className="empty">Belum ada jadwal berikutnya.</p>}</section>
    <section className="panel"><h2>Rekap terbaru</h2><RecordsTable rows={rows.slice(0, 20)}/></section></>;
}
