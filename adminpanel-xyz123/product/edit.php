<?php
// /adminpanel-xyz123/product/edit.php
$page_title = 'Edit Produk';
ob_start();
require_once '../../includes/db.php';

// Validasi ID Produk
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: list.php");
    exit;
}
$product_id = (int)$_GET['id'];

// 1. Ambil data produk utama
$stmt_product = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ?");
mysqli_stmt_bind_param($stmt_product, "i", $product_id);
mysqli_stmt_execute($stmt_product);
$product = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_product));

if (!$product) {
    echo "Produk tidak ditemukan.";
    exit;
}

// 2. Ambil semua gambar produk
$stmt_images = mysqli_prepare($conn, "SELECT * FROM product_images WHERE product_id = ? ORDER BY display_order");
mysqli_stmt_bind_param($stmt_images, "i", $product_id);
mysqli_stmt_execute($stmt_images);
$product_images = mysqli_fetch_all(mysqli_stmt_get_result($stmt_images), MYSQLI_ASSOC);

// 3. Ambil semua varian produk
$stmt_variants = mysqli_prepare($conn, "SELECT * FROM product_variants WHERE product_id = ?");
mysqli_stmt_bind_param($stmt_variants, "i", $product_id);
mysqli_stmt_execute($stmt_variants);
$product_variants = mysqli_fetch_all(mysqli_stmt_get_result($stmt_variants), MYSQLI_ASSOC);

// 4. Ambil data kategori untuk dropdown
$genders_result = mysqli_query($conn, "SELECT id, name FROM categories WHERE type = 'gender'");
$wear_times_result = mysqli_query($conn, "SELECT id, name FROM categories WHERE type = 'wear_time'");
$scents_result = mysqli_query($conn, "SELECT id, name FROM categories WHERE type = 'scent'");
?>

<form action="edit_handler.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Kiri: Info Utama & Kategori -->
        <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-lg">
            <h3 class="text-xl font-bold mb-4">Informasi Produk</h3>
            <div class="mb-4">
                <label for="name" class="block text-gray-700 font-bold mb-2">Nama Produk</label>
                <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($product['name']); ?>" class="shadow-sm border rounded w-full py-2 px-3">
            </div>
            <div class="mb-4">
                <label for="brand" class="block text-gray-700 font-bold mb-2">Brand</label>
                <input type="text" id="brand" name="brand" required value="<?php echo htmlspecialchars($product['brand']); ?>" class="shadow-sm border rounded w-full py-2 px-3">
            </div>
            <div class="mb-4">
                <label for="description" class="block text-gray-700 font-bold mb-2">Deskripsi</label>
                <textarea id="description" name="description" rows="5" class="shadow-sm border rounded w-full py-2 px-3"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            
            <h3 class="text-xl font-bold mb-4 mt-6">Kategori</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="gender_id" class="block text-gray-700 font-bold mb-2">Gender</label>
                    <select id="gender_id" name="gender_id" class="shadow-sm border rounded w-full py-2 px-3 bg-white">
                        <option value="">Pilih Gender</option>
                        <?php while($row = mysqli_fetch_assoc($genders_result)): ?>
                            <option value="<?php echo $row['id']; ?>" <?php echo ($product['gender_id'] == $row['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($row['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label for="brand_type" class="block text-gray-700 font-bold mb-2">Tipe Brand</label>
                    <select id="brand_type" name="brand_type" class="shadow-sm border rounded w-full py-2 px-3 bg-white">
                        <option value="Lokal" <?php echo ($product['brand_type'] == 'Lokal') ? 'selected' : ''; ?>>Lokal</option>
                        <option value="Internasional" <?php echo ($product['brand_type'] == 'Internasional') ? 'selected' : ''; ?>>Internasional</option>
                    </select>
                </div>
                <div>
                    <label for="wear_time_id" class="block text-gray-700 font-bold mb-2">Waktu Pakai</label>
                    <select id="wear_time_id" name="wear_time_id" class="shadow-sm border rounded w-full py-2 px-3 bg-white">
                        <option value="">Pilih Waktu</option>
                        <?php while($row = mysqli_fetch_assoc($wear_times_result)): ?>
                            <option value="<?php echo $row['id']; ?>" <?php echo ($product['wear_time_id'] == $row['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($row['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label for="scent_id" class="block text-gray-700 font-bold mb-2">Aroma (Scent)</label>
                    <select id="scent_id" name="scent_id" class="shadow-sm border rounded w-full py-2 px-3 bg-white">
                        <option value="">Pilih Aroma</option>
                        <?php while($row = mysqli_fetch_assoc($scents_result)): ?>
                            <option value="<?php echo $row['id']; ?>" <?php echo ($product['scent_id'] == $row['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($row['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Gambar & Varian -->
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="text-xl font-bold mb-4">Kelola Gambar</h3>
                <div id="image-container" class="grid grid-cols-3 gap-4 mb-4">
                    <?php foreach($product_images as $image): ?>
                        <div class="relative group">
                            <img src="/Scent-By-Arya/public/uploads/<?php echo htmlspecialchars($image['image_url']); ?>" class="w-full h-24 object-cover rounded-md">
                            <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <button type="button" class="delete-image-btn text-white text-xs bg-red-600 rounded-full p-1" data-image-id="<?php echo $image['id']; ?>">&times; Hapus</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="images_to_delete" id="images_to_delete">
                <label class="block text-gray-700 font-bold mb-2">Tambah Gambar Baru</label>
                <input type="file" name="new_images[]" multiple class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
            </div>

            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="text-xl font-bold mb-4">Kelola Varian</h3>
                <div id="variants-container" class="space-y-4">
                    <?php foreach($product_variants as $variant): ?>
                        <div class="variant-row flex items-center gap-2 p-2 border rounded-lg">
                            <input type="hidden" name="variants[id][]" value="<?php echo $variant['id']; ?>">
                            <input type="text" name="variants[size][]" placeholder="Ukuran" required value="<?php echo htmlspecialchars($variant['size']); ?>" class="flex-1 shadow-sm border rounded w-full py-2 px-3">
                            <input type="number" name="variants[price][]" placeholder="Harga" required value="<?php echo $variant['price']; ?>" class="flex-1 shadow-sm border rounded w-full py-2 px-3">
                            <input type="number" name="variants[stock][]" placeholder="Stok" required value="<?php echo $variant['stock']; ?>" class="flex-1 shadow-sm border rounded w-full py-2 px-3">
                            <button type="button" class="remove-variant-btn bg-red-500 text-white rounded-full p-1 w-6 h-6 flex items-center justify-center">&times;</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="add-variant-btn" class="mt-4 text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg">+ Tambah Varian</button>
            </div>
        </div>
    </div>

    <div class="mt-6 text-right">
        <a href="list.php" class="text-gray-600 mr-4">Batal</a>
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg shadow-md">Update Produk</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Logika untuk menambah dan menghapus varian
    const addVariantBtn = document.getElementById('add-variant-btn');
    const variantsContainer = document.getElementById('variants-container');
    addVariantBtn.addEventListener('click', function() {
        const newVariantRow = document.createElement('div');
        newVariantRow.className = 'variant-row flex items-center gap-2 p-2 border rounded-lg';
        newVariantRow.innerHTML = `
            <input type="hidden" name="variants[id][]" value="">
            <input type="text" name="variants[size][]" placeholder="Ukuran Baru" required class="flex-1 shadow-sm border rounded w-full py-2 px-3">
            <input type="number" name="variants[price][]" placeholder="Harga" required class="flex-1 shadow-sm border rounded w-full py-2 px-3">
            <input type="number" name="variants[stock][]" placeholder="Stok" required class="flex-1 shadow-sm border rounded w-full py-2 px-3">
            <button type="button" class="remove-variant-btn bg-red-500 text-white rounded-full p-1 w-6 h-6 flex items-center justify-center">&times;</button>
        `;
        variantsContainer.appendChild(newVariantRow);
    });
    variantsContainer.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('remove-variant-btn')) {
            e.target.closest('.variant-row').remove();
        }
    });

    // Logika untuk menghapus gambar
    const imageContainer = document.getElementById('image-container');
    const imagesToDeleteInput = document.getElementById('images_to_delete');
    let imagesToDelete = [];
    imageContainer.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('delete-image-btn')) {
            const imageDiv = e.target.closest('.relative');
            const imageId = e.target.getAttribute('data-image-id');
            
            // Tambahkan ID ke array dan sembunyikan gambar
            imagesToDelete.push(imageId);
            imagesToDeleteInput.value = imagesToDelete.join(',');
            imageDiv.style.display = 'none';
        }
    });
});
</script>

<?php
$content = ob_get_clean();
require_once '../layouts/app.php';
?>
