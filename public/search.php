<?php
// /public/search.php
session_start();
require_once '../includes/db.php';

// Ambil kata kunci pencarian dari URL
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

// Menyiapkan query pencarian dengan JOIN untuk mendapatkan harga dan gambar
$products = [];
if (!empty($search_query)) {
    $sql = "SELECT 
                p.id, 
                p.name, 
                p.brand,
                (SELECT MAX(pv.price) FROM product_variants pv WHERE pv.product_id = p.id) as display_price,
                (SELECT pi.image_url FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.display_order ASC LIMIT 1) as image_url
            FROM products p 
            WHERE p.name LIKE ? OR p.brand LIKE ?";
            
    if ($stmt = mysqli_prepare($conn, $sql)) {
        $search_term = "%" . $search_query . "%";
        mysqli_stmt_bind_param($stmt, "ss", $search_term, $search_term);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pencarian untuk "<?php echo htmlspecialchars($search_query); ?>"</title>
    <link href="assets/bundle.css" rel="stylesheet">
</head>
<body class="bg-white flex flex-col min-h-screen">

    <?php require_once '../includes/navbar.php'; ?>

    <!-- Hasil Pencarian -->
    <main class="flex-grow container mx-auto px-6 py-12">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">
            Hasil Pencarian untuk "<?php echo htmlspecialchars($search_query); ?>"
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
            
            <?php if (!empty($products)): ?>
                <?php foreach($products as $product): ?>
                    <!-- Product Card -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden group">
                        <div class="h-64 overflow-hidden">
                           <img src="uploads/<?php echo htmlspecialchars($product['image_url'] ?? 'default-product.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                        </div>
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-800 truncate"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="text-gray-500 text-sm mb-2"><?php echo htmlspecialchars($product['brand']); ?></p>
                            <!-- PERBAIKAN: Menggunakan 'display_price' -->
                            <p class="text-xl font-bold text-indigo-600">Rp <?php echo number_format($product['display_price'] ?? 0, 0, ',', '.'); ?></p>
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="mt-4 block w-full text-center bg-gray-800 text-white py-2 rounded-md hover:bg-gray-900">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-12">
                    <p class="text-xl text-gray-500">Produk tidak ditemukan.</p>
                    <p class="text-gray-400 mt-2">Coba gunakan kata kunci lain.</p>
                </div>
            <?php endif; ?>

        </div>
    </main>
    
    <footer class="bg-gray-800 text-white py-4 mt-auto">
        <div class="container mx-auto text-center"><p>&copy; <?php echo date("Y"); ?> ScentByArya. All Rights Reserved.</p></div>
    </footer>

    <?php if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true): ?>
        <!-- ... kode modal login ... -->
    <?php endif; ?>
    <script src="assets/app.js"></script>
</body>
</html>
