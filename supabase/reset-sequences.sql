-- Setelah impor data dengan ID lama agar ID baru tidak bentrok.
select setval(pg_get_serial_sequence('public.classes','id'), coalesce((select max(id)+1 from public.classes),1), false);
select setval(pg_get_serial_sequence('public.sessions','id'), coalesce((select max(id)+1 from public.sessions),1), false);
select setval(pg_get_serial_sequence('public.attendance','id'), coalesce((select max(id)+1 from public.attendance),1), false);
select setval(pg_get_serial_sequence('public.attempts','id'), coalesce((select max(id)+1 from public.attempts),1), false);
