<?php
// /public/cart-handler.php
session_start();

// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Pastikan request adalah POST dan data yang dibutuhkan ada
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['variant_id']) && is_numeric($_POST['variant_id']) && isset($_POST['quantity']) && is_numeric($_POST['quantity'])) {
        
        $variant_id = (int)$_POST['variant_id'];
        $quantity = (int)$_POST['quantity'];

        // Jika varian produk sudah ada di keranjang, tambahkan kuantitasnya
        // Jika belum, tambahkan sebagai item baru
        if (isset($_SESSION['cart'][$variant_id])) {
            $_SESSION['cart'][$variant_id] += $quantity;
        } else {
            $_SESSION['cart'][$variant_id] = $quantity;
        }
    }
}

// Setelah memproses, arahkan pengguna ke halaman keranjang
header('Location: cart.php');
exit;
