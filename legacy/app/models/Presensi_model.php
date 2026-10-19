<?php

class Presensi_model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function getPresensi($idSesi, $idMahasiswa)
    {
        $this->db->query('SELECT id FROM attendance WHERE session_id=? AND student_id=?');
        $this->db->bind(1, $idSesi);
        $this->db->bind(2, $idMahasiswa);
        return $this->db->single();
    }

    public function simpan($idSesi, $idMahasiswa, $gps, $jarak)
    {
        $this->db->query('INSERT OR IGNORE INTO attendance(session_id,student_id,recorded_at,latitude,longitude,accuracy,distance) VALUES(?,?,?,?,?,?,?)');
        $this->db->bind(1, $idSesi);
        $this->db->bind(2, $idMahasiswa);
        $this->db->bind(3, date('Y-m-d H:i:s'));
        $this->db->bind(4, $gps['latitude']);
        $this->db->bind(5, $gps['longitude']);
        $this->db->bind(6, $gps['accuracy']);
        $this->db->bind(7, $jarak);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function catatPenolakan($idMahasiswa, $idSesi, $alasan)
    {
        $this->db->query('INSERT INTO attempts(student_id,session_id,occurred_at,reason) VALUES(?,?,?,?)');
        $this->db->bind(1, $idMahasiswa);
        $this->db->bind(2, $idSesi);
        $this->db->bind(3, date('Y-m-d H:i:s'));
        $this->db->bind(4, $alasan);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function getLogPenolakan()
    {
        $this->db->query('SELECT a.*,u.name,u.identifier FROM attempts a LEFT JOIN users u ON u.id=a.student_id ORDER BY a.id DESC LIMIT 200');
        return $this->db->resultSet();
    }
}
