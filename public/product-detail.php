<?php
// /public/product-detail.php
session_start();
require_once '../includes/db.php';

// Validasi ID produk dari URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$product_id = (int)$_GET['id'];

// 1. Ambil data produk utama
$stmt_product = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ?");
mysqli_stmt_bind_param($stmt_product, "i", $product_id);
mysqli_stmt_execute($stmt_product);
$product = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_product));

if (!$product) {
    header("Location: index.php");
    exit;
}

// 2. Ambil semua gambar produk
$stmt_images = mysqli_prepare($conn, "SELECT image_url FROM product_images WHERE product_id = ? ORDER BY display_order ASC");
mysqli_stmt_bind_param($stmt_images, "i", $product_id);
mysqli_stmt_execute($stmt_images);
$product_images = mysqli_fetch_all(mysqli_stmt_get_result($stmt_images), MYSQLI_ASSOC);

// 3. Ambil semua varian produk
$stmt_variants = mysqli_prepare($conn, "SELECT * FROM product_variants WHERE product_id = ? ORDER BY price ASC");
mysqli_stmt_bind_param($stmt_variants, "i", $product_id);
mysqli_stmt_execute($stmt_variants);
$product_variants = mysqli_fetch_all(mysqli_stmt_get_result($stmt_variants), MYSQLI_ASSOC);

// Menyimpan data ke format JSON untuk digunakan oleh JavaScript
$variants_json = json_encode($product_variants);
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Scent By Arya</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'fade-in': 'fadeIn 0.3s ease-in-out',
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen antialiased">

    <?php require_once '../includes/navbar.php'; ?>

    <main class="flex-grow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                
                <!-- Kolom Kiri: Galeri Gambar -->
                <div class="flex gap-4">
                    <!-- Thumbnail Gallery -->
                    <div class="flex flex-col gap-3 w-20 flex-shrink-0">
                        <?php if (!empty($product_images)): ?>
                            <?php foreach($product_images as $index => $image): ?>
                                <img src="uploads/<?php echo htmlspecialchars($image['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?> - Image <?php echo $index + 1; ?>"
                                     class="w-20 h-20 object-cover rounded-lg cursor-pointer border-2 transition-all duration-300 hover:border-indigo-500 <?php echo $index === 0 ? 'border-indigo-500' : 'border-transparent'; ?>"
                                     onclick="changeMainImage(this, <?php echo $index; ?>)"
                                     loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>">
                            <?php endforeach; ?>
                        <?php else: ?>
                            <img src="uploads/default-product.jpg" 
                                 alt="Default Product Image"
                                 class="w-20 h-20 object-cover rounded-lg cursor-pointer border-2 border-indigo-500 transition-all duration-300"
                                 onclick="changeMainImage(this, 0)">
                        <?php endif; ?>
                    </div>
                    
                    <!-- Main Image -->
                    <div class="flex-1">
                        <?php if (!empty($product_images)): ?>
                            <img id="main-image" 
                                 src="uploads/<?php echo htmlspecialchars($product_images[0]['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 class="w-full h-96 lg:h-[600px] object-cover rounded-lg shadow-lg transition-opacity duration-300"
                                 loading="eager">
                        <?php else: ?>
                            <img id="main-image" 
                                 src="uploads/default-product.jpg" 
                                 alt="Default Product Image"
                                 class="w-full h-96 lg:h-[600px] object-cover rounded-lg shadow-lg">
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Kolom Kanan: Informasi Produk -->
                <div class="flex flex-col space-y-6">
                    
                    <!-- Brand -->
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">
                            <?php echo htmlspecialchars($product['brand']); ?>
                        </p>
                    </div>
                    
                    <!-- Nama Produk -->
                    <div>
                        <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 leading-tight">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </h1>
                    </div>
                    
                    <!-- Deskripsi -->
                    <div class="text-gray-700 leading-relaxed">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </div>
                    
                    <!-- Pilihan Ukuran -->
                    <?php if (!empty($product_variants)): ?>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Pilih Ukuran:</h3>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <?php foreach($product_variants as $variant): ?>
                                    <button type="button" 
                                            class="variant-button p-3 rounded-lg text-center font-medium border-2 border-gray-300 hover:border-indigo-500 hover:bg-gray-50 transition-all duration-300"
                                            data-variant-id="<?php echo $variant['id']; ?>"
                                            data-price="<?php echo $variant['price']; ?>"
                                            data-stock="<?php echo $variant['stock']; ?>">
                                        <?php echo htmlspecialchars($variant['size']); ?> ml
                                    </button>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Harga dan Stok -->
                            <div class="mt-4 flex items-center justify-between">
                                <div class="text-2xl font-bold text-indigo-600" id="price-display">
                                    Pilih ukuran
                                </div>
                                <div class="text-sm text-gray-600" id="stock-display">
                                    
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Form Tambah ke Keranjang -->
                    <form id="add-to-cart-form" action="cart-handler.php" method="POST" class="space-y-6">
                        <input type="hidden" id="selected-variant-id" name="variant_id" value="">
                        
                        <!-- Jumlah -->
                        <div>
                            <label class="block text-lg font-semibold text-gray-900 mb-3">Jumlah:</label>
                            <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden w-fit">
                                <button type="button" 
                                        class="w-10 h-10 flex items-center justify-center bg-gray-100 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed text-xl font-bold text-gray-600 transition-colors duration-200" 
                                        id="decrease-btn" 
                                        disabled>
                                    -
                                </button>
                                <div class="w-16 h-10 flex items-center justify-center font-semibold text-lg bg-white" id="quantity-display">
                                    1
                                </div>
                                <input type="hidden" id="quantity-input" name="quantity" value="1">
                                <button type="button" 
                                        class="w-10 h-10 flex items-center justify-center bg-gray-100 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed text-xl font-bold text-gray-600 transition-colors duration-200" 
                                        id="increase-btn" 
                                        disabled>
                                    +
                                </button>
                            </div>
                        </div>
                        
                        <!-- Button Tambah ke Keranjang -->
                        <button type="submit" 
                                id="add-to-cart-btn"
                                class="w-full bg-black text-white font-semibold py-4 px-6 rounded-lg hover:bg-gray-800 transition-colors duration-300 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg"
                                disabled>
                            Tambah kekeranjang
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto text-center">
            <p>&copy; <?php echo date("Y"); ?> ScentByArya. All Rights Reserved.</p>
        </div>
    </footer>

    <script>
        // Data varian produk dari PHP
        const productVariants = <?php echo $variants_json; ?>;
        
        // Image gallery functionality
        function changeMainImage(thumbnailElement, index) {
            const mainImage = document.getElementById('main-image');
            const allThumbnails = document.querySelectorAll('.flex-col img');
            
            // Update main image with fade effect
            mainImage.style.opacity = '0';
            
            setTimeout(() => {
                mainImage.src = thumbnailElement.src;
                mainImage.style.opacity = '1';
            }, 150);
            
            // Update thumbnail active state
            allThumbnails.forEach(thumb => {
                thumb.classList.remove('border-indigo-500');
                thumb.classList.add('border-transparent');
            });
            thumbnailElement.classList.add('border-indigo-500');
            thumbnailElement.classList.remove('border-transparent');
        }
        
        // Product variant functionality
        document.addEventListener('DOMContentLoaded', function() {
            const variantButtons = document.querySelectorAll('.variant-button');
            const priceDisplay = document.getElementById('price-display');
            const stockDisplay = document.getElementById('stock-display');
            const selectedVariantInput = document.getElementById('selected-variant-id');
            const quantityDisplay = document.getElementById('quantity-display');
            const quantityInput = document.getElementById('quantity-input');
            const decreaseBtn = document.getElementById('decrease-btn');
            const increaseBtn = document.getElementById('increase-btn');
            const addToCartBtn = document.getElementById('add-to-cart-btn');
            
            let selectedVariant = null;
            let quantity = 1;
            
            // Variant selection
            variantButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove selection from all buttons
                    variantButtons.forEach(btn => {
                        btn.classList.remove('border-indigo-500', 'bg-indigo-600', 'text-white');
                        btn.classList.add('border-gray-300', 'hover:border-indigo-500');
                    });
                    
                    // Add selection to clicked button
                    this.classList.add('border-indigo-500', 'bg-indigo-600', 'text-white');
                    this.classList.remove('border-gray-300', 'hover:border-indigo-500');
                    
                    // Get variant data
                    const variantId = this.dataset.variantId;
                    const price = this.dataset.price;
                    const stock = this.dataset.stock;
                    
                    selectedVariant = {
                        id: variantId,
                        price: parseInt(price),
                        stock: parseInt(stock)
                    };
                    
                    // Update display
                    priceDisplay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(selectedVariant.price);
                    stockDisplay.textContent = `Stok: ${selectedVariant.stock}`;
                    selectedVariantInput.value = selectedVariant.id;
                    
                    // Reset quantity
                    quantity = 1;
                    updateQuantityDisplay();
                    
                    // Enable quantity controls and add to cart button
                    decreaseBtn.disabled = false;
                    increaseBtn.disabled = false;
                    addToCartBtn.disabled = false;
                });
            });
            
            // Quantity controls
            decreaseBtn.addEventListener('click', function() {
                if (quantity > 1) {
                    quantity--;
                    updateQuantityDisplay();
                }
            });
            
            increaseBtn.addEventListener('click', function() {
                if (selectedVariant && quantity < selectedVariant.stock) {
                    quantity++;
                    updateQuantityDisplay();
                }
            });
            
            function updateQuantityDisplay() {
                quantityDisplay.textContent = quantity;
                quantityInput.value = quantity;
                
                // Update button states
                decreaseBtn.disabled = quantity <= 1;
                increaseBtn.disabled = !selectedVariant || quantity >= selectedVariant.stock;
            }
            
            // Form submission
            document.getElementById('add-to-cart-form').addEventListener('submit', function(e) {
                if (!selectedVariant) {
                    e.preventDefault();
                    alert('Silakan pilih ukuran terlebih dahulu');
                    return;
                }
                
                if (quantity <= 0 || quantity > selectedVariant.stock) {
                    e.preventDefault();
                    alert('Jumlah tidak valid');
                    return;
                }
            });
        });
    </script>
</body>
</html>