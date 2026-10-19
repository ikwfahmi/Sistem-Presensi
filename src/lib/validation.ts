import { z } from 'zod';
export const roles = ['admin', 'baak', 'dosen', 'mahasiswa'] as const;
const coordinate = (min: number, max: number) => z.preprocess(value => typeof value === 'string' && value.trim() === '' ? undefined : value, z.coerce.number().min(min).max(max));
export const classInput = z.object({
  code: z.string().trim().min(1).max(40), name: z.string().trim().min(1).max(160),
  room: z.string().trim().min(1).max(100), lecturer_id: z.uuid(),
  latitude: coordinate(-90, 90), longitude: coordinate(-180, 180),
  radius: z.coerce.number().int().min(10).max(500),
});
export const gpsInput = z.object({
  session_id: z.number().int().positive(), token: z.uuid(), latitude: z.number().min(-90).max(90),
  longitude: z.number().min(-180).max(180), accuracy: z.number().positive().max(500),
  timestamp: z.number().positive(), mock: z.boolean().optional(),
});
export function haversine(lat: number, lng: number, targetLat: number, targetLng: number) {
  const rad = (value: number) => value * Math.PI / 180;
  const a = Math.sin(rad(targetLat - lat) / 2) ** 2 + Math.cos(rad(lat)) * Math.cos(rad(targetLat)) * Math.sin(rad(targetLng - lng) / 2) ** 2;
  return 6371000 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(Math.max(0, 1 - a)));
}
export function wibToIso(value: string) {
  if (!/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/.test(value)) throw new Error('Tanggal tidak valid.');
  const date = new Date(`${value}:00+07:00`);
  if (!Number.isFinite(date.getTime()) || new Date(date.getTime() + 7 * 3600000).toISOString().slice(0, 16) !== value) throw new Error('Tanggal tidak valid.');
  return date.toISOString();
}
export function loginEmail(identifier: string) {
  const normalized = identifier.trim().toLowerCase();
  if (!/^[a-z0-9._-]{1,80}$/.test(normalized)) throw new Error('NIM/NIP hanya boleh huruf, angka, titik, garis bawah, atau tanda hubung.');
  return `${normalized}@presensi.invalid`;
}
