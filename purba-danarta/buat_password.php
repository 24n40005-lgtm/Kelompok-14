<?php
// File: buat_password.php

$password_asli = 'password123';
$hash_password = password_hash($password_asli, PASSWORD_DEFAULT);

echo 'Password asli: ' . $password_asli . '<br>';
echo 'Hash yang harus Anda simpan di database:<br>';
echo '<textarea rows="3" cols="70" readonly>' . $hash_password . '</textarea>';
?>