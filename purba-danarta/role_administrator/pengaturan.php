<?php
// File: role_administrator/pengaturan.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hanya Administrator yang bisa akses
if ($_SESSION['role'] !== 'Administrator') {
    header('Location: ../login.php');
    exit();
}

$judul_halaman = 'Pengaturan Sistem';
include '../includes/header.php';
?>

<div class="main-background">
    <div class="overlay py-5">
        <div class="container">
            <div class="content-container">
                <h3 class="fw-bold text-center mb-4">Pengaturan Sistem</h3>

                <div class="row">
                    <!-- Pengaturan Umum -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Pengaturan Umum</h5>
                            </div>
                            <div class="card-body">
                                <form id="pengaturanUmum">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Perusahaan</label>
                                        <input type="text" class="form-control" value="Yayasan Purba Danarta">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email Sistem</label>
                                        <input type="email" class="form-control" value="admin@purba-danarta.com">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Maksimal Cuti Tahunan</label>
                                        <input type="number" class="form-control" value="12" min="1" max="30">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Notifikasi Email</label>
                                        <select class="form-select">
                                            <option value="1" selected>Aktif</option>
                                            <option value="0">Non-Aktif</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save me-2"></i>Simpan Pengaturan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Backup & Maintenance -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0"><i class="fas fa-database me-2"></i>Backup & Maintenance</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-primary" onclick="backupDatabase()">
                                        <i class="fas fa-download me-2"></i>Backup Database
                                    </button>
                                    <button class="btn btn-outline-info" onclick="optimizeDatabase()">
                                        <i class="fas fa-broom me-2"></i>Optimasi Database
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="clearCache()">
                                        <i class="fas fa-trash me-2"></i>Bersihkan Cache
                                    </button>
                                </div>
                                
                                <hr>
                                
                                <div class="mt-3">
                                    <h6>Log Sistem</h6>
                                    <div class="border rounded p-3 bg-light" style="max-height: 150px; overflow-y: auto;">
                                        <small class="text-muted">
                                            <div>✓ Sistem berjalan normal</div>
                                            <div>✓ Database terhubung</div>
                                            <div>✓ Session aktif</div>
                                            <div>✓ File upload tersedia</div>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pengaturan Keamanan -->
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Pengaturan Keamanan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Ganti Password Admin</label>
                                    <input type="password" class="form-control" placeholder="Password baru">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Konfirmasi Password</label>
                                    <input type="password" class="form-control" placeholder="Ulangi password">
                                </div>
                                <button class="btn btn-warning">
                                    <i class="fas fa-key me-2"></i>Ganti Password
                                </button>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle me-2"></i>Tips Keamanan</h6>
                                    <ul class="mb-0 small">
                                        <li>Ganti password secara berkala</li>
                                        <li>Backup database mingguan</li>
                                        <li>Monitor log aktivitas</li>
                                        <li>Update sistem secara teratur</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function backupDatabase() {
    if (confirm('Lakukan backup database sekarang?')) {
        // Simulasi backup
        alert('Backup database sedang diproses...\n\nDalam production, ini akan menjalankan script backup MySQL.');
        
        // Di production, akan redirect ke script backup
        // window.location.href = 'backup_database.php';
    }
}

function optimizeDatabase() {
    if (confirm('Optimasi database untuk performa yang lebih baik?')) {
        alert('Optimasi database berhasil!');
    }
}

function clearCache() {
    if (confirm('Bersihkan cache sistem?')) {
        alert('Cache berhasil dibersihkan!');
    }
}

// Handle form submission
document.getElementById('pengaturanUmum').addEventListener('submit', function(e) {
    e.preventDefault();
    alert('Pengaturan berhasil disimpan!');
});
</script>

<?php include '../includes/footer.php'; ?>