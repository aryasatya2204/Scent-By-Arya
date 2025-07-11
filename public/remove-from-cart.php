<?php
// /public/remove-from-cart.php

session_start();

// Pastikan ID produk ada di URL dan keranjang belanja ada
if (isset($_GET['id']) && isset($_SESSION['cart'])) {
    $product_id_to_remove = (int)$_GET['id'];

    // Hapus item dari array session cart menggunakan kuncinya (product_id)
    if (isset($_SESSION['cart'][$product_id_to_remove])) {
        unset($_SESSION['cart'][$product_id_to_remove]);
    }
}

// Setelah menghapus, arahkan pengguna kembali ke halaman keranjang
header('Location: cart.php');
exit;
?>