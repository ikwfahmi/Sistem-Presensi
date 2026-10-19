import ExcelJS from 'exceljs';
import type { RecordRow } from './types';
export async function excelReport(rows: RecordRow[]) {
  const format = (value: string) => new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short', timeZone: 'Asia/Jakarta' }).format(new Date(value));
  const workbook = new ExcelJS.Workbook();
  const sheet = workbook.addWorksheet('Presensi');
  sheet.columns = ['Nama', 'NIM / NIP', 'Kode kelas', 'Kelas', 'Pertemuan', 'Jadwal WIB', 'Status', 'Presensi WIB', 'Jarak (m)', 'Akurasi (m)'].map(header => ({ header, width: 24 }));
  for (const r of rows) sheet.addRow([r.name, r.identifier, r.code, r.class_name, r.title, format(r.starts_at), r.status, r.recorded_at ? format(r.recorded_at) : '', r.distance, r.accuracy]);
  sheet.getRow(1).font = { bold: true };
  sheet.views = [{ state: 'frozen', ySplit: 1 }];
  return new Uint8Array(await workbook.xlsx.writeBuffer());
}
