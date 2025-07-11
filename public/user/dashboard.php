<?php
require_once '../../includes/auth.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Scent By Arya</title>
    <link href="../assets/bundle.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold text-gray-800">
            Selamat Datang di Dashboard, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!
        </h1>
        <p class="mt-2 text-lg text-gray-600">Ini adalah halaman pribadi Anda.</p>

        <a href="../logout.php" class="mt-8 inline-block px-6 py-3 bg-red-600 text-white rounded-lg shadow hover:bg-red-700">
            Logout
        </a>
    </div>
</body>
</html>