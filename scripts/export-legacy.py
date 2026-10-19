"""Export SQLite read-only; password hashes and PHP sessions are never exported."""
import json
import sqlite3
from pathlib import Path

root = Path(__file__).resolve().parents[1]
database = root / 'legacy/app/config/presensi.sqlite'
with sqlite3.connect(database.as_uri() + '?mode=ro', uri=True) as connection:
    connection.row_factory = sqlite3.Row
    data = {'users': [dict(r) for r in connection.execute('SELECT id,name,identifier,role FROM users')]}
    for table in ('classes', 'enrollments', 'sessions', 'attendance', 'attempts'):
        data[table] = [dict(r) for r in connection.execute(f'SELECT * FROM {table}')]
destination = root / 'migration-data.json'
destination.write_text(json.dumps(data, ensure_ascii=False, indent=2), encoding='utf-8')
print(f'Ekspor selesai: {destination}. File memuat data pribadi; jangan commit atau upload ke Vercel.')
