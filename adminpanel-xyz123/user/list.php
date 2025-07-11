<?php
// /adminpanel-xyz123/user/list.php

$page_title = 'Manajemen Pengguna';
ob_start();
require_once '../../includes/db.php';

// Ambil semua pengguna yang bukan admin
$sql = "SELECT id, name, email, status, created_at FROM users WHERE role != 'admin' ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!-- Konten spesifik untuk halaman ini -->
<div class="bg-white shadow-md rounded-lg overflow-x-auto">
    <table class="min-w-full bg-white">
        <thead class="bg-gray-800 text-white">
            <tr>
                <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Nama Pengguna</th>
                <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Email</th>
                <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Status</th>
                <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Tanggal Bergabung</th>
                <th class="py-3 px-4 uppercase font-semibold text-sm text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="text-gray-700">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while($user = mysqli_fetch_assoc($result)): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-4 font-semibold"><?php echo htmlspecialchars($user['name']); ?></td>
                        <td class="py-3 px-4"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td class="py-3 px-4 text-center">
                            <?php if ($user['status'] == 'active'): ?>
                                <span class="bg-green-200 text-green-800 py-1 px-3 rounded-full text-xs">Aktif</span>
                            <?php else: ?>
                                <span class="bg-red-200 text-red-800 py-1 px-3 rounded-full text-xs">Diblokir</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-4"><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                        <td class="py-3 px-4 text-center">
                            <?php if ($user['status'] == 'active'): ?>
                                <a href="action_handler.php?action=block&id=<?php echo $user['id']; ?>" class="text-yellow-600 hover:text-yellow-800 font-semibold">Blokir</a>
                            <?php else: ?>
                                <a href="action_handler.php?action=activate&id=<?php echo $user['id']; ?>" class="text-green-600 hover:text-green-800 font-semibold">Aktifkan</a>
                            <?php endif; ?>
                            <a href="action_handler.php?action=delete&id=<?php echo $user['id']; ?>" class="text-red-500 hover:text-red-700 font-semibold ml-4" onclick="return confirm('PERINGATAN: Menghapus pengguna akan menghapus semua data terkait (pesanan, dll). Anda yakin?');">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center py-6">Tidak ada pengguna yang terdaftar.</td>
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
