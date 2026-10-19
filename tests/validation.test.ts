import { test } from 'node:test';
import assert from 'node:assert/strict';
import { classInput, gpsInput, haversine, loginEmail, wibToIso } from '../src/lib/validation';
test('WIB is converted to UTC independently of host timezone', () => {
  assert.equal(wibToIso('2026-10-19T09:30'), '2026-10-19T02:30:00.000Z');
  assert.throws(() => wibToIso('2026-10-19'));
  assert.throws(() => wibToIso('2026-02-30T09:30'));
});
test('identifier normalization and input constraints', () => {
  assert.equal(loginEmail(' NIM.123 '), 'nim.123@presensi.invalid');
  assert.throws(() => loginEmail('student@external.com'));
  assert.throws(() => loginEmail('a b'));
});
test('geolocation rejects invalid numbers and coordinates', () => {
  const gps = { session_id: 1, token: '126e89a7-4ad0-4451-950f-b8ece404ce43', latitude: -6.2, longitude: 106.8, accuracy: 5, timestamp: Date.now() };
  assert.equal(gpsInput.safeParse(gps).success, true);
  for (const change of [{ latitude: 91 }, { longitude: Infinity }, { accuracy: 0 }, { timestamp: NaN }, { session_id: -1 }, { token: '' }]) assert.equal(gpsInput.safeParse({ ...gps, ...change }).success, false);
});
test('Haversine distance preserves geofencing scale', () => {
  assert.equal(haversine(-6.2, 106.8, -6.2, 106.8), 0);
  assert.ok(Math.abs(haversine(0, 0, 0, 1) - 111194.93) < 1);
  assert.ok(haversine(-6.2, 106.8, -6.2001, 106.8) < 50);
  assert.ok(haversine(-6.2, 106.8, -6.201, 106.8) > 50);
});
test('class input enforces radius and lecturer UUID', () => {
  const value = { code: 'IF1', name: 'Pemrograman', room: 'A1', lecturer_id: '126e89a7-4ad0-4451-950f-b8ece404ce43', latitude: '-6.2', longitude: '106.8', radius: '50' };
  assert.equal(classInput.parse(value).radius, 50);
  assert.equal(classInput.safeParse({ ...value, radius: 501 }).success, false);
  assert.equal(classInput.safeParse({ ...value, lecturer_id: '1' }).success, false);
  assert.equal(classInput.safeParse({ ...value, latitude: '' }).success, false);
});
