<?php

class Laporan_model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function getRekap($pengguna)
    {
        // LEFT JOIN menyertakan peserta yang belum/tidak melakukan presensi.
        $sql = "SELECT s.id AS session_id, c.code, c.name AS class_name, s.title,
                       s.starts_at, s.ends_at, s.closed, u.name, u.identifier,
                       a.recorded_at, a.distance, a.accuracy,
                       CASE WHEN a.id IS NOT NULL THEN 'Hadir'
                            WHEN s.closed=1 OR s.ends_at < ? THEN 'Tidak hadir'
                            ELSE 'Belum presensi' END AS status
                FROM sessions s JOIN classes c ON c.id=s.class_id
                JOIN enrollments en ON en.class_id=c.id
                JOIN users u ON u.id=en.student_id
                LEFT JOIN attendance a ON a.session_id=s.id AND a.student_id=u.id
                WHERE 1=1";
        $parameter = [date('Y-m-d H:i:s')];
        if ($pengguna['role'] === 'dosen') {
            $sql .= ' AND c.lecturer_id=?';
            $parameter[] = $pengguna['id'];
        }
        if ($pengguna['role'] === 'mahasiswa') {
            $sql .= ' AND u.id=?';
            $parameter[] = $pengguna['id'];
        }
        if (!empty($_GET['class_id'])) {
            $sql .= ' AND c.id=?';
            $parameter[] = (int) $_GET['class_id'];
        }
        foreach (['from' => '>=', 'to' => '<='] as $nama => $operator) {
            if (!empty($_GET[$nama]) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET[$nama])) {
                $sql .= ' AND date(s.starts_at)' . $operator . '?';
                $parameter[] = $_GET[$nama];
            }
        }
        $this->db->query($sql . ' ORDER BY s.starts_at DESC,u.name');
        foreach ($parameter as $index => $nilai) {
            $this->db->bind($index + 1, $nilai);
        }
        return $this->db->resultSet();
    }
}
