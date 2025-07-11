<?php
session_start();
require_once '../includes/db.php';

$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$base_sql = "SELECT 
                p.*,
                (SELECT MAX(pv.price) FROM product_variants pv WHERE pv.product_id = p.id) as price,
                (SELECT pi.image_url FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.display_order ASC LIMIT 1) as image_url
            FROM products p";


// 1. Baris GENDER
$gender_filter_id = isset($_GET['gender_filter']) ? (int)$_GET['gender_filter'] : null;
$sql_gender = $base_sql;
if ($gender_filter_id) {
    $sql_gender .= " WHERE p.gender_id = ?";
}
$sql_gender .= " ORDER BY RAND() LIMIT 4";
$stmt_gender = mysqli_prepare($conn, $sql_gender);
if ($gender_filter_id) {
    mysqli_stmt_bind_param($stmt_gender, "i", $gender_filter_id);
}
mysqli_stmt_execute($stmt_gender);
$result_gender = mysqli_stmt_get_result($stmt_gender);
$genders_categories = mysqli_query($conn, "SELECT id, name FROM categories WHERE type = 'gender'");

// 2. Baris WAKTU PAKAI
$wear_time_filter_id = isset($_GET['wear_time_filter']) ? (int)$_GET['wear_time_filter'] : null;
$sql_wear_time = $base_sql;
if ($wear_time_filter_id) {
    $sql_wear_time .= " WHERE p.wear_time_id = ?";
}
$sql_wear_time .= " ORDER BY RAND() LIMIT 4";
$stmt_wear_time = mysqli_prepare($conn, $sql_wear_time);
if ($wear_time_filter_id) {
    mysqli_stmt_bind_param($stmt_wear_time, "i", $wear_time_filter_id);
}
mysqli_stmt_execute($stmt_wear_time);
$result_wear_time = mysqli_stmt_get_result($stmt_wear_time);
$wear_times_categories = mysqli_query($conn, "SELECT id, name FROM categories WHERE type = 'wear_time'");

// 3. Baris AROMA (SCENT)
$scent_filter_id = isset($_GET['scent_filter']) ? (int)$_GET['scent_filter'] : null;
$sql_scent = $base_sql;
if ($scent_filter_id) {
    $sql_scent .= " WHERE p.scent_id = ?";
}
$sql_scent .= " ORDER BY RAND() LIMIT 4";
$stmt_scent = mysqli_prepare($conn, $sql_scent);
if ($scent_filter_id) {
    mysqli_stmt_bind_param($stmt_scent, "i", $scent_filter_id);
}
mysqli_stmt_execute($stmt_scent);
$result_scent = mysqli_stmt_get_result($stmt_scent);
$scents_categories = mysqli_query($conn, "SELECT id, name FROM categories WHERE type = 'scent'");

// 4. Baris BRAND
$brand_filter_type = isset($_GET['brand_filter']) ? $_GET['brand_filter'] : null;
$sql_brand = $base_sql;
if ($brand_filter_type && in_array($brand_filter_type, ['Lokal', 'Internasional'])) {
    $sql_brand .= " WHERE p.brand_type = ?";
}
$sql_brand .= " ORDER BY RAND() LIMIT 4";
$stmt_brand = mysqli_prepare($conn, $sql_brand);
if ($brand_filter_type) {
    mysqli_stmt_bind_param($stmt_brand, "s", $brand_filter_type);
}
mysqli_stmt_execute($stmt_brand);
$result_brand = mysqli_stmt_get_result($stmt_brand);

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scent By Arya - Selamat Datang</title>
    <link href="assets/bundle.css" rel="stylesheet">
</head>

<body class="bg-gray-50">

    <header class="bg-white shadow-sm sticky top-0 z-40">
        <?php require_once '../includes/navbar.php'; ?>
    </header>

    <section class="h-screen bg-cover bg-center flex items-center justify-center text-white" style="background-image: url('https://images.unsplash.com/photo-1541643600914-78b084683601?q=80&w=1904&auto=format&fit=crop');">
        <div class="text-center bg-black bg-opacity-50 p-8 rounded-lg">
            <h1 class="text-5xl font-extrabold mb-4">Temukan Aroma Khas Anda</h1>
            <p class="text-xl mb-8">Koleksi parfum premium untuk setiap momen dalam hidup Anda.</p>
        </div>
    </section>

    <main class="container mx-auto px-6 py-12 space-y-16">

        <section>
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold text-gray-800">Berdasarkan Gender</h2>
                <div class="flex items-center space-x-2 text-sm font-semibold">
                    <?php mysqli_data_seek($genders_categories, 0);
                    while ($cat = mysqli_fetch_assoc($genders_categories)):
                        $isActive = $gender_filter_id == $cat['id'];
                        $class = $isActive ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100';
                    ?>
                        <a href="?gender_filter=<?php echo $cat['id']; ?>#gender-row" class="px-4 py-2 rounded-full border transition-colors duration-200 <?php echo $class; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </a>
                    <?php endwhile; ?>
                    <a href="index.php#gender-row" class="px-4 py-2 rounded-full border bg-white text-gray-700 border-gray-300 hover:bg-gray-100 transition-colors duration-200">Lihat Semua</a>
                </div>
            </div>
            <div id="gender-row" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
                <?php while ($product = mysqli_fetch_assoc($result_gender)): ?>
                    <div class="product-card bg-white rounded-lg shadow-lg overflow-hidden group transition-all duration-500 ease-out transform opacity-0 translate-y-8">
                        <div class="h-64 overflow-hidden"><img src="uploads/<?php echo htmlspecialchars($product['image_url'] ?? 'default-product.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"></div>
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-800 truncate"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="text-gray-500 text-sm mb-2"><?php echo htmlspecialchars($product['brand']); ?></p>
                            <p class="text-xl font-bold text-indigo-600">Rp <?php echo number_format($product['price'] ?? 0, 0, ',', '.'); ?></p><a href="product-detail.php?id=<?php echo $product['id']; ?>" class="mt-4 block w-full text-center bg-gray-800 text-white py-2 rounded-md hover:bg-gray-900">Lihat Detail</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>

        <section>
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold text-gray-800">Berdasarkan Waktu Pakai</h2>
                <div class="flex items-center space-x-2 text-sm font-semibold">
                    <?php mysqli_data_seek($wear_times_categories, 0);
                    while ($cat = mysqli_fetch_assoc($wear_times_categories)):
                        $isActive = $wear_time_filter_id == $cat['id'];
                        $class = $isActive ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100';
                    ?>
                        <a href="?wear_time_filter=<?php echo $cat['id']; ?>#wear-time-row" class="px-4 py-2 rounded-full border transition-colors duration-200 <?php echo $class; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </a>
                    <?php endwhile; ?>
                    <a href="index.php#wear-time-row" class="px-4 py-2 rounded-full border bg-white text-gray-700 border-gray-300 hover:bg-gray-100 transition-colors duration-200">Lihat Semua</a>
                </div>
            </div>
            <div id="wear-time-row" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
                <?php while ($product = mysqli_fetch_assoc($result_wear_time)): ?>
                    <div class="product-card bg-white rounded-lg shadow-lg overflow-hidden group transition-all duration-500 ease-out transform opacity-0 translate-y-8">
                        <div class="h-64 overflow-hidden"><img src="uploads/<?php echo htmlspecialchars($product['image_url'] ?? 'default-product.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"></div>
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-800 truncate"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="text-gray-500 text-sm mb-2"><?php echo htmlspecialchars($product['brand']); ?></p>
                            <p class="text-xl font-bold text-indigo-600">Rp <?php echo number_format($product['price'] ?? 0, 0, ',', '.'); ?></p><a href="product-detail.php?id=<?php echo $product['id']; ?>" class="mt-4 block w-full text-center bg-gray-800 text-white py-2 rounded-md hover:bg-gray-900">Lihat Detail</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>

        <section>
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold text-gray-800">Berdasarkan Aroma</h2>
                <div class="flex items-center space-x-2 text-sm font-semibold">
                    <?php mysqli_data_seek($scents_categories, 0);
                    while ($cat = mysqli_fetch_assoc($scents_categories)):
                        $isActive = $scent_filter_id == $cat['id'];
                        $class = $isActive ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100';
                    ?>
                        <a href="?scent_filter=<?php echo $cat['id']; ?>#scent-row" class="px-4 py-2 rounded-full border transition-colors duration-200 <?php echo $class; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </a>
                    <?php endwhile; ?>
                    <a href="index.php#scent-row" class="px-4 py-2 rounded-full border bg-white text-gray-700 border-gray-300 hover:bg-gray-100 transition-colors duration-200">Lihat Semua</a>
                </div>
            </div>
            <div id="scent-row" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
                <?php while ($product = mysqli_fetch_assoc($result_scent)): ?>
                    <div class="product-card bg-white rounded-lg shadow-lg overflow-hidden group transition-all duration-500 ease-out transform opacity-0 translate-y-8">
                        <div class="h-64 overflow-hidden"><img src="uploads/<?php echo htmlspecialchars($product['image_url'] ?? 'default-product.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"></div>
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-800 truncate"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="text-gray-500 text-sm mb-2"><?php echo htmlspecialchars($product['brand']); ?></p>
                            <p class="text-xl font-bold text-indigo-600">Rp <?php echo number_format($product['price'] ?? 0, 0, ',', '.'); ?></p><a href="product-detail.php?id=<?php echo $product['id']; ?>" class="mt-4 block w-full text-center bg-gray-800 text-white py-2 rounded-md hover:bg-gray-900">Lihat Detail</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>

        <section>
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold text-gray-800">Berdasarkan Brand</h2>
                <div class="flex items-center space-x-2 text-sm font-semibold">
                    <?php
                    $isLokalActive = $brand_filter_type == 'Lokal';
                    $classLokal = $isLokalActive ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100';
                    $isInternasionalActive = $brand_filter_type == 'Internasional';
                    $classInternasional = $isInternasionalActive ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100';
                    ?>
                    <a href="?brand_filter=Lokal#brand-row" class="px-4 py-2 rounded-full border transition-colors duration-200 <?php echo $classLokal; ?>">Brand Lokal</a>
                    <a href="?brand_filter=Internasional#brand-row" class="px-4 py-2 rounded-full border transition-colors duration-200 <?php echo $classInternasional; ?>">Internasional</a>
                    <a href="index.php#brand-row" class="px-4 py-2 rounded-full border bg-white text-gray-700 border-gray-300 hover:bg-gray-100 transition-colors duration-200">Lihat Semua</a>
                </div>
            </div>
            <div id="brand-row" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
                <?php while ($product = mysqli_fetch_assoc($result_brand)): ?>
                    <div class="product-card bg-white rounded-lg shadow-lg overflow-hidden group transition-all duration-500 ease-out transform opacity-0 translate-y-8">
                        <div class="h-64 overflow-hidden"><img src="uploads/<?php echo htmlspecialchars($product['image_url'] ?? 'default-product.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"></div>
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-800 truncate"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="text-gray-500 text-sm mb-2"><?php echo htmlspecialchars($product['brand']); ?></p>
                            <p class="text-xl font-bold text-indigo-600">Rp <?php echo number_format($product['price'] ?? 0, 0, ',', '.'); ?></p><a href="product-detail.php?id=<?php echo $product['id']; ?>" class="mt-4 block w-full text-center bg-gray-800 text-white py-2 rounded-md hover:bg-gray-900">Lihat Detail</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>

    </main>

    <footer class="bg-gray-800 text-white py-6">
        <div class="container mx-auto text-center">
            <p>&copy; 2025 ScentByArya. All Rights Reserved.</p>
        </div>
    </footer>

    <?php if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true): ?>
        <div id="login-modal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex justify-center items-center p-4">
            <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-sm">
                <div class="flex justify-end"><button id="close-login-modal" class="text-gray-500 hover:text-gray-800 text-3xl leading-none">&times;</button></div>
                <h2 class="text-2xl font-bold text-center mb-6">Login</h2>
                <form id="login-form">
                    <div class="mb-4"><label for="modal-email" class="block text-sm font-medium text-gray-700">Email</label><input type="email" id="modal-email" name="email" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"></div>
                    <div class="mb-6"><label for="modal-password" class="block text-sm font-medium text-gray-700">Password</label><input type="password" id="modal-password" name="password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"></div>
                    <div id="login-error-message" class="text-red-500 text-sm mb-4 text-center h-4"></div>
                    <div><button type="submit" class="w-full flex justify-center py-2 px-4 border rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">Login</button></div>
                </form>
                <p class="mt-8 text-center text-sm text-gray-500">Belum punya akun? <a href="register.php" class="font-semibold text-indigo-600 hover:text-indigo-500">Daftar di sini</a></p>
            </div>
        </div>
    <?php endif; ?>

    <script src="assets/app.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productCards = document.querySelectorAll('.product-card');

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.remove('opacity-0', 'translate-y-8');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1
            });

            productCards.forEach(card => {
                observer.observe(card);
            });
        });
    </script>
</body>

</html>