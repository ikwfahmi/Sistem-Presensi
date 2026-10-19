<?php

class User_model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function getPenggunaPertama()
    {
        $this->db->query('SELECT id FROM users LIMIT 1');
        return $this->db->single();
    }

    public function getById($id)
    {
        $this->db->query('SELECT id,name,identifier,role FROM users WHERE id=?');
        $this->db->bind(1, $id);
        return $this->db->single();
    }

    public function getByIdentitas($identitas)
    {
        $this->db->query('SELECT * FROM users WHERE identifier=?');
        $this->db->bind(1, $identitas);
        return $this->db->single();
    }

    public function getSemuaPengguna()
    {
        $this->db->query('SELECT id,name,identifier,role FROM users ORDER BY role,name');
        return $this->db->resultSet();
    }

    public function getByPeran($peran)
    {
        $this->db->query('SELECT id,name,identifier FROM users WHERE role=? ORDER BY name');
        $this->db->bind(1, $peran);
        return $this->db->resultSet();
    }

    public function tambah($data, $peran)
    {
        $this->db->query('INSERT INTO users(name,identifier,password,role) VALUES(?,?,?,?)');
        $this->db->bind(1, trim($data['name']));
        $this->db->bind(2, trim($data['identifier']));
        $this->db->bind(3, password_hash($data['password'], PASSWORD_DEFAULT));
        $this->db->bind(4, $peran);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function getPassword($id)
    {
        $this->db->query('SELECT password FROM users WHERE id=?');
        $this->db->bind(1, $id);
        return $this->db->single();
    }

    public function ubahPassword($id, $password)
    {
        $this->db->query('UPDATE users SET password=? WHERE id=?');
        $this->db->bind(1, password_hash($password, PASSWORD_DEFAULT));
        $this->db->bind(2, $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function bersihkanLoginGagal($batasWaktu)
    {
        $this->db->query('DELETE FROM login_attempts WHERE occurred_at<?');
        $this->db->bind(1, $batasWaktu);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function jumlahLoginGagal($identitas)
    {
        $this->db->query('SELECT COUNT(*) AS total FROM login_attempts WHERE identity=?');
        $this->db->bind(1, $identitas);
        return $this->db->single();
    }

    public function catatLoginGagal($identitas)
    {
        $this->db->query('INSERT INTO login_attempts(identity,occurred_at) VALUES(?,?)');
        $this->db->bind(1, $identitas);
        $this->db->bind(2, time());
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function hapusLoginGagal($identitas)
    {
        $this->db->query('DELETE FROM login_attempts WHERE identity=?');
        $this->db->bind(1, $identitas);
        $this->db->execute();
        return $this->db->rowCount();
    }
}
