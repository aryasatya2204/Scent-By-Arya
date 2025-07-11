<?php
// /public/cart.php
session_start();
require_once '../includes/db.php';

$cart_items_details = [];
$total_price = 0;

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && !empty($_SESSION['cart'])) {
    // Ambil semua variant ID dari keranjang
    $variant_ids = array_keys($_SESSION['cart']);
    
    if(!empty($variant_ids)) {
        $placeholders = implode(',', array_fill(0, count($variant_ids), '?'));
        
        // Query kompleks untuk mengambil semua data yang dibutuhkan dalam satu kali jalan
        $sql = "SELECT 
                    pv.id as variant_id, 
                    pv.size, 
                    pv.price,
                    p.id as product_id,
                    p.name,
                    p.brand,
                    (SELECT image_url FROM product_images WHERE product_id = p.id ORDER BY display_order ASC LIMIT 1) as image_url
                FROM product_variants pv
                JOIN products p ON pv.product_id = p.id
                WHERE pv.id IN ($placeholders)";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, str_repeat('i', count($variant_ids)), ...$variant_ids);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $cart_items_details[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja</title>
    <link href="assets/bundle.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">

    <?php require_once '../includes/navbar.php'; ?>

    <main class="container mx-auto px-6 py-12 flex-grow">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Keranjang Anda</h1>

        <?php if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true): ?>
            <!-- Tampilan jika user belum login -->
            <div class="text-center bg-white p-12 rounded-lg shadow-lg">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Anda Harus Login Terlebih Dahulu</h2>
                <p class="text-gray-600 mb-6">Silakan login untuk melihat keranjang belanja Anda.</p>
                <button class="js-open-auth-modal bg-indigo-600 text-white font-bold py-3 px-8 rounded-lg hover:bg-indigo-700 transition-colors">
                    Login atau Daftar
                </button>
            </div>
        <?php elseif (empty($cart_items_details)): ?>
            <!-- Tampilan jika keranjang kosong (untuk user login) -->
            <div class="text-center bg-white p-8 rounded-lg shadow">
                <p class="text-gray-600">Keranjang Anda masih kosong.</p>
                <a href="index.php" class="mt-4 inline-block bg-indigo-600 text-white font-bold py-2 px-6 rounded hover:bg-indigo-700">Mulai Belanja</a>
            </div>
        <?php else: ?>
            <!-- Tampilan jika ada item di keranjang -->
            <div class="flex flex-col md:flex-row gap-8">
                <!-- Daftar Item -->
                <div class="w-full md:w-2/3">
                    <form action="update-cart.php" method="POST">
                        <?php foreach ($cart_items_details as $item):
                            $quantity = $_SESSION['cart'][$item['variant_id']];
                            $subtotal = $item['price'] * $quantity;
                            $total_price += $subtotal;
                        ?>
                        <div class="bg-white rounded-lg shadow p-4 mb-4 flex items-center gap-4">
                            <img src="uploads/<?php echo htmlspecialchars($item['image_url'] ?? 'default-product.jpg'); ?>" class="w-24 h-24 object-cover rounded">
                            <div class="flex-grow">
                                <h2 class="font-bold text-lg"><?php echo htmlspecialchars($item['name']); ?></h2>
                                <p class="text-gray-500 text-sm">Ukuran: <?php echo htmlspecialchars($item['size']); ?></p>
                                <p class="text-gray-600">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></p>
                                <div class="mt-2">
                                    <label for="quantity-<?php echo $item['variant_id']; ?>" class="text-sm">Jumlah:</label>
                                    <input type="number" id="quantity-<?php echo $item['variant_id']; ?>" name="quantities[<?php echo $item['variant_id']; ?>]" value="<?php echo $quantity; ?>" min="0" class="w-20 border border-gray-300 rounded-md p-1 text-center">
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold">Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></p>
                                <a href="remove-from-cart.php?id=<?php echo $item['variant_id']; ?>" class="text-red-500 hover:text-red-700 text-sm mt-2">Hapus</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <div class="text-right mt-4">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Update Keranjang</button>
                        </div>
                    </form>
                </div>
                <!-- Ringkasan Pesanan -->
                <div class="w-full md:w-1/3">
                    <div class="bg-white rounded-lg shadow p-6 sticky top-28">
                        <h2 class="text-xl font-bold mb-4">Ringkasan Pesanan</h2>
                        <div class="flex justify-between mb-2">
                            <span>Subtotal</span>
                            <span>Rp <?php echo number_format($total_price, 0, ',', '.'); ?></span>
                        </div>
                        <div class="flex justify-between mb-4">
                            <span>Ongkos Kirim</span>
                            <span>Akan dihitung</span>
                        </div>
                        <hr class="my-4">
                        <div class="flex justify-between font-bold text-lg">
                            <span>Total</span>
                            <span>Rp <?php echo number_format($total_price, 0, ',', '.'); ?></span>
                        </div>
                        <a href="checkout.php" class="mt-6 block w-full text-center bg-gray-800 text-white font-bold py-3 rounded-lg hover:bg-gray-900">Lanjutkan ke Checkout</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>
    
    <footer class="bg-gray-800 text-white py-4 mt-8">
        <div class="container mx-auto text-center"><p>&copy; <?php echo date("Y"); ?> ScentByArya. All Rights Reserved.</p></div>
    </footer>

    <script src="assets/app.js"></script>
</body>
</html>
