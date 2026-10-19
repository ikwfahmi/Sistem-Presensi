export type Role = 'admin' | 'baak' | 'dosen' | 'mahasiswa';
export type Profile = { id: string; name: string; identifier: string; role: Role };
export type Class = { id: number; code: string; name: string; lecturer_id: string; room: string; latitude: number; longitude: number; radius: number };
export type Session = { id: number; class_id: number; title: string; starts_at: string; ends_at: string; closed: boolean; latitude: number; longitude: number; radius: number };
export type RecordRow = { session_id: number; class_id: number; code: string; class_name: string; title: string; starts_at: string; ends_at: string; closed: boolean; student_id: string; name: string; identifier: string; recorded_at: string | null; distance: number | null; accuracy: number | null; status: string };
