<?php
// File: proses_login.php (FIXED VERSION)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header('Location: login.php');
    exit();
}

$login_type = $_POST['login_type'] ?? '';

// LOGIN INTERNAL (Karyawan, PJ, Admin, Direksi)
if ($login_type === 'internal') {
    $login_input = trim($_POST['username'] ?? '');
    $password_input = $_POST['password'] ?? '';
    $role_dipilih = $_POST['role'] ?? '';

    // Debug log
    error_log("Login attempt: $login_input, Role: $role_dipilih");

    if (empty($login_input) || empty($password_input) || empty($role_dipilih)) {
        $_SESSION['login_error'] = "Semua field wajib diisi!";
        header('Location: login.php');
        exit();
    }

    // Mapping role name to role ID untuk query
    $role_mapping = [
        'Karyawan' => 'Karyawan',
        'Penanggung Jawab' => 'Penanggung Jawab', 
        'Administrator' => 'Administrator',
        'Direksi' => 'Direksi'
    ];

    $role_query = $role_mapping[$role_dipilih] ?? '';

    // Cari user dengan role yang sesuai
    $sql = "SELECT u.id as user_id, u.username, u.password, 
                   k.nik, k.nama_lengkap, r.nama_role as role_name
            FROM users u
            JOIN user_roles ur ON u.id = ur.user_id
            JOIN roles r ON ur.role_id = r.id
            LEFT JOIN karyawan k ON u.id = k.user_id
            WHERE (u.username = ? OR k.nik = ?) 
            AND r.nama_role = ?";
            
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("sss", $login_input, $login_input, $role_query);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user_data = $result->fetch_assoc();
        
        // Verifikasi password
        if (password_verify($password_input, $user_data['password'])) {
            // LOGIN BERHASIL
            $_SESSION['user_id'] = $user_data['user_id'];
            $_SESSION['nik'] = $user_data['nik'];
            $_SESSION['nama_lengkap'] = $user_data['nama_lengkap'];
            $_SESSION['username'] = $user_data['username'];
            $_SESSION['role'] = $user_data['role_name'];

            error_log("Login SUCCESS: {$user_data['username']} as {$user_data['role_name']}");
            
            header('Location: dashboard.php');
            exit();
        } else {
            error_log("Login FAILED: Password mismatch for $login_input");
        }
    } else {
        error_log("Login FAILED: User not found - $login_input as $role_query");
    }

    $_SESSION['login_error'] = "NIK/Username, password, atau role tidak sesuai.";
    header('Location: login.php');
    exit();
}

header('Location: login.php');
exit();
?>