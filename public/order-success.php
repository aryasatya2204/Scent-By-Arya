<?php require_once '../includes/auth.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil</title>
    <link href="assets/bundle.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="text-center bg-white p-10 rounded-lg shadow-xl">
        <h1 class="text-3xl font-bold text-green-500 mb-4">Terima Kasih!</h1>
        <p class="text-gray-700 mb-6">Pesanan Anda dengan nomor #<?php echo htmlspecialchars($_GET['order_id'] ?? ''); ?> telah kami terima dan akan segera diproses.</p>
        <a href="index.php" class="bg-indigo-600 text-white font-bold py-2 px-6 rounded hover:bg-indigo-700">Kembali ke Beranda</a>
    </div>
</body>
</html>