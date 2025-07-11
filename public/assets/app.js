document.addEventListener('DOMContentLoaded', function () {
    // Elemen-elemen Modal Autentikasi
    const authModal = document.getElementById('auth-modal');
    const authModalContent = document.getElementById('auth-modal-content');
    const openAuthButtons = document.querySelectorAll('.js-open-auth-modal');
    
    // Kontainer Form
    const loginFormContainer = document.getElementById('login-form-container');
    const registerFormContainer = document.getElementById('register-form-container');

    // Link untuk beralih form
    const showRegisterLink = document.getElementById('js-show-register');
    const showLoginLink = document.getElementById('js-show-login');
    
    // Forms
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');

    //Logika Search
    const searchInput = document.getElementById('live-search-input');
    const searchResultsContainer = document.getElementById('live-search-results');

    // Fungsi untuk membuka modal dengan animasi
    function openModal() {
        if (authModal && authModalContent) {
            authModal.classList.remove('hidden');
            void authModal.offsetWidth; // Memicu reflow browser agar transisi berjalan
            authModal.classList.remove('opacity-0');
            authModalContent.classList.remove('opacity-0', 'scale-95');
        }
    }

    // Fungsi untuk menutup modal dengan animasi
    function closeModal() {
        if (authModal && authModalContent) {
            authModal.classList.add('opacity-0');
            authModalContent.classList.add('opacity-0', 'scale-95');
            setTimeout(() => {
                authModal.classList.add('hidden');
            }, 300); // Durasi harus sama dengan `duration-300` di CSS
        }
    }

    // Tambahkan event listener ke semua tombol yang bisa membuka modal
    openAuthButtons.forEach(button => {
        button.addEventListener('click', openModal);
    });
    
    // Menutup modal jika klik di luar area kontennya
    if (authModal) {
        authModal.addEventListener('click', function(event) {
            if (event.target === authModal) {
                closeModal();
            }
        });
    }

    // Logika untuk beralih antara form Login dan Register
    if (showRegisterLink) {
        showRegisterLink.addEventListener('click', (e) => {
            e.preventDefault();
            loginFormContainer.classList.add('hidden');
            registerFormContainer.classList.remove('hidden');
        });
    }

    if (showLoginLink) {
        showLoginLink.addEventListener('click', (e) => {
            e.preventDefault();
            registerFormContainer.classList.add('hidden');
            loginFormContainer.classList.remove('hidden');
        });
    }
    
    // Handler untuk Form Login
    if (loginForm) {
        loginForm.addEventListener('submit', function (event) {
            event.preventDefault();
            const formData = new FormData(loginForm);
            const errorMessageDiv = document.getElementById('login-error-message');
            errorMessageDiv.textContent = '';

            fetch('/Scent-By-Arya/public/login-handler.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload(); // Refresh halaman untuk update UI
                } else {
                    errorMessageDiv.textContent = data.message || 'Terjadi kesalahan.';
                }
            }).catch(error => {
                console.error('Error:', error);
                errorMessageDiv.textContent = 'Tidak bisa terhubung ke server.';
            });
        });
    }

    // Handler untuk Form Register
    if (registerForm) {
        registerForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(registerForm);
            const errorMessageDiv = document.getElementById('register-error-message');
            errorMessageDiv.textContent = '';
            
            fetch('/Scent-By-Arya/public/register_handler_ajax.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // Pindah ke tab login setelah registrasi sukses
                    if (showLoginLink) showLoginLink.click();
                    registerForm.reset();
                } else {
                    errorMessageDiv.textContent = data.message || 'Terjadi kesalahan.';
                }
            }).catch(error => {
                console.error('Error:', error);
                errorMessageDiv.textContent = 'Tidak bisa terhubung ke server.';
            });
        });
    }
    
    // Handler untuk membatasi form "Tambah ke Keranjang" bagi user non-login
    const addToCartForms = document.querySelectorAll('.add-to-cart-form');
    const isGuest = document.querySelector('.js-open-auth-modal') !== null;

    addToCartForms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (isGuest) {
                event.preventDefault();
                alert('Anda harus login terlebih dahulu untuk menambahkan item ke keranjang.');
                openModal();
            }
        });
    });

    // Logika search
    if (searchInput && searchResultsContainer) {
        searchInput.addEventListener('keyup', function() {
            const query = searchInput.value;

            if (query.length > 0) {
                // Lakukan fetch ke backend
                fetch(`/Scent-By-Arya/public/live_search.php?term=${query}`)
                    .then(response => response.json())
                    .then(data => {
                        // Kosongkan hasil sebelumnya
                        searchResultsContainer.innerHTML = '';

                        if (data.length > 0) {
                            data.forEach(item => {
                                // Buat elemen link untuk setiap hasil
                                const link = document.createElement('a');
                                link.href = `/Scent-By-Arya/public/product-detail.php?id=${item.id}`;
                                link.className = 'block px-4 py-3 text-gray-700 hover:bg-gray-100';
                                link.innerHTML = `<span class="font-semibold">${item.name}</span> <span class="text-sm text-gray-500">- ${item.brand}</span>`;
                                searchResultsContainer.appendChild(link);
                            });
                            searchResultsContainer.classList.remove('hidden');
                        } else {
                            // Tampilkan pesan jika tidak ada hasil
                            searchResultsContainer.innerHTML = `<div class="px-4 py-3 text-gray-500">Produk tidak ditemukan.</div>`;
                            searchResultsContainer.classList.remove('hidden');
                        }
                    })
                    .catch(error => console.error('Error:', error));
            } else {
                // Sembunyikan jika input kosong
                searchResultsContainer.classList.add('hidden');
            }
        });

        // Sembunyikan hasil jika klik di luar area pencarian
        document.addEventListener('click', function(event) {
            if (!searchInput.contains(event.target)) {
                searchResultsContainer.classList.add('hidden');
            }
        });
    }

    // Animasi untuk memunculkan kartu produk saat di-scroll
    const productCards = document.querySelectorAll('.product-card');
    if (productCards.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.remove('opacity-0', 'translate-y-8');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        productCards.forEach(card => {
            observer.observe(card);
        });
    }
});
