'use client';
import { useRouter } from 'next/navigation';
import { useEffect, useState } from 'react';
export function LiveRefresh() {
  const router = useRouter();
  useEffect(() => { const timer = setInterval(() => { if (document.visibilityState === 'visible') router.refresh(); }, 10000); return () => clearInterval(timer); }, [router]);
  return <span className="badge">Diperbarui setiap 10 detik</span>;
}
export function PrintButton() { return <button onClick={() => window.print()}>Cetak / simpan PDF</button>; }
export function Reminder({ sessions }: { sessions: { id: number; title: string; starts_at: string }[] }) {
  const [enabled, setEnabled] = useState(false);
  useEffect(() => {
    if (!enabled) return;
    const seen = new Set<number>();
    const check = () => { for (const session of sessions) {
      const remaining = +new Date(session.starts_at) - Date.now();
      const key = `reminded-${session.id}`;
      if (remaining > 0 && remaining <= 900000 && !seen.has(session.id) && !sessionStorage.getItem(key)) {
        new Notification('Pengingat presensi', { body: `${session.title} dimulai dalam ${Math.ceil(remaining / 60000)} menit.` });
        seen.add(session.id); sessionStorage.setItem(key, '1');
      }
    } };
    check(); const timer = setInterval(check, 30000); return () => clearInterval(timer);
  }, [enabled, sessions]);
  return <button className="secondary" disabled={enabled} onClick={async () => {
    if ('Notification' in window && await Notification.requestPermission() === 'granted') setEnabled(true);
  }}>{enabled ? 'Pengingat aktif selama halaman terbuka' : 'Aktifkan pengingat sesi'}</button>;
}
