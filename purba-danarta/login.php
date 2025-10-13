<?php
// File: login.php (UPGRADED VERSION)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika sudah login, redirect ke dashboard sesuai role
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$judul_halaman = 'Login Sistem';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $judul_halaman; ?> - Purba Danarta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { background-color: #f0f2f5; }
        .login-card { max-width: 800px; margin: 5rem auto; box-shadow: 0 8px 30px rgba(0,0,0,0.1); border-radius: 1rem; border: none; overflow: hidden; }
        .login-form-side { padding: 2rem 3rem 3rem 3rem; }
        .login-logo-side { background-color: #ffffff; display: flex; align-items: center; justify-content: center; padding: 3rem; border-left: 1px solid #dee2e6; }
        .login-logo-side img { max-width: 150px; }
        .nav-tabs .nav-link { color: #6c757d; }
        .nav-tabs .nav-link.active { color: #0d6efd; font-weight: bold; }
    </style>
</head>
<body>

    <div class="container">
        <div class="card login-card">
            <div class="row g-0">
                <div class="col-md-7 login-form-side">
                    
                    <ul class="nav nav-tabs nav-fill mb-4" id="loginTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="internal-tab" data-bs-toggle="tab" data-bs-target="#internal-tab-pane" type="button" role="tab" aria-controls="internal-tab-pane" aria-selected="true">
                                <i class="fas fa-user-tie me-2"></i>Karyawan & Internal
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pelamar-tab" data-bs-toggle="tab" data-bs-target="#pelamar-tab-pane" type="button" role="tab" aria-controls="pelamar-tab-pane" aria-selected="false">
                                <i class="fas fa-user-plus me-2"></i>Pelamar
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="loginTabContent">
                        
                        <!-- TAB INTERNAL (Karyawan, PJ, Admin, Direksi) -->
                        <div class="tab-pane fade show active" id="internal-tab-pane" role="tabpanel" aria-labelledby="internal-tab" tabindex="0">
                            <h3 class="fw-bold mb-4">Login Internal</h3>
                            <?php
                                // Tampilkan pesan error jika ada
                                if (isset($_SESSION['login_error'])) {
                                    echo '<div class="alert alert-danger" role="alert">' . $_SESSION['login_error'] . '</div>';
                                    unset($_SESSION['login_error']);
                                }
                            ?>
                            <form action="proses_login.php" method="POST">
                                <div class="mb-3">
                                    <input type="text" class="form-control form-control-lg" name="username" placeholder="NIK/Username" required>
                                </div>
                                <div class="mb-3">
                                    <input type="password" class="form-control form-control-lg" name="password" placeholder="Password" required>
                                </div>
                                <div class="mb-3">
                                    <select class="form-select form-select-lg" name="role" required>
                                        <option value="" selected disabled>Pilih Role Anda</option>
                                        <option value="Karyawan">Karyawan</option>
                                        <option value="Penanggung Jawab">Penanggung Jawab</option>
                                        <option value="Administrator">Administrator</option>
                                        <option value="Direksi">Direksi</option>
                                    </select>
                                </div>
                                <input type="hidden" name="login_type" value="internal">
                                <button type="submit" class="btn btn-primary w-100 btn-lg">Login</button>
                            </form>
                            <div class="text-center mt-3">
                                <a href="lupa_password.php" class="small">Lupa Password?</a>
                            </div>
                        </div>

                        <!-- TAB PELAMAR -->
                        <div class="tab-pane fade" id="pelamar-tab-pane" role="tabpanel" aria-labelledby="pelamar-tab" tabindex="0">
                            <h3 class="fw-bold mb-4">Login Pelamar</h3>
                            <?php
                                if (isset($_SESSION['login_error_pelamar'])) {
                                    echo '<div class="alert alert-danger" role="alert">' . $_SESSION['login_error_pelamar'] . '</div>';
                                    unset($_SESSION['login_error_pelamar']);
                                }
                            ?>
                            <form action="proses_login_pelamar.php" method="POST">
                                <div class="mb-3">
                                    <input type="text" class="form-control form-control-lg" name="username" placeholder="Username" required>
                                </div>
                                <div class="mb-3">
                                    <input type="password" class="form-control form-control-lg" name="password" placeholder="Password" required>
                                </div>
                                <input type="hidden" name="login_type" value="pelamar">
                                <button type="submit" class="btn btn-primary w-100 btn-lg">Login</button>
                            </form>
                             <p class="mt-4 text-center small">
                                Belum punya akun? <a href="registrasi_pelamar.php">Daftar di sini</a>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-5 d-none d-md-flex login-logo-side flex-column">
                    <img src="assets/img/yys.png" alt="Logo Yayasan" class="mb-3">
                    <h4 class="fw-bold text-center text-secondary">Yayasan<br>Purba Danarta</h4>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>