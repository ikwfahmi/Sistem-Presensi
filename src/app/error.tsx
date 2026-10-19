'use client';
export default function ErrorPage({ reset }: { error: Error & { digest?: string }; reset: () => void }) {
  return <main className="panel"><h1>Halaman belum dapat dimuat.</h1><p>Periksa koneksi, konfigurasi Supabase, dan pastikan migrasi database sudah dijalankan.</p><button onClick={reset}>Coba kembali</button></main>;
}
