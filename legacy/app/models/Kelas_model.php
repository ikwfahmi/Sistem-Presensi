<?php

class Kelas_model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function getById($id)
    {
        $this->db->query('SELECT * FROM classes WHERE id=?');
        $this->db->bind(1, $id);
        return $this->db->single();
    }

    public function getByKode($kode, $id = 0)
    {
        $this->db->query('SELECT id FROM classes WHERE code=? AND id<>?');
        $this->db->bind(1, $kode);
        $this->db->bind(2, $id);
        return $this->db->single();
    }

    public function getPeserta()
    {
        $this->db->query('SELECT * FROM enrollments');
        return $this->db->resultSet();
    }

    public function daftarkanMahasiswa($idKelas, $idMahasiswa)
    {
        $this->db->query('INSERT OR IGNORE INTO enrollments(class_id,student_id) VALUES(?,?)');
        $this->db->bind(1, $idKelas);
        $this->db->bind(2, $idMahasiswa);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function tambah($data)
    {
        $this->db->query('INSERT INTO classes(code,name,lecturer_id,room,latitude,longitude,radius) VALUES(?,?,?,?,?,?,?)');
        $this->db->bind(1, $data['code']);
        $this->db->bind(2, $data['name']);
        $this->db->bind(3, $data['lecturer_id']);
        $this->db->bind(4, $data['room']);
        $this->db->bind(5, $data['latitude']);
        $this->db->bind(6, $data['longitude']);
        $this->db->bind(7, $data['radius']);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function ubah($data)
    {
        $this->db->query('UPDATE classes SET code=?,name=?,lecturer_id=?,room=?,latitude=?,longitude=?,radius=? WHERE id=?');
        $this->db->bind(1, $data['code']);
        $this->db->bind(2, $data['name']);
        $this->db->bind(3, $data['lecturer_id']);
        $this->db->bind(4, $data['room']);
        $this->db->bind(5, $data['latitude']);
        $this->db->bind(6, $data['longitude']);
        $this->db->bind(7, $data['radius']);
        $this->db->bind(8, $data['id']);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function getKelasPengguna($pengguna)
    {
        $sql = 'SELECT c.*, u.name AS lecturer,
                       (SELECT COUNT(*) FROM enrollments e WHERE e.class_id=c.id) AS students
                FROM classes c JOIN users u ON u.id=c.lecturer_id';
        $parameter = [];
        // Dosen hanya melihat kelasnya; mahasiswa hanya melihat kelas terdaftar.
        if ($pengguna['role'] === 'dosen') {
            $sql .= ' WHERE c.lecturer_id=?';
            $parameter[] = $pengguna['id'];
        }
        if ($pengguna['role'] === 'mahasiswa') {
            $sql .= ' JOIN enrollments e ON e.class_id=c.id WHERE e.student_id=?';
            $parameter[] = $pengguna['id'];
        }
        $this->db->query($sql . ' ORDER BY c.name');
        foreach ($parameter as $index => $nilai) {
            $this->db->bind($index + 1, $nilai);
        }
        return $this->db->resultSet();
    }
}
