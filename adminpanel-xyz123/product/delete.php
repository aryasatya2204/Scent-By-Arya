<?php
// /adminpanel-xyz123/product/delete.php

session_start();
require_once '../includes/db.php';

// Proteksi admin dan validasi dasar
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin' || !isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: /Scent-By-Arya/adminpanel-xyz123/login.php");
    exit;
}

$product_id = (int)$_GET['id'];

// Mulai transaksi database untuk memastikan semua proses berjalan lancar
mysqli_begin_transaction($conn);

try {
    // Langkah 1: Hapus semua file gambar fisik dari server
    $upload_dir = '../../public/uploads/';
    $sql_get_images = "SELECT image_url FROM product_images WHERE product_id = ?";
    $stmt_get_images = mysqli_prepare($conn, $sql_get_images);
    mysqli_stmt_bind_param($stmt_get_images, "i", $product_id);
    mysqli_stmt_execute($stmt_get_images);
    $result_images = mysqli_stmt_get_result($stmt_get_images);
    while ($row = mysqli_fetch_assoc($result_images)) {
        if ($row['image_url'] != 'default-product.jpg' && file_exists($upload_dir . $row['image_url'])) {
            unlink($upload_dir . $row['image_url']);
        }
    }
    mysqli_stmt_close($stmt_get_images);

    // Langkah 2: Hapus semua data terkait di tabel lain (anak)
    // Urutan ini penting untuk menghindari error foreign key

    // Hapus dari product_images
    $stmt_images = mysqli_prepare($conn, "DELETE FROM product_images WHERE product_id = ?");
    mysqli_stmt_bind_param($stmt_images, "i", $product_id);
    mysqli_stmt_execute($stmt_images);
    mysqli_stmt_close($stmt_images);

    // Hapus dari product_variants
    $stmt_variants = mysqli_prepare($conn, "DELETE FROM product_variants WHERE product_id = ?");
    mysqli_stmt_bind_param($stmt_variants, "i", $product_id);
    mysqli_stmt_execute($stmt_variants);
    mysqli_stmt_close($stmt_variants);

    // Hapus dari order_items (ini yang menyebabkan error sebelumnya)
    $stmt_order_items = mysqli_prepare($conn, "DELETE FROM order_items WHERE product_id = ?");
    mysqli_stmt_bind_param($stmt_order_items, "i", $product_id);
    mysqli_stmt_execute($stmt_order_items);
    mysqli_stmt_close($stmt_order_items);

    // Langkah 3: Setelah semua data anak dihapus, baru hapus produk utama (induk)
    $stmt_product = mysqli_prepare($conn, "DELETE FROM products WHERE id = ?");
    mysqli_stmt_bind_param($stmt_product, "i", $product_id);
    mysqli_stmt_execute($stmt_product);
    mysqli_stmt_close($stmt_product);

    // Jika semua query berhasil, commit transaksi
    mysqli_commit($conn);
    header("Location: list.php?status=delete_success");
    exit;

} catch (Exception $e) {
    // Jika terjadi kesalahan, batalkan semua perubahan
    mysqli_rollback($conn);
    header("Location: list.php?status=delete_failed&error=" . urlencode($e->getMessage()));
    exit;
}
?>
