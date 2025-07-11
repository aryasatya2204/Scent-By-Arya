<?php
// /adminpanel-xyz123/chat.php
session_start();
require_once '../includes/db.php';

// Proteksi halaman admin
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: /Scent-By-Arya/public/index.php");
    exit;
}

$admin_id = 1; // Asumsi ID admin selalu 1

// --- Logika untuk Memproses Pesan Balasan dari Admin ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_content'])) {
    $receiver_id = $_POST['receiver_id'];
    $message_content = trim($_POST['message_content']);

    if (!empty($message_content)) {
        $sql_insert = "INSERT INTO messages (sender_id, receiver_id, message_content) VALUES (?, ?, ?)";
        $stmt_insert = mysqli_prepare($conn, $sql_insert);
        mysqli_stmt_bind_param($stmt_insert, "iis", $admin_id, $receiver_id, $message_content);
        mysqli_stmt_execute($stmt_insert);

        // Redirect untuk refresh halaman dan menampilkan pesan baru
        header("Location: chat.php?user_id=" . $receiver_id);
        exit;
    }
}

// --- Logika untuk Menampilkan Data ---

// 1. Ambil daftar semua user yang pernah berinteraksi dengan admin
$sql_users = "SELECT DISTINCT u.id, u.name 
              FROM users u 
              JOIN messages m ON u.id = m.sender_id OR u.id = m.receiver_id 
              WHERE ? IN (m.sender_id, m.receiver_id) AND u.id != ?";
$stmt_users = mysqli_prepare($conn, $sql_users);
mysqli_stmt_bind_param($stmt_users, "ii", $admin_id, $admin_id);
mysqli_stmt_execute($stmt_users);
$result_users = mysqli_stmt_get_result($stmt_users);

// 2. Ambil ID user yang sedang dipilih dari URL
$selected_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
$messages = [];
$selected_user_name = '';

if ($selected_user_id) {
    // Ambil riwayat percakapan dengan user yang dipilih
    $sql_messages = "SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY timestamp ASC";
    $stmt_messages = mysqli_prepare($conn, $sql_messages);
    mysqli_stmt_bind_param($stmt_messages, "iiii", $admin_id, $selected_user_id, $selected_user_id, $admin_id);
    mysqli_stmt_execute($stmt_messages);
    $result_messages = mysqli_stmt_get_result($stmt_messages);
    while ($row = mysqli_fetch_assoc($result_messages)) {
        $messages[] = $row;
    }

    // Ambil nama user yang dipilih untuk ditampilkan di header chat
    $sql_user_name = "SELECT name FROM users WHERE id = ?";
    $stmt_user_name = mysqli_prepare($conn, $sql_user_name);
    mysqli_stmt_bind_param($stmt_user_name, "i", $selected_user_id);
    mysqli_stmt_execute($stmt_user_name);
    $selected_user_name = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_user_name))['name'];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Chat</title>
    <link href="/Scent-By-Arya/public/assets/bundle.css" rel="stylesheet">
</head>

<body class="bg-gray-100 h-screen">
    <div class="flex h-full">
        <aside class="w-1/3 bg-white border-r">
            <div class="p-4 border-b">
                <h1 class="text-xl font-bold">Inbox Pesan</h1>
            </div>
            <div class="overflow-y-auto h-full">
                <ul>
                    <?php while ($user = mysqli_fetch_assoc($result_users)):
                        $is_active = ($selected_user_id == $user['id']);
                        $active_class = $is_active ? 'bg-indigo-100' : '';
                    ?>
                        <li>
                            <a href="?user_id=<?php echo $user['id']; ?>" class="block p-4 border-b hover:bg-gray-50 <?php echo $active_class; ?>">
                                <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($user['name']); ?></p>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </aside>

        <main class="w-2/3 flex flex-col">
            <?php if ($selected_user_id): ?>
                <div class="p-4 border-b bg-white shadow-sm">
                    <h2 class="text-xl font-bold">Percakapan dengan <?php echo htmlspecialchars($selected_user_name); ?></h2>
                </div>

                <div class="p-6 flex-grow overflow-y-auto space-y-4">
                    <?php foreach ($messages as $msg): ?>
                        <?php if ($msg['sender_id'] == $admin_id): ?>
                            <div class="flex justify-end">
                                <div class="bg-indigo-500 text-white p-3 rounded-lg max-w-md">
                                    <p><?php echo nl2br(htmlspecialchars($msg['message_content'])); ?></p><span class="text-xs text-indigo-200 block text-right mt-1"><?php echo date('H:i', strtotime($msg['timestamp'])); ?></span>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="flex justify-start">
                                <div class="bg-gray-200 text-gray-800 p-3 rounded-lg max-w-md">
                                    <p><?php echo nl2br(htmlspecialchars($msg['message_content'])); ?></p><span class="text-xs text-gray-500 block text-right mt-1"><?php echo date('H:i', strtotime($msg['timestamp'])); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <div class="p-4 bg-white border-t">
                    <form action="chat.php?user_id=<?php echo $selected_user_id; ?>" method="POST" class="flex items-center">
                        <input type="hidden" name="receiver_id" value="<?php echo $selected_user_id; ?>">
                        <textarea name="message_content" placeholder="Ketik balasan Anda..." rows="1" class="flex-grow p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                        <button type="submit" class="ml-4 bg-indigo-600 text-white p-2 rounded-full hover:bg-indigo-700"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg></button>
                    </form>
                </div>
            <?php else: ?>
                <div class="flex items-center justify-center h-full">
                    <div class="text-center text-gray-500">
                        <p class="text-xl">Pilih sebuah percakapan dari panel kiri untuk memulai.</p>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>

</html>