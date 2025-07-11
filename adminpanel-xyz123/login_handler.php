<?php
// /adminpanel-xyz123/login_handler.php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT id, name, email, password, role FROM users WHERE email = ? AND role = 'admin' LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if ($user && password_verify($password, $user['password'])) {
    // Jika login berhasil
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['logged_in'] = true;
    header("Location: product/list.php"); // Arahkan ke dashboard admin
    exit;
} else {
    // Jika gagal, kembali ke halaman login admin dengan pesan error
    header("Location: login.php?error=1");
    exit;
}
?>