<?php
// /public/checkout.php
require_once '../includes/auth.php'; // Memastikan hanya user login yang bisa akses
require_once '../includes/db.php';

// Jika keranjang kosong, redirect kembali ke halaman keranjang
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Menghitung ulang total untuk ditampilkan di ringkasan
$total_price = 0;
if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $product_ids_string = implode(',', $product_ids);
    $sql = "SELECT id, price FROM products WHERE id IN ($product_ids_string)";
    $result = mysqli_query($conn, $sql);
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[$row['id']] = $row;
    }
    foreach ($_SESSION['cart'] as $id => $quantity) {
        $total_price += $products[$id]['price'] * $quantity;
    }
}
// Panggil logika update navbar
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link href="assets/bundle.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <header class="bg-white shadow-sm">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
             <a href="index.php" class="text-xl font-bold text-gray-800">ScentByArya</a>
             </nav>
    </header>

    <main class="container mx-auto px-6 py-12">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Checkout</h1>
        <div class="flex flex-col md:flex-row gap-12">
            <div class="w-full md:w-2/3">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-bold mb-4">Alamat Pengiriman</h2>
                    <form action="checkout-handler.php" method="POST">
                        <div class="mb-4">
                            <label for="customer_name" class="block text-gray-700 font-bold mb-2">Nama Lengkap</label>
                            <input type="text" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" required class="shadow border rounded w-full py-2 px-3">
                        </div>
                        <div class="mb-4">
                            <label for="customer_phone" class="block text-gray-700 font-bold mb-2">Nomor Telepon</label>
                            <input type="tel" id="customer_phone" name="customer_phone" required class="shadow border rounded w-full py-2 px-3">
                        </div>
                        <div class="mb-4">
                            <label for="shipping_address" class="block text-gray-700 font-bold mb-2">Alamat Lengkap</label>
                            <textarea id="shipping_address" name="shipping_address" rows="4" required class="shadow border rounded w-full py-2 px-3"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-gray-800 text-white font-bold py-3 rounded-lg hover:bg-gray-900">
                            Buat Pesanan
                        </button>
                    </form>
                </div>
            </div>
            <div class="w-full md:w-1/3">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold mb-4">Ringkasan Pesanan</h2>
                    <div class="flex justify-between font-bold text-lg">
                        <span>Total</span>
                        <span>Rp <?php echo number_format($total_price, 0, ',', '.'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>