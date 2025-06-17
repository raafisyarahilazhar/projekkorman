<?php
$host = "localhost";
$user = "root"; // sesuaikan jika pakai XAMPP/Laragon
$pass = "";     // sesuaikan jika ada password
$db   = "koran_mandala";

// Buat koneksi
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>
