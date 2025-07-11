<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: /Scent-By-Arya/adminpanel-xyz123/login.php");
    exit;
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Panel'; ?> - ScentByArya</title>
    <link href="/Scent-By-Arya/public/assets/bundle.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-gray-100 font-sans">
    <div class="flex h-screen">
        <aside class="w-64 bg-white shadow-md flex-shrink-0 flex flex-col">
            <div>
                <div class="p-6 text-center">
                    <a href="/Scent-By-Arya/adminpanel-xyz123/dashboard.php" class="text-2xl font-bold text-indigo-600">Admin Panel</a>
                </div>
                <nav class="mt-6">
                    <?php
                    $menu_items = [
                        'dashboard.php' => ['name' => 'Dashboard', 'icon' => '...'],
                        'list.php' => ['name' => 'Produk', 'icon' => '...'],
                        // PERUBAHAN: Menambahkan menu Pesanan dan Chat
                        'orders.php' => ['name' => 'Pesanan', 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>'],
                        'chat.php' => ['name' => 'Chat', 'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>'],
                        'users.php' => ['name' => 'Pengguna', 'icon' => '...']
                    ];
                    $active_page_name = '';
                    if (strpos($_SERVER['REQUEST_URI'], '/product/')) {
                        $active_page_name = 'list.php';
                    } elseif (strpos($_SERVER['REQUEST_URI'], '/user/')) {
                        $active_page_name = 'users.php';
                    } else {
                        $active_page_name = $current_page;
                    }
                    ?>
                    <?php foreach ($menu_items as $url => $item): ?>
                        <?php
                        $is_active = ($active_page_name == $url);
                        $link_class = $is_active ? 'bg-indigo-100 text-indigo-600' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900';
                        $base_url = ($url == 'list.php') ? '/Scent-By-Arya/adminpanel-xyz123/product/list.php' : (($url == 'users.php') ? '/Scent-By-Arya/adminpanel-xyz123/user/list.php' : '/Scent-By-Arya/adminpanel-xyz123/' . $url);
                        ?>
                        <a href="<?php echo $base_url; ?>" class="flex items-center px-6 py-3 text-base font-semibold rounded-lg mx-4 my-1 transition-colors duration-200 <?php echo $link_class; ?>">
                            <?php echo $item['icon']; ?>
                            <span class="ml-4"><?php echo $item['name']; ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>

            <div class="mt-auto">
                <a href="/Scent-By-Arya/adminpanel-xyz123/logout.php" class="flex items-center px-6 py-3 text-base font-semibold text-gray-600 hover:bg-gray-100 mx-4 my-4 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span class="ml-4">Logout</span>
                </a>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm p-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800"><?php echo $page_title ?? 'Dashboard'; ?></h1>
                    <div class="flex items-center">
                        <span class="mr-2"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <div class="w-10 h-10 rounded-full bg-indigo-200"></div>
                    </div>
                </div>
            </header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <?php echo $content ?? ''; ?>
            </main>
        </div>
    </div>
</body>

</html>