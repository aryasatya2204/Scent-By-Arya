<?php
// /adminpanel-xyz123/product/add.php
$page_title = 'Tambah Produk Baru';
ob_start();
require_once '../../includes/db.php';

// Ambil data kategori untuk dropdown
$genders_result = mysqli_query($conn, "SELECT id, name FROM categories WHERE type = 'gender' ORDER BY name");
$wear_times_result = mysqli_query($conn, "SELECT id, name FROM categories WHERE type = 'wear_time' ORDER BY name");
$scents_result = mysqli_query($conn, "SELECT id, name FROM categories WHERE type = 'scent' ORDER BY name");

// PERBAIKAN: Tampilkan pesan error jika ada
if (isset($_GET['status']) && $_GET['status'] == 'add_failed') {
    $error_message = $_GET['error'] ?? 'Terjadi kesalahan saat menambahkan produk.';
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">';
    echo '<strong>Error:</strong> ' . htmlspecialchars($error_message);
    echo '</div>';
}
?>

<!-- PERBAIKAN: Tambahkan path lengkap untuk action -->
<form action="/Scent-By-Arya/adminpanel-xyz123/product/add_handler.php" method="POST" enctype="multipart/form-data">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Kiri: Info Utama & Kategori -->
        <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-lg">
            <h3 class="text-xl font-bold mb-4">Informasi Produk</h3>
            <div class="mb-4">
                <label for="name" class="block text-gray-700 font-bold mb-2">Nama Produk</label>
                <input type="text" id="name" name="name" required class="shadow-sm border rounded w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="mb-4">
                <label for="brand" class="block text-gray-700 font-bold mb-2">Brand</label>
                <input type="text" id="brand" name="brand" required class="shadow-sm border rounded w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="mb-4">
                <label for="description" class="block text-gray-700 font-bold mb-2">Deskripsi</label>
                <textarea id="description" name="description" rows="5" class="shadow-sm border rounded w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
            
            <h3 class="text-xl font-bold mb-4 mt-6">Kategori</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="gender_id" class="block text-gray-700 font-bold mb-2">Gender</label>
                    <select id="gender_id" name="gender_id" class="shadow-sm border rounded w-full py-2 px-3 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Pilih Gender</option>
                        <?php while($row = mysqli_fetch_assoc($genders_result)): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label for="brand_type" class="block text-gray-700 font-bold mb-2">Tipe Brand</label>
                    <select id="brand_type" name="brand_type" class="shadow-sm border rounded w-full py-2 px-3 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Pilih Tipe</option>
                        <option value="Lokal">Lokal</option>
                        <option value="Internasional">Internasional</option>
                    </select>
                </div>
                <div>
                    <label for="wear_time_id" class="block text-gray-700 font-bold mb-2">Waktu Pakai</label>
                    <select id="wear_time_id" name="wear_time_id" class="shadow-sm border rounded w-full py-2 px-3 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Pilih Waktu</option>
                        <?php while($row = mysqli_fetch_assoc($wear_times_result)): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label for="scent_id" class="block text-gray-700 font-bold mb-2">Aroma (Scent)</label>
                    <select id="scent_id" name="scent_id" class="shadow-sm border rounded w-full py-2 px-3 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Pilih Aroma</option>
                        <?php while($row = mysqli_fetch_assoc($scents_result)): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Gambar & Varian -->
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="text-xl font-bold mb-4">Gambar Produk</h3>
                <p class="text-sm text-gray-500 mb-2">Gambar pertama akan menjadi gambar utama. Anda bisa upload beberapa sekaligus.</p>
                <input type="file" name="images[]" multiple required accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="text-xl font-bold mb-4">Varian Produk (Ukuran, Harga, Stok)</h3>
                <div id="variants-container" class="space-y-4">
                    <div class="variant-row flex items-center gap-2 p-2 border rounded-lg">
                        <input type="text" name="variants[size][]" placeholder="Ukuran (e.g., 50ml)" required class="flex-1 shadow-sm border rounded w-full py-2 px-3">
                        <input type="number" name="variants[price][]" placeholder="Harga" required class="flex-1 shadow-sm border rounded w-full py-2 px-3">
                        <input type="number" name="variants[stock][]" placeholder="Stok" required class="flex-1 shadow-sm border rounded w-full py-2 px-3">
                    </div>
                </div>
                <button type="button" id="add-variant-btn" class="mt-4 text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg">+ Tambah Varian</button>
            </div>
        </div>
    </div>

    <div class="mt-6 flex justify-between">
    <a href="/Scent-By-Arya/adminpanel-xyz123/product/list.php" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 font-semibold shadow-sm">Batal</a>

    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-bold shadow-sm">Simpan Produk</button>
</div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addVariantBtn = document.getElementById('add-variant-btn');
    const variantsContainer = document.getElementById('variants-container');

    addVariantBtn.addEventListener('click', function() {
        const newVariantRow = document.createElement('div');
        newVariantRow.className = 'variant-row flex items-center gap-2 p-2 border rounded-lg';
        newVariantRow.innerHTML = `
            <input type="text" name="variants[size][]" placeholder="Ukuran (e.g., 30ml)" required class="flex-1 shadow-sm border rounded w-full py-2 px-3">
            <input type="number" name="variants[price][]" placeholder="Harga" required class="flex-1 shadow-sm border rounded w-full py-2 px-3">
            <input type="number" name="variants[stock][]" placeholder="Stok" required class="flex-1 shadow-sm border rounded w-full py-2 px-3">
            <button type="button" class="remove-variant-btn bg-red-500 text-white rounded-full p-1 w-6 h-6 flex items-center justify-center">&times;</button>
        `;
        variantsContainer.appendChild(newVariantRow);
    });

    // Event delegation untuk tombol hapus
    variantsContainer.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('remove-variant-btn')) {
            e.target.closest('.variant-row').remove();
        }
    });
});
</script>

<?php
$content = ob_get_clean();
require_once '../layouts/app.php';
?>