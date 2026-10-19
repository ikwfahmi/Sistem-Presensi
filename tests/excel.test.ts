import { test } from 'node:test';
import assert from 'node:assert/strict';
import ExcelJS from 'exceljs';
import { excelReport } from '../src/lib/excel';
test('export produces real XLSX, preserves identifiers, and keeps user input as text', async () => {
  const bytes = await excelReport([{ session_id: 1, class_id: 1, code: 'IF1', class_name: 'Pemrograman', title: 'Sesi 1', starts_at: '2026-10-19T02:00:00Z', ends_at: '2026-10-19T03:00:00Z', closed: false, student_id: 'student', name: '=HYPERLINK("https://example.com")', identifier: '00123', recorded_at: '2026-10-19T02:10:00Z', distance: 12, accuracy: 5, status: 'Hadir' }]);
  assert.equal(String.fromCharCode(bytes[0], bytes[1]), 'PK');
  const book = new ExcelJS.Workbook();
  await book.xlsx.load(Buffer.from(bytes) as unknown as ExcelJS.Buffer);
  const sheet = book.getWorksheet('Presensi')!;
  assert.equal(sheet.rowCount, 2);
  assert.equal(sheet.getCell('A2').type, ExcelJS.ValueType.String);
  assert.equal(sheet.getCell('B2').value, '00123');
  assert.match(String(sheet.getCell('F2').value), /09[.:]00/);
  assert.equal(sheet.getCell('I2').value, 12);
});
