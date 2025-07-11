<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$address_parts = [];
$street_part = $user['street_address'] ?? '';
if (!empty($user['address_note'])) {
    $street_part .= ' (' . htmlspecialchars($user['address_note']) . ')';
}
if (!empty(trim($street_part))) $address_parts[] = $street_part;
if (!empty($user['village'])) $address_parts[] = $user['village'];
if (!empty($user['district'])) $address_parts[] = $user['district'];
if (!empty($user['city'])) $address_parts[] = $user['city'];
if (!empty($user['province'])) $address_parts[] = $user['province'];

$formatted_address = implode(', ', $address_parts);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil</title>
    <link href="../assets/bundle.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

    <?php require_once '../../includes/navbar.php'; ?>

    <div class="container mx-auto p-4 md:p-8">
        <div class="bg-white p-8 rounded-lg shadow-md max-w-2xl mx-auto">
            <form action="edit-profile-handler.php" method="POST" enctype="multipart/form-data">
                
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold">Profil Saya</h1>
                </div>

                <!-- PERBAIKAN 1 & 2: UI Upload Gambar Baru -->
                <div class="flex flex-col items-center mb-6">
                    <label for="photo_input" class="cursor-pointer group relative">
                        <img id="image-preview" src="../uploads/profiles/<?php echo htmlspecialchars($user['photo_profile'] ?? 'default.jpg'); ?>" alt="Foto Profil" class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg group-hover:opacity-75 transition-opacity">
                        <div class="absolute inset-0 bg-black bg-opacity-50 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <span class="text-white text-sm font-semibold">Ganti Foto</span>
                        </div>
                    </label>
                    <input type="file" id="photo_input" name="photo_profile" accept="image/*" class="hidden">
                    <p class="text-sm text-gray-500 mt-2">Profil Anda</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    </div>
                    <!-- PERBAIKAN 3: Email solid & Tanggal Lahir -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly class="mt-1 block w-full px-3 py-2 border-gray-300 rounded-md shadow-sm bg-gray-200 text-gray-500 cursor-not-allowed">
                    </div>
                    <div>
                        <label for="phone_number" class="block text-sm font-medium text-gray-700">Nomor Telepon</label>
                        <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label for="birth_date" class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
                        <input type="date" id="birth_date" name="birth_date" value="<?php echo htmlspecialchars($user['birth_date'] ?? ''); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                    </div>
                </div>

                <!-- PERBAIKAN 2: Bagian Alamat -->
                <div class="mt-8">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Alamat Pengiriman</h3>
                        <button type="button" id="open-address-modal-btn" class="text-sm text-indigo-600 font-semibold hover:underline">Edit Alamat</button>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg border">
                        <p id="address-display" class="text-gray-700 leading-relaxed">
                            <?php echo !empty(trim($formatted_address)) ? htmlspecialchars($formatted_address) : 'Alamat belum diatur.'; ?>
                        </p>
                    </div>
                </div>

                <!-- PERBAIKAN 3: Tombol Aksi Bawah -->
                <div class="mt-8 pt-6 border-t flex items-center justify-between">
                    <a href="/Scent-By-Arya/public/logout.php" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-md">Keluar</a>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md">Simpan Perubahan</button>
                </div>

                <!-- Modal untuk Edit Alamat -->
                <div id="address-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 flex justify-center items-center p-4">
                    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-lg relative">
                        <button type="button" id="close-address-modal-btn" class="absolute top-4 right-4 text-gray-500 hover:text-gray-800">&times;</button>
                        <h3 class="text-2xl font-bold mb-6">Edit Alamat Pengiriman</h3>
                        
                        <!-- Input tersembunyi untuk menyimpan nama wilayah -->
                        <input type="hidden" name="province_name" id="province_name">
                        <input type="hidden" name="city_name" id="city_name">
                        <input type="hidden" name="district_name" id="district_name">
                        <input type="hidden" name="village_name" id="village_name">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="mb-4"><label for="province" class="block text-gray-700 font-bold mb-2">Provinsi</label><select id="province" name="province_id" class="shadow border rounded w-full py-2 px-3 bg-white"></select></div>
                            <div class="mb-4"><label for="city" class="block text-gray-700 font-bold mb-2">Kota/Kabupaten</label><select id="city" name="city_id" class="shadow border rounded w-full py-2 px-3 bg-white"></select></div>
                            <div class="mb-4"><label for="district" class="block text-gray-700 font-bold mb-2">Kecamatan</label><select id="district" name="district_id" class="shadow border rounded w-full py-2 px-3 bg-white"></select></div>
                            <div class="mb-4"><label for="village" class="block text-gray-700 font-bold mb-2">Kelurahan/Desa</label><select id="village" name="village_id" class="shadow border rounded w-full py-2 px-3 bg-white"></select></div>
                        </div>
                        <div class="mb-4"><label for="street_address" class="block text-gray-700 font-bold mb-2">Alamat Lengkap</label><textarea id="street_address" name="street_address" rows="3" class="shadow border rounded w-full py-2 px-3"><?php echo htmlspecialchars($user['street_address'] ?? ''); ?></textarea></div>
                        <div class="mb-4"><label for="address_note" class="block text-gray-700 font-bold mb-2">Patokan (Opsional)</label><input type="text" id="address_note" name="address_note" value="<?php echo htmlspecialchars($user['address_note'] ?? ''); ?>" class="shadow border rounded w-full py-2 px-3"></div>
                        
                        <button type="button" id="save-address-btn" class="w-full mt-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">Simpan Alamat</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
     <script>
        // Logika untuk UI Upload Gambar
        const photoInput = document.getElementById('photo_input');
        const imagePreview = document.getElementById('image-preview');
        photoInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) { imagePreview.src = e.target.result; }
                reader.readAsDataURL(file);
            }
        });

        // Logika untuk Modal Alamat
        const addressModal = document.getElementById('address-modal');
        const openModalBtn = document.getElementById('open-address-modal-btn');
        const closeModalBtn = document.getElementById('close-address-modal-btn');
        const saveAddressBtn = document.getElementById('save-address-btn');

        openModalBtn.addEventListener('click', () => addressModal.classList.remove('hidden'));
        closeModalBtn.addEventListener('click', () => addressModal.classList.add('hidden'));
        saveAddressBtn.addEventListener('click', () => {
            // Update tampilan alamat di halaman utama secara live
            const addressDisplay = document.getElementById('address-display');
            const newAddress = [
                document.getElementById('street_address').value,
                document.getElementById('village_name').value,
                document.getElementById('district_name').value,
                document.getElementById('city_name').value,
                document.getElementById('province_name').value
            ].filter(Boolean).join(', ');
            addressDisplay.textContent = newAddress || 'Alamat belum diatur.';
            addressModal.classList.add('hidden');
        });

        document.addEventListener('DOMContentLoaded', function() {
            const provinceSelect = document.getElementById('province');
            const citySelect = document.getElementById('city');
            const districtSelect = document.getElementById('district');
            const villageSelect = document.getElementById('village');
            
            const provinceNameInput = document.getElementById('province_name');
            const cityNameInput = document.getElementById('city_name');
            const districtNameInput = document.getElementById('district_name');
            const villageNameInput = document.getElementById('village_name');

            const API_BASE_URL = 'https://www.emsifa.com/api-wilayah-indonesia/api/';

            // Fungsi generik untuk fetch data dan mengisi dropdown
            async function populateDropdown(url, dropdown, placeholder) {
                dropdown.innerHTML = `<option value="">Memuat...</option>`;
                try {
                    const response = await fetch(url);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    const data = await response.json();
                    
                    dropdown.innerHTML = `<option value="">${placeholder}</option>`;
                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = item.name;
                        dropdown.appendChild(option);
                    });
                } catch (error) {
                    console.error("Gagal mengambil data:", error);
                    dropdown.innerHTML = `<option value="">Gagal memuat data</option>`;
                }
            }

            // Fungsi untuk menyimpan nama ke input tersembunyi
            function setHiddenName(selectElement, hiddenInputElement) {
                const selectedIndex = selectElement.selectedIndex;
                hiddenInputElement.value = (selectedIndex > 0) ? selectElement.options[selectedIndex].text : '';
            }

            // Event Listeners
            provinceSelect.addEventListener('change', function() {
                setHiddenName(this, provinceNameInput);
                citySelect.innerHTML = '<option value="">Pilih Kota/Kabupaten</option>';
                districtSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
                villageSelect.innerHTML = '<option value="">Pilih Kelurahan/Desa</option>';
                if (this.value) {
                    populateDropdown(API_BASE_URL + `regencies/${this.value}.json`, citySelect, 'Pilih Kota/Kabupaten');
                }
            });
            
            citySelect.addEventListener('change', function() {
                setHiddenName(this, cityNameInput);
                districtSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
                villageSelect.innerHTML = '<option value="">Pilih Kelurahan/Desa</option>';
                if (this.value) {
                    populateDropdown(API_BASE_URL + `districts/${this.value}.json`, districtSelect, 'Pilih Kecamatan');
                }
            });

             districtSelect.addEventListener('change', function() {
                setHiddenName(this, districtNameInput);
                villageSelect.innerHTML = '<option value="">Pilih Kelurahan/Desa</option>';
                if (this.value) {
                    populateDropdown(API_BASE_URL + `villages/${this.value}.json`, villageSelect, 'Pilih Kelurahan/Desa');
                }
            });

            villageSelect.addEventListener('change', function() {
                setHiddenName(this, villageNameInput);
            });

            // Inisialisasi: Muat data provinsi saat halaman pertama kali dibuka
            populateDropdown(API_BASE_URL + 'provinces.json', provinceSelect, 'Pilih Provinsi');
        });
    </script>
</body>
</html>
