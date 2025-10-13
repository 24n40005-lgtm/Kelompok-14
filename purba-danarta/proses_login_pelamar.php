<?php
// File: proses_login_pelamar.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['login_error_pelamar'] = "Akses tidak valid!";
    header('Location: login.php');
    exit();
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $_SESSION['login_error_pelamar'] = "Username dan password harus diisi!";
    header('Location: login.php');
    exit();
}

// Cari user pelamar
$sql = "SELECT u.id, u.username, u.password, u.email, p.nama_lengkap, p.id as pelamar_id
        FROM users u 
        JOIN pelamar p ON u.id = p.user_id 
        WHERE u.username = ?";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user_data = $result->fetch_assoc();
    
    // Verifikasi password
    if (password_verify($password, $user_data['password'])) {
        // Set session data
        $_SESSION['user_id'] = $user_data['id'];
        $_SESSION['username'] = $user_data['username'];
        $_SESSION['nama_lengkap'] = $user_data['nama_lengkap'];
        $_SESSION['role'] = 'Pelamar';
        $_SESSION['pelamar_id'] = $user_data['pelamar_id'];
        
        header('Location: role_pelamar/dashboard.php');
        exit();
    }
}

$_SESSION['login_error_pelamar'] = "Username atau password salah!";
header('Location: login.php');
exit();
?>