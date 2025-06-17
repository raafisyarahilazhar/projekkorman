<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Barang - Koran Mandala</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php
    include_once(__DIR__ . '/../config/koneksi.php');

    $success_message = '';
    $error_message = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nama_barang = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
        $kode_barang = mysqli_real_escape_string($koneksi, $_POST['kode_barang']);
        $tanggal_beli = mysqli_real_escape_string($koneksi, $_POST['tanggal_beli']);
        $pembeli = mysqli_real_escape_string($koneksi, $_POST['pembeli']);
        $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
        $stok_keseluruhan = mysqli_real_escape_string($koneksi, $_POST['stok_keseluruhan']);

        $check_sql = "SELECT id FROM data_barang WHERE kode_barang = '$kode_barang'";
        $check_result = mysqli_query($koneksi, $check_sql);

        if (mysqli_num_rows($check_result) > 0) {
            $error_message = "Kode Barang sudah ada. Mohon gunakan kode lain.";
        } else {
            $sql = "INSERT INTO data_barang (nama_barang, kode_barang, tanggal_beli, pembeli, keterangan, stok_keseluruhan)
                    VALUES ('$nama_barang', '$kode_barang', '$tanggal_beli', '$pembeli', '$keterangan', '$stok_keseluruhan')";

            if (mysqli_query($koneksi, $sql)) {
                $success_message = "DATA BERHASIL DISIMPAN";
                // Optionally clear form fields or redirect after success
                // $_POST = array(); // Uncomment to clear form
            } else {
                $error_message = "Error: " . $sql . "<br>" . mysqli_error($koneksi);
            }
        }
    }
    ?>
    <div class="container">
        <div class="sidebar">
            <div class="logo">
                <img src="../assets/image 2.png" width="110" alt="">
            </div>
            <nav class="nav-menu">
                <ul>
                    <li><a href="#"><i class="fas fa-home"></i> Beranda</a></li>
                    <li><a href="#"><i class="fas fa-newspaper"></i> Redaksi</a></li>
                    <li><a href="#"><i class="fas fa-user-check"></i> Absensi</a></li>
                    <li class="active"><a href="list_barang.php"><i class="fas fa-boxes"></i> Aset Inventaris</a></li>
                    <li><a href="#"><i class="fas fa-briefcase"></i> Bisnis</a></li>
                    <li><a href="#"><i class="fas fa-users"></i> Kelola User</a></li>
                </ul>
            </nav>
        </div>
        <div class="main-content">
            <header class="header">
                <h1>Tambah Barang</h1>
                <div class="header-right">
                    <div class="search-bar">
                        <input type="text" placeholder="Search">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </div>
                    <div class="user-profile">
                        <img src="../assets/img/profile-placeholder.jpg" alt="User Profile">
                    </div>
                </div>
            </header>

            <div class="content-area">
                <div class="form-card">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                        <div class="form-group">
                            <label for="nama_barang">Nama Barang</label>
                            <input type="text" id="nama_barang" name="nama_barang" placeholder="Masukan Nama Barang" required value="<?php echo isset($_POST['nama_barang']) ? htmlspecialchars($_POST['nama_barang']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="kode_barang">Kode Barang</label>
                            <input type="text" id="kode_barang" name="kode_barang" placeholder="Masukan Kode Barang" required value="<?php echo isset($_POST['kode_barang']) ? htmlspecialchars($_POST['kode_barang']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="tanggal_beli">Tanggal Beli</label>
                            <input type="date" id="tanggal_beli" name="tanggal_beli" placeholder="dd/mm/yyyy" value="<?php echo isset($_POST['tanggal_beli']) ? htmlspecialchars($_POST['tanggal_beli']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="pembeli">Pembeli</label>
                            <input type="text" id="pembeli" name="pembeli" placeholder="Masukan Pembeli" value="<?php echo isset($_POST['pembeli']) ? htmlspecialchars($_POST['pembeli']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Keterangan</label>
                            <div class="radio-group">
                                <label><input type="radio" name="keterangan" value="Bisa Dipinjam" <?php echo (isset($_POST['keterangan']) && $_POST['keterangan'] == 'Bisa Dipinjam') ? 'checked' : ''; ?> required> Bisa Dipinjam</label>
                                <label><input type="radio" name="keterangan" value="Tidak Bisa Dipinjam" <?php echo (isset($_POST['keterangan']) && $_POST['keterangan'] == 'Tidak Bisa Dipinjam') ? 'checked' : ''; ?>> Tidak Bisa Dipinjam</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="stok_keseluruhan">Stok Keseluruhan</label>
                            <input type="number" id="stok_keseluruhan" name="stok_keseluruhan" placeholder="Masukan Stok" min="0" required value="<?php echo isset($_POST['stok_keseluruhan']) ? htmlspecialchars($_POST['stok_keseluruhan']) : ''; ?>">
                        </div>
                        <div class="form-actions">
                            <a href="list_barang.php" class="btn-red">Kembali</a>
                            <button type="submit" class="btn-red">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="statusModal" class="modal-overlay-new" style="<?php echo ($success_message || $error_message) ? 'display: flex;' : 'display: none;'; ?>">
        <div class="modal-content-new">
            <?php if ($success_message): ?>
                <h2><?php echo $success_message; ?></h2>
                <div class="icon-container-new success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
            <?php elseif ($error_message): ?>
                <h2 style="font-size: 1.5em; color: #dc3545;">GAGAL DISIMPAN</h2>
                <p style="color: #555;"><?php echo htmlspecialchars($error_message); ?></p>
                <button class="btn-red" style="margin-top: 20px;" onclick="closeModal()">OK</button>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        <?php if ($success_message): ?>
            document.addEventListener('DOMContentLoaded', function() {
                // Tampilkan modal
                const modal = document.getElementById('statusModal');
                modal.style.display = 'flex';

                // Tunggu 2 detik, lalu redirect
                setTimeout(function() {
                    modal.style.display = 'none';
                    window.location.href = 'list_barang.php?status=added';
                }, 2000); // 2000 milidetik = 2 detik
            });
        <?php endif; ?>

        // Fungsi ini hanya untuk menutup modal error
        function closeModal() {
            document.getElementById('statusModal').style.display = 'none';
        }
    </script>
</body>
</html>
<?php mysqli_close($koneksi); ?>