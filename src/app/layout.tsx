import type { Metadata } from 'next';
import './globals.css';
export const metadata: Metadata = { title: 'GeoPresensi', description: 'Sistem presensi kampus berbasis lokasi' };
export default function RootLayout({ children }: Readonly<{ children: React.ReactNode }>) {
  return <html lang="id"><body>{children}</body></html>;
}
