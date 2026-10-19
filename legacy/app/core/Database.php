<?php

// PDO dengan pola query, bind, execute, resultSet, single, rowCount dari WPU.
class Database
{
    private $dbh;
    private $stmt;
    private static $koneksi;

    public function __construct()
    {
        if (self::$koneksi === null) {
            self::$koneksi = new PDO('sqlite:' . DB_PATH, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            self::$koneksi->exec('PRAGMA foreign_keys=ON');
            self::$koneksi->exec('PRAGMA busy_timeout=5000');
            self::$koneksi->exec('PRAGMA journal_mode=WAL');
            // Skema memakai CREATE IF NOT EXISTS; data lama tetap dipakai.
            self::$koneksi->exec(file_get_contents(__DIR__ . '/../config/schema.sql'));
        }
        $this->dbh = self::$koneksi;
    }

    public function query($query)
    {
        $this->stmt = $this->dbh->prepare($query);
    }

    public function bind($param, $value, $type = null)
    {
        if ($type === null) {
            if (is_int($value)) {
                $type = PDO::PARAM_INT;
            } elseif (is_bool($value)) {
                $type = PDO::PARAM_BOOL;
            } elseif ($value === null) {
                $type = PDO::PARAM_NULL;
            } else {
                $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    public function execute()
    {
        return $this->stmt->execute();
    }

    public function resultSet()
    {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function single()
    {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function rowCount()
    {
        return $this->stmt->rowCount();
    }
}
