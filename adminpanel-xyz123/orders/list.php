<?php
$page_title = 'Manajemen Pesanan';
ob_start();
require_once '../../includes/db.php';

$sql = "SELECT * FROM orders ORDER BY order_date DESC";
$result = mysqli_query($conn, $sql);
?>
<div class="bg-white shadow-md rounded-lg overflow-x-auto">
    <table class="min-w-full bg-white">
        <thead class="bg-gray-800 text-white">
            <tr>
                <th class="py-3 px-4 text-left">Order ID</th>
                <th class="py-3 px-4 text-left">Nama Pelanggan</th>
                <th class="py-3 px-4 text-left">Total Harga</th>
                <th class="py-3 px-4 text-center">Status</th>
                <th class="py-3 px-4 text-left">Tanggal Pesan</th>
                <th class="py-3 px-4 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="text-gray-700">
            <?php while($order = mysqli_fetch_assoc($result)): ?>
            <tr class="border-b hover:bg-gray-50">
                <td class="py-3 px-4">#<?php echo $order['id']; ?></td>
                <td class="py-3 px-4"><?php echo htmlspecialchars($order['customer_name']); ?></td>
                <td class="py-3 px-4">Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?></td>
                <td class="py-3 px-4 text-center">
                    <form action="update_status.php" method="POST" class="inline-flex">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <select name="status" class="p-1 border rounded-md bg-white text-sm" onchange="this.form.submit()">
                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Diproses</option>
                            <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Dikirim</option>
                            <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Selesai</option>
                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                        </select>
                    </form>
                </td>
                <td class="py-3 px-4"><?php echo date('d M Y, H:i', strtotime($order['order_date'])); ?></td>
                <td class="py-3 px-4 text-center">
                    <a href="detail.php?id=<?php echo $order['id']; ?>" class="text-indigo-600 hover:underline">Lihat Detail</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
require_once '../layouts/app.php';
?>
