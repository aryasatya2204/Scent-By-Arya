<?php
// /public/ajax_cart_handler.php
session_start();
require_once '../includes/db.php';
header('Content-Type: application/json');

// Hanya proses jika user sudah login
if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Harus login terlebih dahulu.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $variant_id = isset($_POST['variant_id']) ? (int)$_POST['variant_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    $user_id = (int)$_SESSION['user_id'];

    if ($variant_id > 0 && $quantity > 0) {
        // Cek apakah item sudah ada di keranjang database
        $sql_check = "SELECT quantity FROM user_carts WHERE user_id = ? AND variant_id = ?";
        $stmt_check = mysqli_prepare($conn, $sql_check);
        mysqli_stmt_bind_param($stmt_check, "ii", $user_id, $variant_id);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);

        if (mysqli_num_rows($result_check) > 0) {
            // Jika sudah ada, UPDATE kuantitasnya
            $sql_update = "UPDATE user_carts SET quantity = quantity + ? WHERE user_id = ? AND variant_id = ?";
            $stmt_update = mysqli_prepare($conn, $sql_update);
            mysqli_stmt_bind_param($stmt_update, "iii", $quantity, $user_id, $variant_id);
            mysqli_stmt_execute($stmt_update);
        } else {
            // Jika belum ada, INSERT item baru
            $sql_insert = "INSERT INTO user_carts (user_id, variant_id, quantity) VALUES (?, ?, ?)";
            $stmt_insert = mysqli_prepare($conn, $sql_insert);
            mysqli_stmt_bind_param($stmt_insert, "iii", $user_id, $variant_id, $quantity);
            mysqli_stmt_execute($stmt_insert);
        }

        // Hitung total item baru di keranjang untuk dikirim kembali sebagai respons
        $sql_count = "SELECT COUNT(id) as total_items FROM user_carts WHERE user_id = ?";
        $stmt_count = mysqli_prepare($conn, $sql_count);
        mysqli_stmt_bind_param($stmt_count, "i", $user_id);
        mysqli_stmt_execute($stmt_count);
        $total_items = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_count))['total_items'];

        echo json_encode(['success' => true, 'new_cart_count' => $total_items]);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Data tidak valid.']);
?>
