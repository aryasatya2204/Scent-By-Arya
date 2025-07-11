<?php
// /public/checkout-handler.php
require_once '../includes/auth.php';
require_once '../includes/db.php';

// Redirect jika keranjang kosong atau bukan metode POST
if (empty($_SESSION['cart']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Ambil data dari form
$user_id = $_SESSION['user_id'];
$customer_name = $_POST['customer_name'];
$customer_phone = $_POST['customer_phone'];
$shipping_address = $_POST['shipping_address'];

// Hitung ulang total harga di server untuk keamanan
$total_price = 0;
$product_ids = array_keys($_SESSION['cart']);
$product_ids_string = implode(',', $product_ids);
$sql_products = "SELECT id, price FROM products WHERE id IN ($product_ids_string)";
$result_products = mysqli_query($conn, $sql_products);
$products = [];
while ($row = mysqli_fetch_assoc($result_products)) {
    $products[$row['id']] = $row;
}
foreach ($_SESSION['cart'] as $id => $quantity) {
    $total_price += $products[$id]['price'] * $quantity;
}

// Gunakan transaksi database untuk memastikan semua query berhasil
mysqli_begin_transaction($conn);

try {
    // 1. Simpan ke tabel 'orders'
    $sql_order = "INSERT INTO orders (user_id, customer_name, customer_phone, shipping_address, total_price, status) VALUES (?, ?, ?, ?, ?, 'pending')";
    $stmt_order = mysqli_prepare($conn, $sql_order);
    mysqli_stmt_bind_param($stmt_order, "isssd", $user_id, $customer_name, $customer_phone, $shipping_address, $total_price);
    mysqli_stmt_execute($stmt_order);
    $order_id = mysqli_insert_id($conn); // Ambil ID dari pesanan yang baru dibuat

    // 2. Simpan setiap item ke tabel 'order_items'
    $sql_items = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
    $stmt_items = mysqli_prepare($conn, $sql_items);
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $price = $products[$product_id]['price'];
        mysqli_stmt_bind_param($stmt_items, "iiid", $order_id, $product_id, $quantity, $price);
        mysqli_stmt_execute($stmt_items);
    }

    // Jika semua query berhasil, commit transaksi
    mysqli_commit($conn);

    // 3. Kosongkan keranjang belanja
    unset($_SESSION['cart']);

    // 4. Redirect ke halaman sukses
    header("Location: order-success.php?order_id=" . $order_id);
    exit;

} catch (Exception $e) {
    // Jika terjadi error, batalkan semua perubahan
    mysqli_rollback($conn);
    // Redirect kembali ke checkout dengan pesan error
    header("Location: checkout.php?error=1");
    exit;
}
?>