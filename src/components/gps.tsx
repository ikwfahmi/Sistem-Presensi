'use client';
import { useState } from 'react';
import { useRouter } from 'next/navigation';
export function AttendanceButton({ sessionId }: { sessionId: number }) {
  const [busy, setBusy] = useState(false);
  const [message, setMessage] = useState('');
  const router = useRouter();
  async function attend() {
    setBusy(true); setMessage('Mengambil lokasi GPS…');
    try {
      if (!navigator.geolocation) throw new Error('Browser tidak mendukung GPS. Gunakan HTTPS dan izinkan lokasi.');
      const position = await new Promise<GeolocationPosition>((resolve, reject) => navigator.geolocation.getCurrentPosition(resolve, reject, { enableHighAccuracy: true, maximumAge: 0, timeout: 20000 }));
      const challenge = await fetch('/api/attendance/challenge', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ session_id: sessionId }) });
      const token = await challenge.json();
      if (!challenge.ok) throw new Error(token.message);
      const response = await fetch('/api/attendance', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ session_id: sessionId, token: token.token, latitude: position.coords.latitude, longitude: position.coords.longitude, accuracy: position.coords.accuracy, timestamp: position.timestamp }) });
      const result = await response.json();
      setMessage(result.message);
      if (result.ok) router.refresh();
    } catch (error) {
      setMessage(error instanceof Error ? error.message : 'Lokasi gagal diambil. Periksa izin GPS dan coba di area terbuka.');
    } finally { setBusy(false); }
  }
  return <div className="gps-card"><button disabled={busy} onClick={attend}>{busy ? 'Memproses…' : 'Ambil GPS & presensi'}</button><small aria-live="polite">{message || 'Lokasi hanya diambil saat Anda menekan tombol.'}</small></div>;
}
export function LocationFields({ latitude, longitude }: { latitude?: number; longitude?: number }) {
  const [lat, setLat] = useState(latitude === undefined ? '' : String(latitude));
  const [lng, setLng] = useState(longitude === undefined ? '' : String(longitude));
  const [message, setMessage] = useState('');
  return <><label>Latitude<input name="latitude" type="number" step="any" min={-90} max={90} required value={lat} onChange={e => setLat(e.target.value)} /></label>
    <label>Longitude<input name="longitude" type="number" step="any" min={-180} max={180} required value={lng} onChange={e => setLng(e.target.value)} /></label>
    <div><button type="button" className="secondary" onClick={() => {
      if (!navigator.geolocation) { setMessage('GPS tidak tersedia.'); return; }
      setMessage('Mengambil lokasi…');
      navigator.geolocation.getCurrentPosition(p => { setLat(String(p.coords.latitude)); setLng(String(p.coords.longitude)); setMessage(`Akurasi ±${Math.round(p.coords.accuracy)} m. Pastikan titik gedung benar.`); }, () => setMessage('Gagal mengambil lokasi. Periksa izin GPS.'), { enableHighAccuracy: true, maximumAge: 0, timeout: 20000 });
    }}>Gunakan lokasi saya</button><small className="muted">{message}</small></div></>;
}
