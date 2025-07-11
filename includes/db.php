<?php
$host = 'localhost';
$user = 'root'; 
$pass = '';  
$db   = 'scent_by_arya_db'; 

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>