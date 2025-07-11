<?php
// /adminpanel-xyz123/product/add_handler.php

session_start();
require_once '../../includes/db.php'; // PERBAIKAN: Path yang benar

// Proteksi admin dan cek metode POST
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /Scent-By-Arya/adminpanel-xyz123/login.php");
    exit;
}

// Mulai transaksi database
mysqli_begin_transaction($conn);

try {
    // 1. Simpan informasi produk utama
    $name = $_POST['name'];
    $brand = $_POST['brand'];
    $description = $_POST['description'];
    $brand_type = $_POST['brand_type'];
    $gender_id = !empty($_POST['gender_id']) ? (int)$_POST['gender_id'] : null;
    $wear_time_id = !empty($_POST['wear_time_id']) ? (int)$_POST['wear_time_id'] : null;
    $scent_id = !empty($_POST['scent_id']) ? (int)$_POST['scent_id'] : null;

    $sql_product = "INSERT INTO products (name, brand, description, brand_type, gender_id, wear_time_id, scent_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_product = mysqli_prepare($conn, $sql_product);
    mysqli_stmt_bind_param($stmt_product, "ssssiii", $name, $brand, $description, $brand_type, $gender_id, $wear_time_id, $scent_id);
    mysqli_stmt_execute($stmt_product);

    $product_id = mysqli_insert_id($conn);
    if ($product_id == 0) {
        throw new Exception("Gagal membuat produk utama.");
    }

    // 2. Proses upload gambar
    if (isset($_FILES['images'])) {
        $upload_dir = '../../public/uploads/';
        
        // PERBAIKAN: Buat folder uploads jika belum ada
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $display_order = 1;

        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $file_extension = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                $file_name = time() . '_' . $product_id . '_' . $display_order . '.' . $file_extension;
                $target_file = $upload_dir . $file_name;

                if (move_uploaded_file($tmp_name, $target_file)) {
                    $sql_image = "INSERT INTO product_images (product_id, image_url, display_order) VALUES (?, ?, ?)";
                    $stmt_image = mysqli_prepare($conn, $sql_image);
                    mysqli_stmt_bind_param($stmt_image, "isi", $product_id, $file_name, $display_order);
                    mysqli_stmt_execute($stmt_image);
                    $display_order++;
                }
            }
        }
    }

    // 3. Proses varian produk
    if (isset($_POST['variants'])) {
        $sizes = $_POST['variants']['size'];
        $prices = $_POST['variants']['price'];
        $stocks = $_POST['variants']['stock'];

        $sql_variant = "INSERT INTO product_variants (product_id, size, price, stock) VALUES (?, ?, ?, ?)";
        $stmt_variant = mysqli_prepare($conn, $sql_variant);

        foreach ($sizes as $index => $size) {
            $price = $prices[$index];
            $stock = $stocks[$index];
            if (!empty($size) && !empty($price) && isset($stock)) {
                mysqli_stmt_bind_param($stmt_variant, "isdi", $product_id, $size, $price, $stock);
                mysqli_stmt_execute($stmt_variant);
            }
        }
    }

    // Jika semua berhasil, commit transaksi
    mysqli_commit($conn);
    
    // PERBAIKAN: Redirect ke halaman list dengan pesan sukses
    header("Location: /Scent-By-Arya/adminpanel-xyz123/product/list.php?status=add_success");
    exit();

} catch (Exception $e) {
    // Jika gagal, batalkan semua perubahan
    mysqli_rollback($conn);
    header("Location: /Scent-By-Arya/adminpanel-xyz123/product/add.php?status=add_failed&error=" . urlencode($e->getMessage()));
    exit();
}
?>