import { requireUser } from '@/lib/auth';
import { checked } from '@/lib/data';
import type { Profile } from '@/lib/types';
import { createUser } from '@/app/actions';
import { Heading, Notice } from '@/components/ui';
export default async function Users({ searchParams }: { searchParams: Promise<{ error?: string; message?: string }> }) {
  const { db } = await requireUser(['admin']);
  const users = checked(await db.from('profiles').select('*').order('name')) as Profile[];
  return <><Heading title="Pengguna" subtitle="Buat akun mahasiswa, dosen, BAAK, dan administrator."/><Notice params={await searchParams}/>
    <section className="panel"><h2>Tambah pengguna</h2><form action={createUser} className="form-grid"><label>Nama lengkap<input name="name" required maxLength={160}/></label><label>NIM / NIP<input name="identifier" required pattern="[a-zA-Z0-9._-]{1,80}" maxLength={80}/></label><label>Peran<select name="role"><option value="mahasiswa">Mahasiswa</option><option value="dosen">Dosen</option><option value="baak">BAAK</option><option value="admin">Administrator</option></select></label><label>Kata sandi awal<input name="password" type="password" minLength={10} maxLength={128} required autoComplete="new-password"/></label><div><button>Buat pengguna</button></div></form></section>
    <section className="panel"><h2>Akun terdaftar</h2><div className="table-wrap"><table><thead><tr><th>Nama</th><th>NIM / NIP</th><th>Peran</th></tr></thead><tbody>{users.map(p => <tr key={p.id}><td>{p.name}</td><td>{p.identifier}</td><td><span className="badge">{p.role}</span></td></tr>)}</tbody></table></div></section></>;
}
