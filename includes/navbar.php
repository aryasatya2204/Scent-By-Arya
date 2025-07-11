<?php
$current_page = basename($_SERVER['PHP_SELF']);
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<header class="bg-white shadow-sm sticky top-0 z-40">
    <nav class="container mx-auto px-6 h-20 flex justify-between items-center">
        <div class="w-1/5">
            <a href="/Scent-By-Arya/public/index.php" class="text-2xl font-bold text-gray-800">ScentByArya</a>
        </div>
        <div class="w-3/5 flex justify-center">
            <?php if ($current_page === 'index.php' || $current_page === 'search.php'): ?>
                <div class="w-full max-w-lg relative">
                    <form action="/Scent-By-Arya/public/search.php" method="GET" class="flex">
                        <input type="text" id="live-search-input" name="q" placeholder="Cari nama parfum, brand..." class="w-full bg-gray-100 border-transparent rounded-full py-3 px-6 pr-12 focus:outline-none focus:ring-2 focus:ring-indigo-500" autocomplete="off">
                        <button type="submit" class="absolute inset-y-0 right-0 flex items-center pr-4">
                            <svg class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" /></svg>
                        </button>
                    </form>
                    <div id="live-search-results" class="hidden absolute top-full mt-2 w-full bg-white rounded-lg shadow-lg z-50 overflow-hidden">
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="w-1/5 flex items-center justify-end space-x-6">
            <?php if ($current_page !== 'cart.php'): ?>
                <a href="/Scent-By-Arya/public/cart.php" class="relative text-gray-600 hover:text-indigo-600">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    <?php if ($cart_count > 0): ?><span class="absolute -top-2 -right-3 bg-red-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?php echo $cart_count; ?></span><?php endif; ?>
                </a>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                <?php if ($current_page !== 'chat.php'): ?>
                    <a href="/Scent-By-Arya/public/user/chat.php" class="relative text-gray-600 hover:text-indigo-600">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                    </a>
                <?php endif; ?>
            
                <a href="/Scent-By-Arya/public/user/edit-profile.php" class="flex items-center text-gray-600 hover:text-indigo-600" title="Profil Anda">
                    <?php
                        $user_photo_url = "/Scent-By-Arya/public/uploads/profiles/default.jpg"; 
                        if (isset($_SESSION['user_photo']) && $_SESSION['user_photo'] != 'default.jpg') {
                            $user_photo_path = $_SERVER['DOCUMENT_ROOT'] . '/Scent-By-Arya/public/uploads/profiles/' . $_SESSION['user_photo'];
                            if (file_exists($user_photo_path)) {
                                $user_photo_url = "/Scent-By-Arya/public/uploads/profiles/" . htmlspecialchars($_SESSION['user_photo']);
                            }
                        }
                    ?>
                    <?php if ($user_photo_url !== "/Scent-By-Arya/public/uploads/profiles/default.jpg"): ?>
                        <img src="<?php echo $user_photo_url; ?>" class="h-8 w-8 rounded-full object-cover">
                    <?php else: ?>
                        <svg class="h-7 w-7" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd" /></svg>
                    <?php endif; ?>
                </a>
            <?php else: ?>
                <button class="js-open-auth-modal text-gray-600 hover:text-indigo-600">
                     <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                </button>
            <?php endif; ?>
        </div>
    </nav>
</header>

<?php if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true): ?>
<div id="auth-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex justify-center items-center p-4 transition-opacity duration-300 opacity-0">
    <div id="auth-modal-content" class="bg-white/10 border border-white/20 rounded-2xl shadow-2xl w-full max-w-md text-white transform scale-95 opacity-0 transition-all duration-300 ease-out">
        <div class="flex justify-center pt-8">
            <div class="bg-indigo-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
        </div>
        <div id="login-form-container" class="p-8">
            <h2 class="text-3xl font-bold text-center mb-6">Login</h2>
            <form id="login-form" class="space-y-6">
                <div>
                    <label for="modal-email" class="block text-sm font-medium text-white/80">Email</label>
                    <input type="email" id="modal-email" name="email" value="<?php echo isset($_COOKIE['remember_email']) ? htmlspecialchars($_COOKIE['remember_email']) : ''; ?>" required class="mt-1 block w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white/20">
                </div>
                <div>
                    <label for="modal-password" class="block text-sm font-medium text-white/80">Password</label>
                    <input type="password" id="modal-password" name="password" required class="mt-1 block w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white/20">
                </div>
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center">
                        <input id="remember_me" name="remember_me" type="checkbox" class="h-4 w-4 bg-gray-600 border-white/30 text-indigo-600 focus:ring-indigo-500 rounded">
                        <label for="remember_me" class="ml-2 block text-white/80">Remember me</label>
                    </div>
                    <a href="#" class="font-medium text-indigo-300 hover:text-indigo-400">Forgot Password?</a>
                </div>
                <div id="login-error-message" class="text-red-400 text-sm text-center h-4"></div>
                <div><button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-gray-800 bg-white hover:bg-gray-200">Login</button></div>
                 <p class="text-sm text-center text-white/60">Don't have an account? <a href="#" id="js-show-register" class="font-bold text-indigo-300 hover:text-indigo-400">Register</a></p>
            </form>
        </div>
        <div id="register-form-container" class="hidden p-8">
             <h2 class="text-3xl font-bold text-center mb-6">Create Account</h2>
            <form id="register-form" class="space-y-6">
                <div><label for="register-name" class="block text-sm font-medium text-white/80">Nama Lengkap <span class="text-red-400">*</span></label><input type="text" id="register-name" name="name" required class="mt-1 block w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg"></div>
                <div><label for="register-email" class="block text-sm font-medium text-white/80">Email <span class="text-red-400">*</span></label><input type="email" id="register-email" name="email" required class="mt-1 block w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg"></div>
                <div><label for="register-password" class="block text-sm font-medium text-white/80">Password <span class="text-red-400">*</span></label><input type="password" id="register-password" name="password" required class="mt-1 block w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg"></div>
                <div id="register-error-message" class="text-red-400 text-sm text-center h-4"></div>
                <div><button type="submit" class="w-full flex justify-center py-3 px-4 border rounded-lg shadow-sm text-sm font-bold text-gray-800 bg-white hover:bg-gray-200">Create Account</button></div>
                 <p class="text-sm text-center text-white/60">Already have an account? <a href="#" id="js-show-login" class="font-bold text-indigo-300 hover:text-indigo-400">Login</a></p>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
