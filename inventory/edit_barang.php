<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Barang - Koran Mandala</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php
    include_once(__DIR__ . '/../config/koneksi.php');

    $barang_data = null;
    $success_message = '';
    $error_message = '';

    // Fetch data if ID is provided in URL
    if (isset($_GET['id']) && !empty($_GET['id']) && $_SERVER["REQUEST_METHOD"] != "POST") {
        $id_to_edit = mysqli_real_escape_string($koneksi, $_GET['id']);
        $sql_fetch = "SELECT * FROM data_barang WHERE id = '$id_to_edit'";
        $result_fetch = mysqli_query($koneksi, $sql_fetch);

        if (mysqli_num_rows($result_fetch) > 0) {
            $barang_data = mysqli_fetch_assoc($result_fetch);
        } else {
            $error_message = "Data barang tidak ditemukan.";
        }
    } elseif (!isset($_GET['id']) && !isset($_POST['id'])) {
        // If no ID on GET and not a POST submission with ID, redirect back
        header("Location: list_barang.php");
        exit();
    }

    // Handle form submission for update
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id = mysqli_real_escape_string($koneksi, $_POST['id']);
        $nama_barang = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
        $kode_barang = mysqli_real_escape_string($koneksi, $_POST['kode_barang']);
        $tanggal_beli = mysqli_real_escape_string($koneksi, $_POST['tanggal_beli']);
        $pembeli = mysqli_real_escape_string($koneksi, $_POST['pembeli']);
        $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
        $stok_keseluruhan = mysqli_real_escape_string($koneksi, $_POST['stok_keseluruhan']);

        // Check for duplicate kode_barang, excluding current item's ID
        $check_sql = "SELECT id FROM data_barang WHERE kode_barang = '$kode_barang' AND id != '$id'";
        $check_result = mysqli_query($koneksi, $check_sql);

        if (mysqli_num_rows($check_result) > 0) {
            $error_message = "Kode Barang sudah ada untuk barang lain. Mohon gunakan kode lain.";
            // Re-populate form with submitted data if there's an error
            $barang_data = [
                'id' => $id,
                'nama_barang' => $nama_barang,
                'kode_barang' => $kode_barang,
                'tanggal_beli' => $tanggal_beli,
                'pembeli' => $pembeli,
                'keterangan' => $keterangan,
                'stok_keseluruhan' => $stok_keseluruhan
            ];
        } else {
            $sql = "UPDATE data_barang SET
                    nama_barang = '$nama_barang',
                    kode_barang = '$kode_barang',
                    tanggal_beli = '$tanggal_beli',
                    pembeli = '$pembeli',
                    keterangan = '$keterangan',
                    stok_keseluruhan = '$stok_keseluruhan'
                    WHERE id = '$id'";

            if (mysqli_query($koneksi, $sql)) {
                $success_message = "DATA BERHASIL DISIMPAN";
                // Update barang_data with new values after successful save for display
                $barang_data = [
                    'id' => $id,
                    'nama_barang' => $nama_barang,
                    'kode_barang' => $kode_barang,
                    'tanggal_beli' => $tanggal_beli,
                    'pembeli' => $pembeli,
                    'keterangan' => $keterangan,
                    'stok_keseluruhan' => $stok_keseluruhan
                ];
            } else {
                $error_message = "Error updating record: " . mysqli_error($koneksi);
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
                <h1>Update Barang</h1>
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
                    <?php if ($barang_data): ?>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . htmlspecialchars($barang_data['id']); ?>" method="POST">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($barang_data['id']); ?>">
                        <div class="form-group">
                            <label for="nama_barang">Nama Barang</label>
                            <input type="text" id="nama_barang" name="nama_barang" placeholder="Masukan Nama Barang" required value="<?php echo htmlspecialchars($barang_data['nama_barang']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="kode_barang">Kode Barang</label>
                            <input type="text" id="kode_barang" name="kode_barang" placeholder="Masukan Kode Barang" required value="<?php echo htmlspecialchars($barang_data['kode_barang']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="tanggal_beli">Tanggal Beli</label>
                            <input type="date" id="tanggal_beli" name="tanggal_beli" placeholder="dd/mm/yyyy" value="<?php echo htmlspecialchars($barang_data['tanggal_beli']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="pembeli">Pembeli</label>
                            <input type="text" id="pembeli" name="pembeli" placeholder="Masukan Pembeli" value="<?php echo htmlspecialchars($barang_data['pembeli']); ?>">
                        </div>
                        <div class="form-group">
                            <label>Keterangan</label>
                            <div class="radio-group">
                                <label><input type="radio" name="keterangan" value="Bisa Dipinjam" <?php echo ($barang_data['keterangan'] == 'Bisa Dipinjam') ? 'checked' : ''; ?> required> Bisa Dipinjam</label>
                                <label><input type="radio" name="keterangan" value="Tidak Bisa Dipinjam" <?php echo ($barang_data['keterangan'] == 'Tidak Bisa Dipinjam') ? 'checked' : ''; ?>> Tidak Bisa Dipinjam</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="stok_keseluruhan">Stok Keseluruhan</label>
                            <input type="number" id="stok_keseluruhan" name="stok_keseluruhan" placeholder="Masukan Stok" min="0" required value="<?php echo htmlspecialchars($barang_data['stok_keseluruhan']); ?>">
                        </div>
                        <div class="form-actions">
                            <a href="list_barang.php" class="btn-red">Kembali</a>
                            <button type="submit" class="btn-red">Update</button>
                        </div>
                    </form>
                    <?php else: ?>
                        <p><?php echo $error_message; ?></p>
                        <a href="list_barang.php" class="btn-red">Kembali ke Daftar Barang</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div id="statusModal" class="modal-overlay" style="<?php echo ($success_message || $error_message) ? 'display: flex;' : 'display: none;'; ?>">
        <div class="modal-content">
            <?php if ($success_message): ?>
                <h2>DATA BERHASIL DISIMPAN</h2>
                <i class="fas fa-check-circle icon-success"></i>
            <?php elseif ($error_message): ?>
                <h2 style="color: #dc3545;">GAGAL DISIMPAN</h2>
                <i class="fas fa-times-circle" style="color: #dc3545; font-size: 4em; margin-bottom: 20px;"></i>
                <p><?php echo $error_message; ?></p>
            <?php endif; ?>
            <button class="btn-red" onclick="closeModal()">OK</button>
        </div>
    </div>

    <script>
        function closeModal() {
            document.getElementById('statusModal').style.display = 'none';
            <?php if ($success_message): ?>
                window.location.href = 'list_barang.php?status=updated';
            <?php endif; ?>
        }
    </script>
</body>
</html>
<?php mysqli_close($koneksi); ?>