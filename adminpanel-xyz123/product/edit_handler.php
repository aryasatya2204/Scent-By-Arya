<?php
// /adminpanel-xyz123/product/edit_handler.php

session_start();
require_once '../../includes/db.php';

// Proteksi dan validasi dasar
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /Scent-By-Arya/adminpanel-xyz123/login.php");
    exit;
}

// Mulai transaksi database untuk memastikan integritas data
mysqli_begin_transaction($conn);

try {
    // 1. Update informasi produk utama di tabel 'products'
    $product_id = (int)$_POST['product_id'];
    $name = $_POST['name'];
    $brand = $_POST['brand'];
    $description = $_POST['description'];
    $brand_type = $_POST['brand_type'];
    $gender_id = !empty($_POST['gender_id']) ? (int)$_POST['gender_id'] : null;
    $wear_time_id = !empty($_POST['wear_time_id']) ? (int)$_POST['wear_time_id'] : null;
    $scent_id = !empty($_POST['scent_id']) ? (int)$_POST['scent_id'] : null;

    $sql_product = "UPDATE products SET name = ?, brand = ?, description = ?, brand_type = ?, gender_id = ?, wear_time_id = ?, scent_id = ? WHERE id = ?";
    $stmt_product = mysqli_prepare($conn, $sql_product);
    mysqli_stmt_bind_param($stmt_product, "ssssiiii", $name, $brand, $description, $brand_type, $gender_id, $wear_time_id, $scent_id, $product_id);
    mysqli_stmt_execute($stmt_product);

    // 2. Hapus gambar yang ditandai untuk dihapus
    if (!empty($_POST['images_to_delete'])) {
        $images_to_delete = explode(',', $_POST['images_to_delete']);
        $upload_dir = '../../public/uploads/';

        // Buat placeholder (?) sebanyak jumlah gambar yang akan dihapus
        $placeholders = implode(',', array_fill(0, count($images_to_delete), '?'));

        // Ambil nama file untuk dihapus dari server
        $sql_get_images = "SELECT image_url FROM product_images WHERE id IN ($placeholders)";
        $stmt_get_images = mysqli_prepare($conn, $sql_get_images);
        mysqli_stmt_bind_param($stmt_get_images, str_repeat('i', count($images_to_delete)), ...$images_to_delete);
        mysqli_stmt_execute($stmt_get_images);
        $result_images = mysqli_stmt_get_result($stmt_get_images);
        while ($row = mysqli_fetch_assoc($result_images)) {
            if ($row['image_url'] != 'default-product.jpg' && file_exists($upload_dir . $row['image_url'])) {
                unlink($upload_dir . $row['image_url']);
            }
        }

        // Hapus record dari database
        $sql_delete_images = "DELETE FROM product_images WHERE id IN ($placeholders)";
        $stmt_delete_images = mysqli_prepare($conn, $sql_delete_images);
        mysqli_stmt_bind_param($stmt_delete_images, str_repeat('i', count($images_to_delete)), ...$images_to_delete);
        mysqli_stmt_execute($stmt_delete_images);
    }

    // 3. Tambah gambar baru jika ada
    if (isset($_FILES['new_images'])) {
        $upload_dir = '../../public/uploads/';
        $display_order = 1; // Anda bisa membuat logika yang lebih kompleks untuk display order
        foreach ($_FILES['new_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['new_images']['error'][$key] === UPLOAD_ERR_OK) {
                $file_name = time() . '_' . basename($_FILES['new_images']['name'][$key]);
                if (move_uploaded_file($tmp_name, $upload_dir . $file_name)) {
                    $sql_image = "INSERT INTO product_images (product_id, image_url, display_order) VALUES (?, ?, ?)";
                    $stmt_image = mysqli_prepare($conn, $sql_image);
                    mysqli_stmt_bind_param($stmt_image, "isi", $product_id, $file_name, $display_order);
                    mysqli_stmt_execute($stmt_image);
                    $display_order++;
                }
            }
        }
    }

    // 4. Kelola Varian (Update, Tambah, Hapus)
    if (isset($_POST['variants'])) {
        $submitted_variants = $_POST['variants'];
        $existing_variant_ids = [];

        // Ambil semua ID varian yang ada di DB untuk produk ini
        $result_db_variants = mysqli_query($conn, "SELECT id FROM product_variants WHERE product_id = $product_id");
        while ($row = mysqli_fetch_assoc($result_db_variants)) {
            $existing_variant_ids[] = $row['id'];
        }

        $submitted_ids = [];

        // Loop melalui varian yang disubmit dari form
        foreach ($submitted_variants['size'] as $index => $size) {
            $variant_id = (int)$submitted_variants['id'][$index];
            $price = $submitted_variants['price'][$index];
            $stock = $submitted_variants['stock'][$index];

            if ($variant_id > 0) { // Ini adalah varian yang sudah ada (UPDATE)
                $sql_update_variant = "UPDATE product_variants SET size = ?, price = ?, stock = ? WHERE id = ? AND product_id = ?";
                $stmt_update_variant = mysqli_prepare($conn, $sql_update_variant);
                mysqli_stmt_bind_param($stmt_update_variant, "sdiii", $size, $price, $stock, $variant_id, $product_id);
                mysqli_stmt_execute($stmt_update_variant);
                $submitted_ids[] = $variant_id;
            } else { // Ini adalah varian baru (INSERT)
                $sql_insert_variant = "INSERT INTO product_variants (product_id, size, price, stock) VALUES (?, ?, ?, ?)";
                $stmt_insert_variant = mysqli_prepare($conn, $sql_insert_variant);
                mysqli_stmt_bind_param($stmt_insert_variant, "isdi", $product_id, $size, $price, $stock);
                mysqli_stmt_execute($stmt_insert_variant);
            }
        }

        // Hapus varian yang tidak ada lagi di form
        $variants_to_delete = array_diff($existing_variant_ids, $submitted_ids);
        if (!empty($variants_to_delete)) {
            $placeholders = implode(',', array_fill(0, count($variants_to_delete), '?'));
            $sql_delete_variants = "DELETE FROM product_variants WHERE id IN ($placeholders)";
            $stmt_delete_variants = mysqli_prepare($conn, $sql_delete_variants);
            mysqli_stmt_bind_param($stmt_delete_variants, str_repeat('i', count($variants_to_delete)), ...$variants_to_delete);
            mysqli_stmt_execute($stmt_delete_variants);
        }
    }

    // Jika semua proses berhasil, commit transaksi
    mysqli_commit($conn);
    header("Location: list.php?status=update_success");
    exit();
} catch (Exception $e) {
    // Jika terjadi kesalahan, batalkan semua perubahan
    mysqli_rollback($conn);
    header("Location: edit.php?id=" . $product_id . "&status=update_failed&error=" . urlencode($e->getMessage()));
    exit();
}
