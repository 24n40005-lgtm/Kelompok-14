<?php
// File: includes/auth.php

function checkAuth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit();
    }
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

function hasRole($role_name) {
    $user_role = getUserRole();
    return $user_role === $role_name;
}
?>