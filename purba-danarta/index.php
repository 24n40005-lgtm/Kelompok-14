<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika belum login, arahkan ke halaman login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Jika sudah login, arahkan ke dashboard sesuai role
$role = $_SESSION['role'];

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
        // Fallback jika role tidak dikenali
        header('Location: login.php');
        break;
}
exit();
?>