<?php
session_start();

// Cek apakah pengguna sudah login atau belum.
// Jika tidak ada session 'logged_in' atau nilainya bukan true,
// maka pengguna dianggap belum login.
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../public/index.php");
    exit; 
}
?>