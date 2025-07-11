<?php
// /public/update-cart.php

session_start();

// Pastikan request adalah POST dan session cart ada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['cart'])) {

    // Cek apakah data kuantitas dikirim
    if (isset($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $product_id => $quantity) {
            $product_id = (int)$product_id;
            $quantity = (int)$quantity;

            // Hanya proses jika produk ada di keranjang
            if (isset($_SESSION['cart'][$product_id])) {
                if ($quantity > 0) {
                    // Update kuantitasnya
                    $_SESSION['cart'][$product_id] = $quantity;
                } else {
                    // Jika kuantitas 0 atau kurang, hapus item dari keranjang
                    unset($_SESSION['cart'][$product_id]);
                }
            }
        }
    }
}

// Setelah memproses, arahkan pengguna kembali ke halaman keranjang
header('Location: cart.php');
exit;
?>