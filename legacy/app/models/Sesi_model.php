<?php

class Sesi_model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function getById($id)
    {
        $this->db->query('SELECT * FROM sessions WHERE id=?');
        $this->db->bind(1, $id);
        return $this->db->single();
    }

    public function getSesiMahasiswa($id, $idMahasiswa)
    {
        $this->db->query('SELECT s.* FROM sessions s JOIN enrollments e ON e.class_id=s.class_id WHERE s.id=? AND e.student_id=?');
        $this->db->bind(1, $id);
        $this->db->bind(2, $idMahasiswa);
        return $this->db->single();
    }

    public function tutup($id)
    {
        $this->db->query('UPDATE sessions SET closed=1 WHERE id=?');
        $this->db->bind(1, $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function tambah($data, $kelas)
    {
        $this->db->query('INSERT INTO sessions(class_id,title,starts_at,ends_at,latitude,longitude,radius) VALUES(?,?,?,?,?,?,?)');
        $this->db->bind(1, $kelas['id']);
        $this->db->bind(2, $data['title']);
        $this->db->bind(3, $data['starts_at']);
        $this->db->bind(4, $data['ends_at']);
        $this->db->bind(5, $kelas['latitude']);
        $this->db->bind(6, $kelas['longitude']);
        $this->db->bind(7, $kelas['radius']);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function getSesiPengguna($pengguna)
    {
        $sql = 'SELECT s.*, c.name AS class_name, c.code, c.room, u.name AS lecturer,
                       (SELECT COUNT(*) FROM attendance a WHERE a.session_id=s.id) AS present,
                       (SELECT COUNT(*) FROM enrollments e WHERE e.class_id=c.id) AS students
                FROM sessions s JOIN classes c ON c.id=s.class_id
                JOIN users u ON u.id=c.lecturer_id';
        $parameter = [];
        if ($pengguna['role'] === 'dosen') {
            $sql .= ' WHERE c.lecturer_id=?';
            $parameter[] = $pengguna['id'];
        }
        if ($pengguna['role'] === 'mahasiswa') {
            $sql .= ' JOIN enrollments e ON e.class_id=c.id WHERE e.student_id=?';
            $parameter[] = $pengguna['id'];
        }
        $this->db->query($sql . ' ORDER BY s.starts_at DESC');
        foreach ($parameter as $index => $nilai) {
            $this->db->bind($index + 1, $nilai);
        }
        return $this->db->resultSet();
    }
}
