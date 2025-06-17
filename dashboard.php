<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
$user = $_SESSION['user'];
?>

<h1>Selamat datang, <?= $user['nama']; ?> (<?= $user['role']; ?>)</h1>

<?php if ($user['role'] == 'admin') : ?>
    <ul>
        <li><a href="#">Scan QR Absensi</a></li>
        <li><a href="#">Laporan Redaksi</a></li>
        <li><a href="#">Inventaris</a></li>
        <li><a href="#">Peminjaman</a></li>
        <li><a href="#">Promosi Bisnis</a></li>
    </ul>
<?php elseif ($user['role'] == 'wartawan') : ?>
    <ul>
        <li><a href="#">Input Laporan Redaksi</a></li>
        <li><a href="#">Lihat Inventaris</a></li>
    </ul>
<?php elseif ($user['role'] == 'pegawai') : ?>
    <ul>
        <li><a href="#">Scan QR Absensi</a></li>
        <li><a href="#">Lihat Inventaris</a></li>
        <li><a href="#">Laporan Promosi Bisnis</a></li>
    </ul>
<?php endif; ?>

<a href="logout.php">Logout</a>
