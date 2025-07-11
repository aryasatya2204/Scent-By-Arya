<?php
session_start();
require_once '../../includes/db.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /Scent-By-Arya/adminpanel-xyz123/login.php");
    exit;
}

$order_id = (int)$_POST['order_id'];
$status = $_POST['status'];

// Validasi status untuk keamanan
$allowed_statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
if (in_array($status, $allowed_statuses)) {
    $sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status, $order_id);
    mysqli_stmt_execute($stmt);
}

header("Location: list.php");
exit;
?>
