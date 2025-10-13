<?php
// File: includes/koneksi.php (UPGRADED VERSION)

$host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'db_purba_danarta';

// Koneksi ke database
$koneksi = new mysqli($host, $db_user, $db_pass, $db_name);

if ($koneksi->connect_error) {
    die("Koneksi database gagal: " . $koneksi->connect_error);
}

// Auto-setup database tables jika belum ada
include 'init_database.php';
?>