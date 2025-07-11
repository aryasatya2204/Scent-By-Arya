<?php
// /adminpanel-xyz123/user/action_handler.php

session_start();
require_once '../includes/db.php';

// Proteksi admin dan validasi dasar
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin' || !isset($_GET['action']) || !isset($_GET['id'])) {
    header("Location: /Scent-By-Arya/adminpanel-xyz123/login.php");
    exit;
}

$action = $_GET['action'];
$user_id = (int)$_GET['id'];

$sql = '';
$status = '';

// Tentukan query SQL berdasarkan aksi yang dipilih
switch ($action) {
    case 'block':
        $sql = "UPDATE users SET status = 'blocked' WHERE id = ?";
        break;
    case 'activate':
        $sql = "UPDATE users SET status = 'active' WHERE id = ?";
        break;
    case 'delete':
        // Peringatan: Ini adalah "hard delete". Di aplikasi nyata, pertimbangkan "soft delete".
        // Juga, pertimbangkan apa yang terjadi pada pesanan pengguna ini.
        $sql = "DELETE FROM users WHERE id = ?";
        break;
    default:
        // Jika aksi tidak valid, kembali ke daftar pengguna
        header("Location: list.php");
        exit;
}

// Eksekusi query
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
}

// Kembali ke halaman daftar pengguna setelah aksi selesai
header("Location: list.php?status=" . $action . "_success");
exit;
?>
