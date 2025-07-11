<?php
// /adminpanel-xyz123/product/list.php

// Tentukan judul halaman
$page_title = 'Manajemen Produk';

// Mulai "menangkap" output HTML
ob_start();

// Panggil koneksi database
require_once '../../includes/db.php';

// PERBAIKAN: Tampilkan pesan sukses jika ada
if (isset($_GET['status']) && $_GET['status'] == 'add_success') {
    echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">';
    echo '<strong>Berhasil!</strong> Produk baru telah berhasil ditambahkan.';
    echo '</div>';
}

// Ambil semua data produk
// Kita perlu JOIN dengan tabel lain untuk mendapatkan harga dan gambar utama
$sql = "SELECT 
            p.id, 
            p.name, 
            p.brand, 
            (SELECT MIN(pv.price) FROM product_variants pv WHERE pv.product_id = p.id) as starting_price,
            (SELECT pi.image_url FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.display_order ASC LIMIT 1) as main_image,
            (SELECT SUM(pv.stock) FROM product_variants pv WHERE pv.product_id = p.id) as total_stock
        FROM products p 
        ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!-- Konten spesifik untuk halaman ini -->
<div class="mb-6 text-right">
    <!-- PERBAIKAN: Gunakan path lengkap untuk tombol tambah -->
    <a href="/Scent-By-Arya/adminpanel-xyz123/product/add.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-md">
        + Tambah Produk Baru
    </a>
</div>

<div class="bg-white shadow-md rounded-lg overflow-x-auto">
    <table class="min-w-full bg-white">
        <thead class="bg-gray-800 text-white">
            <tr>
                <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Gambar</th>
                <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Nama Produk</th>
                <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Brand</th>
                <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Mulai Dari Harga</th>
                <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Total Stok</th>
                <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="text-gray-700">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-4">
                            <img src="/Scent-By-Arya/public/uploads/<?php echo htmlspecialchars($row['main_image'] ?? 'default-product.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($row['name']); ?>" 
                                 class="h-16 w-16 object-cover rounded-md"
                                 onerror="this.src='/Scent-By-Arya/public/assets/default-product.jpg'">
                        </td>
                        <td class="py-3 px-4 font-semibold"><?php echo htmlspecialchars($row['name']); ?></td>
                        <td class="py-3 px-4"><?php echo htmlspecialchars($row['brand']); ?></td>
                        <td class="py-3 px-4">Rp <?php echo number_format($row['starting_price'] ?? 0, 0, ',', '.'); ?></td>
                        <td class="py-3 px-4"><?php echo $row['total_stock'] ?? 0; ?></td>
                        <td class="py-3 px-4 text-center">
                            <a href="/Scent-By-Arya/adminpanel-xyz123/product/edit.php?id=<?php echo $row['id']; ?>" 
                               class="text-blue-500 hover:text-blue-700 font-semibold">Edit</a>
                            <a href="/Scent-By-Arya/adminpanel-xyz123/product/delete.php?id=<?php echo $row['id']; ?>" 
                               class="text-red-500 hover:text-red-700 font-semibold ml-4" 
                               onclick="return confirm('Anda yakin ingin menghapus produk ini dan semua datanya?');">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center py-6">
                        <div class="text-gray-500">
                            <p>Belum ada produk. Silakan tambah produk baru.</p>
                            <a href="/Scent-By-Arya/adminpanel-xyz123/product/add.php" 
                               class="text-indigo-600 hover:text-indigo-700 font-semibold">Tambah Produk Pertama</a>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
// Ambil konten yang sudah ditangkap
$content = ob_get_clean();

// Panggil file layout utama
require_once '../layouts/app.php';
?>