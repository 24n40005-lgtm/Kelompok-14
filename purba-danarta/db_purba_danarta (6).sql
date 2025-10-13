-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 13, 2025 at 07:46 AM
-- Server version: 10.4.22-MariaDB
-- PHP Version: 7.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_purba_danarta`
--

-- --------------------------------------------------------

--
-- Table structure for table `jenis_cuti`
--

CREATE TABLE `jenis_cuti` (
  `id` int(11) NOT NULL,
  `nama_jenis` varchar(100) NOT NULL,
  `maks_hari` int(11) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `jenis_cuti`
--

INSERT INTO `jenis_cuti` (`id`, `nama_jenis`, `maks_hari`, `deskripsi`) VALUES
(1, 'Lustrum', NULL, 'Cuti lustrum berdasarkan masa kerja'),
(2, 'Tahunan', 12, 'Cuti tahunan'),
(3, 'Cuti Sakit', NULL, 'Cuti karena sakit'),
(4, 'Cuti di Luar Tanggungan', NULL, 'Cuti tanpa dibayar'),
(5, 'Anggota Keluarga Dalam 1 Rumah Meninggal', 1, 'Maksimal 1 hari'),
(6, 'Istri Melahirkan/Keguguran', 2, 'Maksimal 2 hari'),
(7, 'Anak Menikah/Baptis/Khitan', 2, 'Maksimal 2 hari'),
(8, 'Suami/Istri, Anak/Menantu, Orangtua/Mertua Meninggal', 2, 'Maksimal 2 hari'),
(9, 'Menikah', 3, 'Maksimal 3 hari'),
(10, 'Cek Kesehatan/Pindah Rumah', 1, 'Maksimal 1 hari'),
(11, 'Cuti Ibadah', NULL, 'Cuti untuk keperluan ibadah');

-- --------------------------------------------------------

--
-- Table structure for table `karyawan`
--

CREATE TABLE `karyawan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nik` varchar(20) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `divisi` varchar(100) DEFAULT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') DEFAULT NULL,
  `alamat_ktp` text DEFAULT NULL,
  `alamat_domisili` text DEFAULT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `agama` enum('Islam','Kristen','Katolik','Hindu','Buddha','Konghucu') DEFAULT NULL,
  `kontak_darurat_nama` varchar(100) DEFAULT NULL,
  `kontak_darurat_telepon` varchar(20) DEFAULT NULL,
  `pendidikan_terakhir` varchar(100) DEFAULT NULL,
  `ipk` decimal(3,2) DEFAULT NULL,
  `path_pas_foto` varchar(255) DEFAULT NULL,
  `path_ktp` varchar(255) DEFAULT NULL,
  `path_ijazah` varchar(255) DEFAULT NULL,
  `path_dokumen_lain` varchar(255) DEFAULT NULL,
  `tanggal_masuk` date DEFAULT NULL,
  `status_karyawan` enum('Aktif','Non-Aktif') DEFAULT 'Aktif',
  `sisa_cuti_tahunan` int(11) DEFAULT 12,
  `sisa_cuti_lustrum` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `karyawan`
--

INSERT INTO `karyawan` (`id`, `user_id`, `nik`, `nama_lengkap`, `divisi`, `jabatan`, `jenis_kelamin`, `alamat_ktp`, `alamat_domisili`, `no_telepon`, `email`, `agama`, `kontak_darurat_nama`, `kontak_darurat_telepon`, `pendidikan_terakhir`, `ipk`, `path_pas_foto`, `path_ktp`, `path_ijazah`, `path_dokumen_lain`, `tanggal_masuk`, `status_karyawan`, `sisa_cuti_tahunan`, `sisa_cuti_lustrum`, `created_at`) VALUES
(1, 30, '202410001', 'Bian Christy', 'Marketing', 'Staff Marketing', 'Perempuan', 'Jl. KTP Bian No. 789, Jakarta', 'Jl. Domisili Bian No. 101, Jakarta', '08111222333', 'bian.karyawan@purba-danarta.com', 'Katolik', 'Budi Christy', '08111333444', 'S1 Manajemen', '3.25', NULL, NULL, NULL, NULL, '2025-10-13', 'Aktif', 10, 5, '2025-10-13 03:15:51'),
(2, 31, '202410002', 'Naupal', 'Penanggung Jawab', 'Manager Training', 'Laki-laki', 'Jl. KTP Naupal No. 222, Jakarta', 'Jl. Domisili Naupal No. 333, Jakarta', '08122444888', 'naupal.pj@purba-danarta.com', 'Islam', 'Siti Naupal', '08122555999', 'S2 Pendidikan', '3.75', NULL, NULL, NULL, NULL, '2025-10-13', 'Aktif', 12, 8, '2025-10-13 03:15:51'),
(3, 32, '202410003', 'Malcolm', 'SDM', 'HR Manager', 'Laki-laki', 'Jl. KTP Malcolm No. 444, Jakarta', 'Jl. Domisili Malcolm No. 555, Jakarta', '08133666777', 'malcolm@purba-danarta.com', 'Islam', 'Admin Darurat', '08133777888', 'S1 Psikologi', '3.60', NULL, NULL, NULL, NULL, '2025-10-13', 'Aktif', 12, 10, '2025-10-13 03:15:51'),
(4, 33, '202410004', 'Budi', 'Direksi', 'Direktur', 'Laki-laki', 'Jl. KTP Budi No. 666, Jakarta', 'Jl. Domisili Budi No. 777, Jakarta', '08144888999', 'budi@purba-danarta.com', 'Kristen', 'Sekretaris Budi', '08144999000', 'S2 Manajemen', '3.80', NULL, NULL, NULL, NULL, '2025-10-13', 'Aktif', 15, 12, '2025-10-13 03:15:51'),
(5, 34, '202410005', 'Aan Kundra', 'Training', 'Trainer', 'Laki-laki', 'Jl. KTP Aan No. 888, Jakarta', 'Jl. Domisili Aan No. 999, Jakarta', '08155000111', 'aan@purba-danarta.com', 'Islam', 'Kontak Aan', '08155111222', 'S1 Pendidikan', '3.40', NULL, NULL, NULL, NULL, '2025-10-13', 'Aktif', 8, 3, '2025-10-13 03:15:51'),
(6, 35, '202410006', 'Caca Angga', 'Training', 'Training Coordinator', 'Perempuan', 'Jl. KTP Caca No. 111, Jakarta', 'Jl. Domisili Caca No. 222, Jakarta', '08166000222', 'caca@purba-danarta.com', 'Islam', 'Kontak Caca', '08166111333', 'S1 Komunikasi', '3.35', NULL, NULL, NULL, NULL, '2025-10-13', 'Aktif', 9, 4, '2025-10-13 03:15:51'),
(7, 36, '202410007', 'Ady Beken', 'IT', 'Programmer', 'Laki-laki', 'Jl. KTP Ady No. 333, Jakarta', 'Jl. Domisili Ady No. 444, Jakarta', '08177000333', 'ady@purba-danarta.com', 'Kristen', 'Kontak Ady', '08177111444', 'S1 Teknik Informatika', '3.50', NULL, NULL, NULL, NULL, '2025-10-13', 'Aktif', 7, 2, '2025-10-13 03:15:51'),
(8, 37, '202410008', 'Siti Rahma', 'SDM', 'HR Staff', 'Perempuan', 'Jl. KTP Siti No. 555, Jakarta', 'Jl. Domisili Siti No. 666, Jakarta', '08188000444', 'siti@purba-danarta.com', 'Islam', 'Kontak Siti', '08188111555', 'S1 Psikologi', '3.45', NULL, NULL, NULL, NULL, '2025-10-13', 'Aktif', 11, 6, '2025-10-13 03:15:51');

-- --------------------------------------------------------

--
-- Table structure for table `pelamar`
--

CREATE TABLE `pelamar` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `nik` varchar(20) DEFAULT NULL,
  `posisi_dilamar` varchar(100) DEFAULT NULL,
  `divisi_dilamar` varchar(100) DEFAULT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') DEFAULT NULL,
  `alamat_ktp` text DEFAULT NULL,
  `alamat_domisili` text DEFAULT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `agama` enum('Islam','Kristen','Katolik','Hindu','Buddha','Konghucu') DEFAULT NULL,
  `kontak_darurat_nama` varchar(100) DEFAULT NULL,
  `kontak_darurat_telepon` varchar(20) DEFAULT NULL,
  `pendidikan_terakhir` varchar(100) DEFAULT NULL,
  `ipk` decimal(3,2) DEFAULT NULL,
  `gaji_diharapkan` decimal(12,2) DEFAULT NULL,
  `path_surat_lamaran` varchar(255) DEFAULT NULL,
  `path_cv` varchar(255) DEFAULT NULL,
  `path_pas_foto` varchar(255) DEFAULT NULL,
  `path_ktp` varchar(255) DEFAULT NULL,
  `path_ijazah` varchar(255) DEFAULT NULL,
  `path_dokumen_lain` varchar(255) DEFAULT NULL,
  `status_lamaran` enum('Administrasi','Wawancara','Psikotes','Kesehatan','Diterima','Ditolak') DEFAULT 'Administrasi',
  `catatan_admin` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pelamar`
--

INSERT INTO `pelamar` (`id`, `user_id`, `nama_lengkap`, `nik`, `posisi_dilamar`, `divisi_dilamar`, `jenis_kelamin`, `alamat_ktp`, `alamat_domisili`, `no_telepon`, `email`, `agama`, `kontak_darurat_nama`, `kontak_darurat_telepon`, `pendidikan_terakhir`, `ipk`, `gaji_diharapkan`, `path_surat_lamaran`, `path_cv`, `path_pas_foto`, `path_ktp`, `path_ijazah`, `path_dokumen_lain`, `status_lamaran`, `catatan_admin`, `created_at`) VALUES
(1, 29, 'Ventyo Wijarnarko', '3578011501950002', 'Staff Marketing', 'Marketing', 'Laki-laki', 'Jl. Raya Darmo Permai III No. 15, Surabaya', 'Jl. Raya Darmo Permai III No. 15, Surabaya', '081234567890', 'ventyo.wijarnarko@email.com', 'Islam', 'Sari Wijarnarko', '081298765432', 'S1 Manajemen', '3.55', '7500000.00', NULL, NULL, NULL, NULL, NULL, NULL, 'Wawancara', NULL, '2025-10-13 03:15:51');

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan_cuti`
--

CREATE TABLE `pengajuan_cuti` (
  `id` int(11) NOT NULL,
  `karyawan_nik` varchar(20) DEFAULT NULL,
  `jenis_cuti_id` int(11) DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `lama_hari` int(11) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `status` enum('Menunggu','Disetujui','Ditolak') DEFAULT 'Menunggu',
  `alasan_penolakan` text DEFAULT NULL,
  `disetujui_oleh` varchar(100) DEFAULT NULL,
  `tanggal_pengajuan` timestamp NOT NULL DEFAULT current_timestamp(),
  `tanggal_persetujuan` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pengajuan_cuti`
--

INSERT INTO `pengajuan_cuti` (`id`, `karyawan_nik`, `jenis_cuti_id`, `tanggal_mulai`, `tanggal_selesai`, `lama_hari`, `keterangan`, `status`, `alasan_penolakan`, `disetujui_oleh`, `tanggal_pengajuan`, `tanggal_persetujuan`) VALUES
(3, '202410001', 1, '2024-12-20', '2024-12-22', 3, 'Liburan keluarga', 'Disetujui', NULL, NULL, '2025-10-13 03:15:51', NULL),
(4, '202410001', 3, '2025-01-10', '2025-01-10', 1, 'Kontrol dokter', '', NULL, NULL, '2025-10-13 03:15:51', NULL),
(5, '202410005', 1, '2024-12-15', '2024-12-17', 3, 'Cuti tahunan untuk liburan', 'Disetujui', NULL, NULL, '2025-10-13 03:15:51', NULL),
(6, '202410006', 3, '2024-12-18', '2024-12-18', 1, 'Cuti sakit', '', NULL, NULL, '2025-10-13 03:15:51', NULL),
(7, '202410007', 4, '2024-12-24', '2024-12-24', 1, 'Cuti ibadah Natal', '', NULL, NULL, '2025-10-13 03:15:51', NULL),
(8, '202410008', 6, '2024-12-20', '2024-12-21', 2, 'Istri melahirkan', 'Disetujui', NULL, NULL, '2025-10-13 03:15:51', NULL),
(9, '202410001', 2, '2025-10-24', '2025-10-31', 8, 'keluar kota', '', NULL, NULL, '2025-10-13 03:52:53', NULL),
(10, '202410005', 1, '2025-10-23', '2025-10-24', 2, 'luar kota', '', NULL, NULL, '2025-10-13 03:56:01', NULL),
(11, '202410005', 3, '2025-10-14', '2025-10-15', 2, 'Badan Panas', '', NULL, NULL, '2025-10-13 04:45:28', NULL),
(12, '202410006', 3, '2025-10-13', '2025-10-14', 2, 'badan panas', '', NULL, NULL, '2025-10-13 04:53:13', NULL),
(13, '202410006', 4, '2025-10-15', '2025-10-17', 3, 'p', '', NULL, NULL, '2025-10-13 04:53:33', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan_khl`
--

CREATE TABLE `pengajuan_khl` (
  `id` int(11) NOT NULL,
  `karyawan_nik` varchar(20) DEFAULT NULL,
  `proyek` text NOT NULL,
  `tanggal_kerja` date NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `tanggal_libur_pengganti` date NOT NULL,
  `status` enum('Menunggu','Disetujui','Ditolak') DEFAULT 'Menunggu',
  `alasan_penolakan` text DEFAULT NULL,
  `disetujui_oleh` varchar(100) DEFAULT NULL,
  `tanggal_pengajuan` timestamp NOT NULL DEFAULT current_timestamp(),
  `tanggal_persetujuan` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pengajuan_khl`
--

INSERT INTO `pengajuan_khl` (`id`, `karyawan_nik`, `proyek`, `tanggal_kerja`, `jam_mulai`, `jam_selesai`, `tanggal_libur_pengganti`, `status`, `alasan_penolakan`, `disetujui_oleh`, `tanggal_pengajuan`, `tanggal_persetujuan`) VALUES
(5, '202410005', 'Training Internal Staff', '2024-12-14', '08:00:00', '17:00:00', '2024-12-21', '', NULL, NULL, '2025-10-13 03:15:51', NULL),
(6, '202410007', 'Maintenance Server', '2024-12-15', '09:00:00', '15:00:00', '2024-12-22', 'Disetujui', NULL, NULL, '2025-10-13 03:15:51', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `proses_seleksi`
--

CREATE TABLE `proses_seleksi` (
  `id` int(11) NOT NULL,
  `pelamar_id` int(11) DEFAULT NULL,
  `tahap` enum('Administrasi','Wawancara','Psikotes','Kesehatan','Final') DEFAULT NULL,
  `tanggal_tahap` date DEFAULT NULL,
  `hasil` enum('Lolos','Tidak Lolos','Menunggu') DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `proses_seleksi`
--

INSERT INTO `proses_seleksi` (`id`, `pelamar_id`, `tahap`, `tanggal_tahap`, `hasil`, `catatan`, `created_at`) VALUES
(3, 1, 'Administrasi', NULL, 'Lolos', 'Berkas lengkap, pengalaman magang di bidang marketing', '2025-10-13 03:15:51'),
(4, 1, 'Wawancara', NULL, 'Menunggu', 'Jadwal wawancara: 27 Des 2024, 09:00 WIB dengan HRD', '2025-10-13 03:15:51');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nama_role` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `nama_role`, `deskripsi`) VALUES
(1, 'Pelamar', 'User yang mendaftar sebagai calon karyawan'),
(2, 'Karyawan', 'Karyawan biasa'),
(3, 'Penanggung Jawab', 'Supervisor/Manager divisi'),
(4, 'Administrator', 'HRD/Admin sistem'),
(5, 'Direksi', 'Direktur/Manajemen atas');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `created_at`) VALUES
(29, 'ventyo9501', '$2y$10$R1lOt1WyEfmSb.lR5Og3ee6aJFzrFDIpf9qgkovZR1PESWDtg5ql.', 'ventyo.wijarnarko@email.com', '2025-10-13 03:15:51'),
(30, 'bian9202', '$2y$10$eVCBqAZpnMOg7CEdhd3OJ.cQDtubX3EVK1tF3ZOgjOXlEi9k.YLrW', 'bian.karyawan@purba-danarta.com', '2025-10-13 03:15:51'),
(31, 'naupal8503', '$2y$10$lUi16epZzcFHm/d1E.uH4uMr//PWUalz4jxJi2FtOgiMdRt5/pIDy', 'naupal.pj@purba-danarta.com', '2025-10-13 03:15:51'),
(32, 'malcolm8804', '$2y$10$1fe8K8x6NQaA2LrnivvUgupxZ/y6KC2PJmkbO4rlFFhdh1xQ9h/fS', 'malcolm@purba-danarta.com', '2025-10-13 03:15:51'),
(33, 'budi7805', '$2y$10$FlBoud/aFG0UKlQ8gGVE7u0HPYPf07dQq0g2tryVUgErIK8cTOPEm', 'budi@purba-danarta.com', '2025-10-13 03:15:51'),
(34, 'aan9006', '$2y$10$y0xcPb2snzuvTQb9YM2jGeQU3w4Y32NQFVzCV1DyggUnyv1YHsgp2', 'aan@purba-danarta.com', '2025-10-13 03:15:51'),
(35, 'caca9107', '$2y$10$t12PRJ3FtOqDaQi.yL9VB.HNYDW7crBx4UNTq.9kVZ/KHVw8VhPwC', 'caca@purba-danarta.com', '2025-10-13 03:15:51'),
(36, 'ady9208', '$2y$10$pRuMG0NxEbOAcSDmgz0jneLOB8BfiNq1PyrDFhXo4q7BVzyyIl2fm', 'ady@purba-danarta.com', '2025-10-13 03:15:51'),
(37, 'siti9309', '$2y$10$x3OgPBqoYwBYrTo74VH0teNeL.c8a39ADQpd5Y49SVmraFuANYBwG', 'siti@purba-danarta.com', '2025-10-13 03:15:51');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
(29, 1),
(30, 2),
(31, 3),
(32, 4),
(33, 5),
(34, 2),
(35, 2),
(36, 2),
(37, 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `jenis_cuti`
--
ALTER TABLE `jenis_cuti`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `karyawan`
--
ALTER TABLE `karyawan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nik` (`nik`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pelamar`
--
ALTER TABLE `pelamar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pengajuan_cuti`
--
ALTER TABLE `pengajuan_cuti`
  ADD PRIMARY KEY (`id`),
  ADD KEY `karyawan_nik` (`karyawan_nik`),
  ADD KEY `jenis_cuti_id` (`jenis_cuti_id`);

--
-- Indexes for table `pengajuan_khl`
--
ALTER TABLE `pengajuan_khl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `karyawan_nik` (`karyawan_nik`);

--
-- Indexes for table `proses_seleksi`
--
ALTER TABLE `proses_seleksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pelamar_id` (`pelamar_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_role` (`nama_role`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `jenis_cuti`
--
ALTER TABLE `jenis_cuti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `karyawan`
--
ALTER TABLE `karyawan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `pelamar`
--
ALTER TABLE `pelamar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pengajuan_cuti`
--
ALTER TABLE `pengajuan_cuti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `pengajuan_khl`
--
ALTER TABLE `pengajuan_khl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `proses_seleksi`
--
ALTER TABLE `proses_seleksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `karyawan`
--
ALTER TABLE `karyawan`
  ADD CONSTRAINT `karyawan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `pelamar`
--
ALTER TABLE `pelamar`
  ADD CONSTRAINT `pelamar_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `pengajuan_cuti`
--
ALTER TABLE `pengajuan_cuti`
  ADD CONSTRAINT `pengajuan_cuti_ibfk_1` FOREIGN KEY (`karyawan_nik`) REFERENCES `karyawan` (`nik`),
  ADD CONSTRAINT `pengajuan_cuti_ibfk_2` FOREIGN KEY (`jenis_cuti_id`) REFERENCES `jenis_cuti` (`id`);

--
-- Constraints for table `pengajuan_khl`
--
ALTER TABLE `pengajuan_khl`
  ADD CONSTRAINT `pengajuan_khl_ibfk_1` FOREIGN KEY (`karyawan_nik`) REFERENCES `karyawan` (`nik`);

--
-- Constraints for table `proses_seleksi`
--
ALTER TABLE `proses_seleksi`
  ADD CONSTRAINT `proses_seleksi_ibfk_1` FOREIGN KEY (`pelamar_id`) REFERENCES `pelamar` (`id`);

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
