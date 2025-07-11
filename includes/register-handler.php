<?php
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashed_password);
        if (mysqli_stmt_execute($stmt)) {
            echo "Registrasi berhasil! Anda akan dialihkan ke halaman login.";
            exit();
        } else {
            echo "Error: Gagal melakukan registrasi. Kemungkinan email sudah terdaftar.";
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Error: Tidak bisa menyiapkan statement SQL.";
    }
    mysqli_close($conn);
} else {
    header("Location: ../public/register.php");
    exit();
}
?>