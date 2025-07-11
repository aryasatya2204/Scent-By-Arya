<?php
// /adminpanel-xyz123/dashboard.php
$page_title = 'Dashboard';

// Panggil koneksi database
require_once '../includes/db.php';

// --- Mengambil Data Statistik Dinamis dari Database ---

// 1. Total Penghasilan (dari pesanan yang statusnya bukan 'pending')
$sql_revenue = "SELECT SUM(total_price) as total_revenue FROM orders WHERE status != 'pending'";
$result_revenue = mysqli_query($conn, $sql_revenue);
$total_revenue = mysqli_fetch_assoc($result_revenue)['total_revenue'] ?? 0;

// 2. Total Penjualan (jumlah semua item/botol yang terjual)
$sql_items = "SELECT SUM(quantity) as total_items_sold FROM order_items";
$result_items = mysqli_query($conn, $sql_items);
$total_items_sold = mysqli_fetch_assoc($result_items)['total_items_sold'] ?? 0;

// 3. Total Pengguna (yang bukan admin)
$sql_users = "SELECT COUNT(id) as total_users FROM users WHERE role != 'admin'";
$result_users = mysqli_query($conn, $sql_users);
$total_users = mysqli_fetch_assoc($result_users)['total_users'] ?? 0;

// 4. Data untuk Grafik Penjualan (6 bulan terakhir)
$chart_labels = [];
$chart_data = [];
$sql_chart = "SELECT 
                DATE_FORMAT(order_date, '%b %Y') as month, 
                SUM(total_price) as monthly_sales 
              FROM orders 
              WHERE order_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH) AND status != 'pending'
              GROUP BY DATE_FORMAT(order_date, '%Y-%m')
              ORDER BY DATE_FORMAT(order_date, '%Y-%m')";
$result_chart = mysqli_query($conn, $sql_chart);
while ($row = mysqli_fetch_assoc($result_chart)) {
    $chart_labels[] = $row['month'];
    $chart_data[] = $row['monthly_sales'];
}
// --- Akhir Pengambilan Data ---


// Mulai "menangkap" output HTML
ob_start(); 
?>

<!-- Konten spesifik untuk halaman dashboard -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Card Penghasilan -->
    <div class="bg-white p-6 rounded-xl shadow-lg flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500">Total Penghasilan</p>
            <p class="text-3xl font-bold text-gray-800">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></p>
        </div>
        <div class="bg-green-100 p-3 rounded-full">
            <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v.01"></path></svg>
        </div>
    </div>
    <!-- Card Total Penjualan (Botol) -->
    <div class="bg-white p-6 rounded-xl shadow-lg flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500">Total Penjualan (Item)</p>
            <p class="text-3xl font-bold text-gray-800"><?php echo number_format($total_items_sold, 0, ',', '.'); ?></p>
        </div>
        <div class="bg-blue-100 p-3 rounded-full">
            <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
        </div>
    </div>
    <!-- Card Pengguna -->
    <div class="bg-white p-6 rounded-xl shadow-lg flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500">Total Pengguna</p>
            <p class="text-3xl font-bold text-gray-800"><?php echo $total_users; ?></p>
        </div>
        <div class="bg-purple-100 p-3 rounded-full">
            <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        </div>
    </div>
</div>

<div class="mt-8 bg-white p-6 rounded-xl shadow-lg">
    <h3 class="text-xl font-bold text-gray-800 mb-4">Grafik Penjualan (6 Bulan Terakhir)</h3>
    <canvas id="salesChart"></canvas>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [{
                label: 'Penjualan (Rp)',
                data: <?php echo json_encode($chart_data); ?>,
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                borderColor: 'rgba(79, 70, 229, 1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'rgba(79, 70, 229, 1)',
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value, index, values) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>

<?php
$content = ob_get_clean(); // Ambil output HTML yang "ditangkap"
require_once 'layouts/app.php'; // Panggil layout utama
?>
