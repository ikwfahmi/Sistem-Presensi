import Link from 'next/link';
import { requireUser } from '@/lib/auth';
import { logout } from '@/app/actions';
export const dynamic = 'force-dynamic';
export default async function AppLayout({ children }: { children: React.ReactNode }) {
  const { profile } = await requireUser();
  const manage = ['admin', 'dosen'].includes(profile.role);
  return <><aside className="sidebar"><Link href="/dashboard" className="brand"><span className="logo">G</span>GeoPresensi</Link>
    <small>SISTEM PRESENSI KAMPUS</small><nav aria-label="Menu utama"><Link href="/dashboard">Dashboard</Link>
      {manage && <><Link href="/classes">Kelas & peserta</Link><Link href="/sessions">Sesi presensi</Link></>}
      <Link href="/reports">{profile.role === 'mahasiswa' ? 'Riwayat presensi' : 'Laporan'}</Link>
      {profile.role === 'admin' && <Link href="/users">Pengguna</Link>}
      {manage && <Link href="/security">Log penolakan</Link>}<Link href="/password">Ganti kata sandi</Link>
    </nav><div className="profile"><strong>{profile.name}</strong><small>{profile.role} · {profile.identifier}</small><form action={logout}><button className="subtle">Keluar</button></form></div>
  </aside><main className="main">{children}</main></>;
}
