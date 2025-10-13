<?php
// File: registrasi_pelamar.php

$judul_halaman = 'Registrasi Pelamar';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $judul_halaman; ?> - Purba Danarta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="main-background">
    <div class="overlay py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="form-container">
                        <h3 class="text-center fw-bold mb-4">Formulir Pendaftaran Lamaran Kerja</h3>
                        
                        <?php
                        if (session_status() === PHP_SESSION_NONE) {
                            session_start();
                        }
                        if (isset($_SESSION['regist_error'])) {
                            echo '<div class="alert alert-danger">' . $_SESSION['regist_error'] . '</div>';
                            unset($_SESSION['regist_error']);
                        }
                        if (isset($_SESSION['regist_success'])) {
                            echo '<div class="alert alert-success">' . $_SESSION['regist_success'] . '</div>';
                            unset($_SESSION['regist_success']);
                        }
                        ?>

                        <form action="proses_registrasi_pelamar.php" method="POST" enctype="multipart/form-data">
                            <!-- Data Pribadi -->
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Data Pribadi</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="nama_lengkap" class="form-label">Nama Lengkap *</label>
                                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="nik" class="form-label">NIK *</label>
                                            <input type="text" class="form-control" id="nik" name="nik" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="posisi_dilamar" class="form-label">Posisi Yang Dilamar *</label>
                                            <input type="text" class="form-control" id="posisi_dilamar" name="posisi_dilamar" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="divisi_dilamar" class="form-label">Divisi Yang Dilamar *</label>
                                            <select class="form-select" id="divisi_dilamar" name="divisi_dilamar" required>
                                                <option value="" selected disabled>Pilih Divisi</option>
                                                <option value="SDM">SDM</option>
                                                <option value="Keuangan">Keuangan</option>
                                                <option value="IT">IT</option>
                                                <option value="Marketing">Marketing</option>
                                                <option value="Operasional">Operasional</option>
                                                <option value="Training">Training</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin *</label>
                                            <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                                <option value="" selected disabled>Pilih Jenis Kelamin</option>
                                                <option value="Laki-laki">Laki-laki</option>
                                                <option value="Perempuan">Perempuan</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="agama" class="form-label">Agama *</label>
                                            <select class="form-select" id="agama" name="agama" required>
                                                <option value="" selected disabled>Pilih Agama</option>
                                                <option value="Islam">Islam</option>
                                                <option value="Kristen">Kristen</option>
                                                <option value="Katolik">Katolik</option>
                                                <option value="Hindu">Hindu</option>
                                                <option value="Buddha">Buddha</option>
                                                <option value="Konghucu">Konghucu</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="alamat_ktp" class="form-label">Alamat Sesuai KTP *</label>
                                        <textarea class="form-control" id="alamat_ktp" name="alamat_ktp" rows="3" required></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="alamat_domisili" class="form-label">Alamat Domisili *</label>
                                        <textarea class="form-control" id="alamat_domisili" name="alamat_domisili" rows="3" required></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="no_telepon" class="form-label">No. Telepon *</label>
                                            <input type="tel" class="form-control" id="no_telepon" name="no_telepon" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email *</label>
                                            <input type="email" class="form-control" id="email" name="email" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Kontak Darurat -->
                            <div class="card mb-4">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="fas fa-phone me-2"></i>Kontak Darurat</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="kontak_darurat_nama" class="form-label">Nama Kontak Darurat *</label>
                                            <input type="text" class="form-control" id="kontak_darurat_nama" name="kontak_darurat_nama" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="kontak_darurat_telepon" class="form-label">No. Telepon Kontak Darurat *</label>
                                            <input type="tel" class="form-control" id="kontak_darurat_telepon" name="kontak_darurat_telepon" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pendidikan -->
                            <div class="card mb-4">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Data Pendidikan</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="pendidikan_terakhir" class="form-label">Pendidikan Terakhir *</label>
                                            <input type="text" class="form-control" id="pendidikan_terakhir" name="pendidikan_terakhir" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="ipk" class="form-label">IPK *</label>
                                            <input type="number" step="0.01" min="0" max="4" class="form-control" id="ipk" name="ipk" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="gaji_diharapkan" class="form-label">Gaji yang Diharapkan *</label>
                                        <input type="number" class="form-control" id="gaji_diharapkan" name="gaji_diharapkan" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Dokumen -->
                            <div class="card mb-4">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0"><i class="fas fa-file me-2"></i>Dokumen Pendukung</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="surat_lamaran" class="form-label">Unggah Surat Lamaran *</label>
                                            <input type="file" class="form-control" id="surat_lamaran" name="surat_lamaran" accept=".pdf,.doc,.docx" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="cv" class="form-label">Unggah CV *</label>
                                            <input type="file" class="form-control" id="cv" name="cv" accept=".pdf,.doc,.docx" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="pas_foto" class="form-label">Unggah Pas Foto Formal *</label>
                                            <input type="file" class="form-control" id="pas_foto" name="pas_foto" accept="image/*" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="ktp" class="form-label">Unggah Kartu Identitas (Opsional)</label>
                                            <input type="file" class="form-control" id="ktp" name="ktp" accept="image/*,.pdf">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="ijazah" class="form-label">Unggah Ijazah & Transkrip Nilai *</label>
                                            <input type="file" class="form-control" id="ijazah" name="ijazah" accept=".pdf,.jpg,.jpeg,.png" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="dokumen_lain" class="form-label">Unggah Data-Data Pendukung Lainnya (Opsional)</label>
                                            <input type="file" class="form-control" id="dokumen_lain" name="dokumen_lain" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Akun Login -->
                            <div class="card mb-4">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="mb-0"><i class="fas fa-key me-2"></i>Akun Login</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="username" class="form-label">Username *</label>
                                            <input type="text" class="form-control" id="username" name="username" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="password" class="form-label">Password *</label>
                                            <input type="password" class="form-control" id="password" name="password" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Kirim Pendaftaran</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>