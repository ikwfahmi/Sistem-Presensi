"""HTTP integration checks. Uses a disposable database, never the application database."""
import datetime as dt
import http.cookiejar
import io
import json
import os
from pathlib import Path
import re
import shutil
import socket
import sqlite3
import subprocess
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request
import uuid
import zipfile
import xml.etree.ElementTree as ET

ROOT = Path(__file__).resolve().parent
DB = Path(tempfile.gettempdir()) / ('geopresensi-test-' + uuid.uuid4().hex + '.sqlite')
SESSION_PATH = Path(tempfile.mkdtemp(prefix='geopresensi-sessions-'))
with socket.socket() as sock:
    sock.bind(('127.0.0.1', 0))
    PORT = sock.getsockname()[1]
BASE = f'http://127.0.0.1:{PORT}/index.php?'
PASSWORD = 'TestingPass123!'
checks = 0

def check(condition, message):
    global checks
    assert condition, message
    checks += 1
    print('PASS:', message)

class Client:
    def __init__(self):
        self.opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))

    def request(self, route, data=None):
        # Seluruh alur utama kini diuji lewat controller/method MVC.
        name, _, query = route.partition('&')
        paths = {'setup':'auth/setup', 'login':'auth/login', 'dashboard':'home/index',
                 'users':'pengguna/index', 'classes':'kelas/index', 'sessions':'sesi/index',
                 'live':'home/live', 'challenge':'presensi/challenge', 'attend':'presensi/simpan',
                 'export':'laporan/excel', 'print':'laporan/cetak'}
        if data is not None:
            paths['login'] = 'auth/masuk'
            paths['users'] = 'pengguna/tambah'
            paths['classes'] = 'kelas/daftar' if data.get('action') == 'enroll' else 'kelas/simpan'
            paths['sessions'] = 'sesi/tutup' if data.get('action') == 'close' else 'sesi/tambah'
        target = 'route=dashboard' if name == 'legacy-dashboard' else urllib.parse.urlencode({'url':paths.get(name, name)})
        if query:
            target += '&' + query
        req = urllib.request.Request(BASE + target, data=urllib.parse.urlencode(data).encode() if data is not None else None)
        try:
            response = self.opener.open(req, timeout=10)
        except urllib.error.HTTPError as error:
            response = error
        return response.status, response.read(), response.headers

    def token(self, route='dashboard'):
        code, body, _ = self.request(route)
        assert code == 200, body
        return re.search(rb'name="csrf-token" content="([^"]+)"', body).group(1).decode()

    def post(self, route, **data):
        data['csrf'] = self.token(route if route in ['setup', 'login', 'users', 'classes', 'sessions', 'password'] else 'dashboard')
        return self.request(route, data)

    def login(self, identifier):
        code, body, _ = self.post('login', identifier=identifier, password=PASSWORD)
        check(code == 200 and b'OVERVIEW' in body, 'Login ' + identifier)

    def attend(self, session=1, **overrides):
        code, body, _ = self.request(f'challenge&session={session}')
        token = json.loads(body).get('token', '')
        data = dict(session_id=session, token=token, latitude=-7.952, longitude=112.608, accuracy=5, timestamp=time.time()*1000)
        data.update(overrides)
        return self.post('attend', **data)

env = os.environ.copy()
env['PRESENSI_DB_PATH'] = str(DB)
env['PRESENSI_SESSION_PATH'] = str(SESSION_PATH)
log = tempfile.TemporaryFile()
server = subprocess.Popen([shutil.which('php'), '-S', f'127.0.0.1:{PORT}', '-t', str(ROOT / 'public')], cwd=ROOT, env=env, stdout=log, stderr=log)
try:
    for _ in range(50):
        try:
            with socket.create_connection(('127.0.0.1', PORT), timeout=.1):
                break
        except OSError:
            time.sleep(.1)
    admin = Client()
    code, body, _ = admin.post('setup', name='Admin Test', identifier='admin-test', password=PASSWORD)
    check(code == 200 and b'Masuk ke akun' in body, 'First administrator setup')
    admin.login('admin-test')
    check(b'OVERVIEW' in admin.request('legacy-dashboard')[1], 'Legacy bookmark remains available')
    check(admin.request('home/model')[0] == 404, 'Base controller methods are not routes')
    check(admin.request('../config/config')[0] == 404, 'Path traversal rejected')
    check(admin.request('kelas/cekPemilik')[0] == 404, 'Private controller methods are not routes')
    check(admin.request('pengguna/tambah')[0] == 405, 'Mutation requires POST')
    check(b'url=auth%2Fmasuk' in Client().request('login')[1], 'Login form points to MVC action')
    check(admin.request('setup')[0] == 200 and b'OVERVIEW' in admin.request('setup')[1], 'Setup cannot run again')
    check(admin.request('users', dict(csrf='invalid'))[0] == 419, 'Invalid CSRF rejected')
    for identity, role in [('lecturer-a','dosen'),('lecturer-b','dosen'),('student-a','mahasiswa'),('student-b','mahasiswa'),('baak-test','baak')]:
        code, body, _ = admin.post('users', name=identity, identifier=identity, password=PASSWORD, role=role)
        check(code == 200 and b'Pengguna ditambahkan' in body, 'Create ' + identity)
    admin.post('classes', code='TI-A', name='Rekayasa Perangkat Lunak', room='Gedung A', lecturer_id=2, latitude=-7.952, longitude=112.608, radius=50)
    admin.post('classes', action='enroll', id=1, student_id=4)
    admin.post('classes', action='enroll', id=1, student_id=5)
    now = dt.datetime.now(dt.timezone(dt.timedelta(hours=7)))
    admin.post('sessions', class_id=1, title='Pertemuan 1', starts_at=(now-dt.timedelta(minutes=5)).strftime('%Y-%m-%dT%H:%M'), ends_at=(now+dt.timedelta(hours=1)).strftime('%Y-%m-%dT%H:%M'))
    student = Client(); student.login('student-a')
    check(student.request('users')[0] == 403, 'Student cannot manage users')
    check(student.request('export')[0] == 403, 'Student cannot export other students')
    check(student.request('challenge&session=999')[0] == 403, 'Unknown session rejected')
    for values, text in [({'latitude':-7.96}, 'luar radius'), ({'accuracy':100}, 'kurang akurat'), ({'timestamp':(time.time()-120)*1000}, 'kedaluwarsa'), ({'mock':1}, 'tiruan'), ({'latitude':91}, 'tidak valid')]:
        code, body, _ = student.attend(**values)
        check(code == 422 and text in json.loads(body)['message'], 'GPS rejected: '+text)
    code, body, _ = student.attend()
    check(code == 200 and json.loads(body)['ok'], 'Valid attendance accepted')
    code, body, _ = student.attend()
    check(code == 422 and 'sudah presensi' in json.loads(body)['message'], 'Duplicate attendance rejected')
    code, body, _ = student.post('attend', session_id=1, token='replayed', latitude=-7.952, longitude=112.608, accuracy=5, timestamp=time.time()*1000)
    check(code == 422 and 'Verifikasi' in json.loads(body)['message'], 'Replay rejected')
    rows = json.loads(student.request('live')[1])['records']
    check(len(rows) == 1 and rows[0]['identifier'] == 'student-a', 'Student history is private')
    other = Client(); other.login('lecturer-b')
    check(json.loads(other.request('live')[1])['records'] == [], 'Other lecturer cannot read this class')
    code, body, _ = other.post('sessions', action='close', id=1)
    check(b'Kelas tidak tersedia' in body, 'Other lecturer cannot close this session')
    lecturer = Client(); lecturer.login('lecturer-a')
    check(len(json.loads(lecturer.request('live')[1])['records']) == 2, 'Lecturer sees present and pending students')
    admin.post('sessions', action='close', id=1)
    second = Client(); second.login('student-b')
    code, body, _ = second.attend()
    check(code == 422 and 'berakhir' in json.loads(body)['message'], 'Closed session rejects attendance')
    baak = Client(); baak.login('baak-test')
    check(baak.request('classes')[0] == 403, 'BAAK is read-only')
    code, body, headers = baak.request('export')
    with zipfile.ZipFile(io.BytesIO(body)) as book:
        check(book.testzip() is None, 'XLSX archive valid')
        for name in book.namelist(): ET.fromstring(book.read(name))
        sheet = book.read('xl/worksheets/sheet1.xml').decode()
        check('Hadir' in sheet and 'Tidak hadir' in sheet and 'student-b' in sheet, 'Excel includes attendance and absences')
    check(b'window.print()' in baak.request('print')[1], 'PDF print view available')
    check(json.loads(baak.request('live&class_id=999')[1])['records'] == [], 'Report class filter applied')
    with sqlite3.connect(DB) as con:
        check(con.execute('SELECT COUNT(*) FROM attendance').fetchone()[0] == 1, 'Exactly one attendance stored')
        check(con.execute('SELECT COUNT(*) FROM attempts').fetchone()[0] >= 8, 'Rejected attempts logged')
    con.close()
    print(f'\nAll {checks} checks passed. Application database untouched.')
finally:
    server.terminate()
    server.wait(timeout=10)
    log.seek(0)
    text = log.read().decode(errors='replace')
    log.close()
    for suffix in ['', '-wal', '-shm']:
        Path(str(DB)+suffix).unlink(missing_ok=True)
    for session_file in SESSION_PATH.iterdir():
        session_file.unlink()
    SESSION_PATH.rmdir()
    if 'Fatal error' in text or 'Warning:' in text:
        print(text)
        raise AssertionError('PHP emitted errors/warnings')
