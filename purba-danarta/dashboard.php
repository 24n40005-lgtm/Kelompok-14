<?php
// File: dashboard.php (ROUTER)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect ke login jika belum login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Redirect ke dashboard sesuai role
$role = $_SESSION['role'] ?? '';

switch ($role) {
    case 'Pelamar':
        header('Location: role_pelamar/dashboard.php');
        break;
    case 'Karyawan':
        header('Location: role_karyawan/dashboard.php');
        break;
    case 'Penanggung Jawab':
        header('Location: role_pj/dashboard.php');
        break;
    case 'Administrator':
        header('Location: role_administrator/dashboard.php');
        break;
    case 'Direksi':
        header('Location: role_direksi/dashboard.php');
        break;
    default:
        // Jika role tidak dikenal, logout
        session_destroy();
        header('Location: login.php');
        break;
}
exit();
?>