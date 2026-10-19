import { requireUser } from '@/lib/auth';
import { checked } from '@/lib/data';
import type { Class, Profile } from '@/lib/types';
import { saveClass, enrollStudent } from '@/app/actions';
import { Heading, Notice } from '@/components/ui';
import { LocationFields } from '@/components/gps';
export default async function Classes({ searchParams }: { searchParams: Promise<{ edit?: string; error?: string; message?: string }> }) {
  const { db, profile } = await requireUser(['admin', 'dosen']);
  const params = await searchParams;
  const [cr, pr, er] = await Promise.all([db.from('classes').select('*').order('name'), db.from('profiles').select('id,name,identifier,role').in('role', ['dosen', 'mahasiswa']).order('name'), db.from('enrollments').select('*')]);
  const classes = checked(cr) as Class[];
  const people = checked(pr) as Profile[];
  const enrollments = checked(er);
  const edit = classes.find(c => String(c.id) === params.edit);
  return <><Heading title="Kelas & peserta" subtitle="Atur kelas, lokasi gedung, radius, dan peserta."/><Notice params={params}/>
    <section className="panel"><h2>{edit ? `Ubah ${edit.code}` : 'Tambah kelas'}</h2><form action={saveClass} className="form-grid" key={edit?.id ?? 'new'}>
      <input name="id" type="hidden" value={edit?.id ?? ''}/>
      <label>Kode kelas<input name="code" required maxLength={40} defaultValue={edit?.code}/></label><label>Nama kelas<input name="name" required maxLength={160} defaultValue={edit?.name}/></label>
      <label>Ruangan<input name="room" required maxLength={100} defaultValue={edit?.room}/></label>
      {profile.role === 'admin' && <label>Dosen pengampu<select name="lecturer_id" required defaultValue={edit?.lecturer_id ?? ''}><option value="" disabled>Pilih dosen</option>{people.filter(p => p.role === 'dosen').map(p => <option key={p.id} value={p.id}>{p.name}</option>)}</select></label>}
      <label>Radius (meter)<input type="number" name="radius" min={10} max={500} required defaultValue={edit?.radius ?? 50}/></label>
      <LocationFields latitude={edit?.latitude} longitude={edit?.longitude}/><div className="form-actions"><button>Simpan kelas</button>{edit && <a className="button secondary" href="/classes">Batal</a>}</div>
    </form></section>
    <section className="panel"><h2>Daftar kelas</h2>{!classes.length && <p className="empty">Tambahkan akun dosen lalu buat kelas pertama.</p>}
      {classes.map(c => <details key={c.id} className="class-detail"><summary><strong>{c.code} · {c.name}</strong> — {c.room} · {enrollments.filter(e => e.class_id === c.id).length} peserta</summary>
        <p className="muted">{people.find(p => p.id === c.lecturer_id)?.name} · radius {c.radius} m · {c.latitude}, {c.longitude}</p><a className="button secondary" href={`/classes?edit=${c.id}`}>Ubah kelas</a>
        <form action={enrollStudent} className="filters"><input type="hidden" name="class_id" value={c.id}/><label>Daftarkan mahasiswa<select name="student_id" required defaultValue=""><option value="" disabled>Pilih mahasiswa</option>{people.filter(p => p.role === 'mahasiswa' && !enrollments.some(e => e.class_id === c.id && e.student_id === p.id)).map(p => <option key={p.id} value={p.id}>{p.identifier} · {p.name}</option>)}</select></label><button>Daftarkan</button></form>
        <ul>{enrollments.filter(e => e.class_id === c.id).map(e => <li key={e.student_id}>{people.find(p => p.id === e.student_id)?.name} · {people.find(p => p.id === e.student_id)?.identifier}</li>)}</ul>
      </details>)}
    </section></>;
}
