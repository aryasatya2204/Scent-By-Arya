<?php
// /public/user/chat.php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

$user_id = $_SESSION['user_id'];
$admin_id = 1; // Asumsi ID admin selalu 1

// Proses jika pengguna mengirim pesan baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty(trim($_POST['message_content']))) {
    $message_content = $_POST['message_content'];
    
    $sql_insert = "INSERT INTO messages (sender_id, receiver_id, message_content) VALUES (?, ?, ?)";
    $stmt_insert = mysqli_prepare($conn, $sql_insert);
    mysqli_stmt_bind_param($stmt_insert, "iis", $user_id, $admin_id, $message_content);
    mysqli_stmt_execute($stmt_insert);
    
    header("Location: chat.php");
    exit;
}

// Ambil riwayat percakapan antara user dan admin
$sql_fetch = "SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY timestamp ASC";
$stmt_fetch = mysqli_prepare($conn, $sql_fetch);
mysqli_stmt_bind_param($stmt_fetch, "iiii", $user_id, $admin_id, $admin_id, $user_id);
mysqli_stmt_execute($stmt_fetch);
$result_messages = mysqli_stmt_get_result($stmt_fetch);
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat dengan Admin</title>
    <link href="../assets/bundle.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex flex-col h-full">

    <?php require_once '../../includes/navbar.php'; ?>

    <!-- Konten Utama Halaman Chat -->
    <main class="flex-grow flex items-center justify-center p-4">
        <div class="w-full max-w-2xl h-[75vh] bg-white rounded-xl shadow-2xl flex flex-col">
            
            <!-- Header Chat -->
            <div class="p-4 border-b flex items-center space-x-4">
                <div class="w-12 h-12 rounded-full bg-indigo-500 text-white flex items-center justify-center font-bold text-xl">A</div>
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Admin ScentByArya</h1>
                    <p class="text-sm text-green-500">Online</p>
                </div>
            </div>

            <!-- Area Pesan (Scrollable) -->
            <div id="message-area" class="p-6 flex-grow overflow-y-auto flex flex-col space-y-4">
                <?php if (mysqli_num_rows($result_messages) > 0): ?>
                    <?php while($msg = mysqli_fetch_assoc($result_messages)): ?>
                        <?php if ($msg['sender_id'] == $user_id): ?>
                            <!-- Pesan Terkirim (dari user) -->
                            <div class="flex justify-end">
                                <div class="bg-indigo-500 text-white p-3 rounded-lg max-w-md shadow">
                                    <p><?php echo nl2br(htmlspecialchars($msg['message_content'])); ?></p>
                                    <span class="text-xs text-indigo-200 block text-right mt-1"><?php echo date('H:i', strtotime($msg['timestamp'])); ?></span>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Pesan Diterima (dari admin) -->
                            <div class="flex justify-start">
                                <div class="bg-gray-200 text-gray-800 p-3 rounded-lg max-w-md shadow">
                                    <p><?php echo nl2br(htmlspecialchars($msg['message_content'])); ?></p>
                                     <span class="text-xs text-gray-500 block text-right mt-1"><?php echo date('H:i', strtotime($msg['timestamp'])); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-center text-gray-500 m-auto">Belum ada percakapan. Mulailah mengirim pesan!</p>
                <?php endif; ?>
            </div>

            <!-- Form Input Pesan -->
            <div class="p-4 bg-gray-50 border-t rounded-b-xl">
                <form action="chat.php" method="POST" class="flex items-center">
                    <textarea name="message_content" placeholder="Ketik pesan Anda..." rows="1" class="flex-grow p-3 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" oninput="this.style.height = 'auto'; this.style.height = (this.scrollHeight) + 'px';"></textarea>
                    <button type="submit" class="ml-4 bg-indigo-600 text-white p-3 rounded-full hover:bg-indigo-700 transition-transform duration-200 hover:scale-110">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </main>
    
    <script>
        // Auto-scroll ke pesan terakhir
        document.addEventListener('DOMContentLoaded', function() {
            const messageArea = document.getElementById('message-area');
            if(messageArea) {
                messageArea.scrollTop = messageArea.scrollHeight;
            }
        });
    </script>
</body>
</html>
