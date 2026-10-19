<?php

class Flasher
{
    public static function setFlash($pesan, $tipe = 'success')
    {
        $_SESSION['flash'] = [$pesan, $tipe];
    }

    public static function flash()
    {
        if (!isset($_SESSION['flash'])) {
            return;
        }
        [$pesan, $tipe] = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div role="alert" class="notice ' . e($tipe) . '">' . e($pesan) . '</div>';
    }
}
