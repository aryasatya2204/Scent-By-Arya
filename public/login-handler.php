<?php
// /public/login-handler.php
session_start();
header('Content-Type: application/json');
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
    exit();
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$remember_me = isset($_POST['remember_me']);

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email dan password harus diisi.']);
    exit();
}

// PERBAIKAN: Menambahkan 'photo_profile' ke dalam query SELECT
$sql = "SELECT id, name, email, password, role, photo_profile FROM users WHERE email = ? AND role != 'admin' LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if ($user && password_verify($password, $user['password'])) {
    if ($remember_me) {
        setcookie('remember_email', $email, time() + (86400 * 30), "/");
    } else {
        setcookie('remember_email', '', time() - 3600, "/");
    }

    // Simpan informasi user ke dalam session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['logged_in'] = true;
    $_SESSION['user_photo'] = $user['photo_profile'];

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Email atau password salah.']);
}
mysqli_close($conn);
?>

