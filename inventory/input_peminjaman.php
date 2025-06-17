<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Barang - Koran Mandala</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php
    include_once(__DIR__ . '/../config/koneksi.php');

    $barang_id = null;
    $barang_nama = '';
    $stok_tersedia_saat_ini = 0;
    $success_message = '';
    $error_message = '';

    // Ambil ID barang dari URL saat pertama kali halaman dibuka
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $barang_id = mysqli_real_escape_string($koneksi, $_GET['id']);
        $sql_barang = "SELECT nama_barang, stok_tersedia FROM data_barang WHERE id = '$barang_id'";
        $result_barang = mysqli_query($koneksi, $sql_barang);

        if (mysqli_num_rows($result_barang) > 0) {
            $data_barang = mysqli_fetch_assoc($result_barang);
            $barang_nama = $data_barang['nama_barang'];
            $stok_tersedia_saat_ini = $data_barang['stok_tersedia'];
        } else {
            $error_message = "Barang tidak ditemukan.";
            $barang_id = null; // Reset id jika tidak ditemukan
        }
    } else {
        $error_message = "ID Barang tidak valid atau tidak diberikan.";
    }

    // Tangani pengajuan peminjaman saat form disubmit
    if ($_SERVER["REQUEST_METHOD"] == "POST" && $barang_id !== null) {
        $id_barang_form = mysqli_real_escape_string($koneksi, $_POST['id_barang']);
        $qty_pinjam = (int) mysqli_real_escape_string($koneksi, $_POST['qty_pinjam']);
        $tanggal_pinjam = mysqli_real_escape_string($koneksi, $_POST['tanggal_pinjam']);
        $tanggal_pengembalian_target = mysqli_real_escape_string($koneksi, $_POST['tanggal_pengembalian']);
        $peminjam_nama = mysqli_real_escape_string($koneksi, $_POST['peminjam_nama']); // Ambil nama peminjam dari input

        // Ambil stok tersedia terbaru (penting untuk mencegah over-borrow jika ada multi-user)
        $sql_current_stock = "SELECT stok_tersedia FROM data_barang WHERE id = '$id_barang_form'";
        $result_current_stock = mysqli_query($koneksi, $sql_current_stock);
        $current_stock_data = mysqli_fetch_assoc($result_current_stock);
        $current_stok_tersedia = $current_stock_data['stok_tersedia'];

        // Validasi
        if (empty($peminjam_nama)) { // Validasi nama peminjam
            $error_message = "Nama Peminjam harus diisi.";
        } elseif ($qty_pinjam <= 0) {
            $error_message = "Kuantitas pinjam harus lebih dari 0.";
        } elseif ($qty_pinjam > $current_stok_tersedia) {
            $error_message = "Stok yang tersedia tidak mencukupi. Stok saat ini: " . $current_stok_tersedia;
        } elseif (empty($tanggal_pinjam) || empty($tanggal_pengembalian_target)) {
            $error_message = "Tanggal Pinjam dan Tanggal Pengembalian harus diisi.";
        } else {
            // Mulai transaksi
            mysqli_autocommit($koneksi, FALSE);
            $transaction_successful = true;

            // 1. Kurangi stok_tersedia di data_barang
            $sql_update_stok = "UPDATE data_barang SET stok_tersedia = stok_tersedia - $qty_pinjam WHERE id = '$id_barang_form'";
            if (!mysqli_query($koneksi, $sql_update_stok)) {
                $error_message = "Gagal mengurangi stok: " . mysqli_error($koneksi);
                $transaction_successful = false;
            }

            // 2. Catat transaksi di tabel peminjaman
            if ($transaction_successful) {
                $sql_insert_peminjaman = "INSERT INTO peminjaman (id_barang, qty_pinjam, tanggal_pinjam, tanggal_kembali, peminjam_nama, status_peminjaman)
                                        VALUES ('$id_barang_form', '$qty_pinjam', '$tanggal_pinjam', '$tanggal_pengembalian_target', '$peminjam_nama', 'dipinjam')";
                if (!mysqli_query($koneksi, $sql_insert_peminjaman)) {
                    $error_message = "Gagal mencatat peminjaman: " . mysqli_error($koneksi);
                    $transaction_successful = false;
                }
            }

            // Commit atau Rollback transaksi
            if ($transaction_successful) {
                mysqli_commit($koneksi);
                $success_message = "Peminjaman berhasil diajukan!";
                // Clear form fields
                $_POST['peminjam_nama'] = ''; // Clear nama peminjam
                $_POST['qty_pinjam'] = '';
                $_POST['tanggal_pinjam'] = '';
                $_POST['tanggal_pengembalian'] = '';
                // Update the displayed current stock
                $stok_tersedia_saat_ini -= $qty_pinjam;
            } else {
                mysqli_rollback($koneksi);
                // Error message already set
            }
            mysqli_autocommit($koneksi, TRUE); // Kembalikan auto-commit
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
                    <li><a href="#"><i class="fas fa-user-check"></i> Absen</a></li>
                    <li><a href="#"><i class="fas fa-newspaper"></i> Redaksi</a></li>
                    <li class="active"><a href="list_barang.php"><i class="fas fa-boxes"></i> Inventory Asset</a></li>
                    <li><a href="#"><i class="fas fa-briefcase"></i> Divisi Bisnis</a></li>
                </ul>
            </nav>
        </div>
        <div class="main-content">
            <header class="header">
                <h1>Peminjaman Barang</h1>
                <div class="header-right">
                    <div class="user-profile">
                        <img src="../assets/img/profile-placeholder.jpg" alt="User Profile">
                    </div>
                </div>
            </header>

            <div class="content-area">
                <div class="form-card">
                    <?php if ($barang_id !== null): ?>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . htmlspecialchars($barang_id); ?>" method="POST">
                        <input type="hidden" name="id_barang" value="<?php echo htmlspecialchars($barang_id); ?>">
                        <div class="form-group">
                            <label>Nama Barang</label>
                            <input type="text" value="<?php echo htmlspecialchars($barang_nama); ?>" readonly>
                        </div>
                         <div class="form-group">
                            <label>Stok Tersedia</label>
                            <input type="text" value="<?php echo htmlspecialchars($stok_tersedia_saat_ini); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="peminjam_nama">Nama Peminjam</label>
                            <input type="text" id="peminjam_nama" name="peminjam_nama" placeholder="Masukan Nama Peminjam" required value="<?php echo isset($_POST['peminjam_nama']) ? htmlspecialchars($_POST['peminjam_nama']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="qty_pinjam">QTY</label>
                            <input type="number" id="qty_pinjam" name="qty_pinjam" placeholder="Masukan Qty" min="1" max="<?php echo $stok_tersedia_saat_ini; ?>" required value="<?php echo isset($_POST['qty_pinjam']) ? htmlspecialchars($_POST['qty_pinjam']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="tanggal_pinjam">Tanggal Pinjam</label>
                            <input type="date" id="tanggal_pinjam" name="tanggal_pinjam" placeholder="dd/mm/yyyy" required value="<?php echo isset($_POST['tanggal_pinjam']) ? htmlspecialchars($_POST['tanggal_pinjam']) : date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="tanggal_pengembalian">Tanggal Pengembalian</label>
                            <input type="date" id="tanggal_pengembalian" name="tanggal_pengembalian" placeholder="dd/mm/yyyy" required value="<?php echo isset($_POST['tanggal_pengembalian']) ? htmlspecialchars($_POST['tanggal_pengembalian']) : ''; ?>">
                        </div>
                        <div class="form-actions">
                            <a href="peminjaman_barang.php" class="btn-red">Kembali</a>
                            <button type="submit" class="btn-red">Ajukan</button>
                        </div>
                    </form>
                    <?php else: ?>
                        <p><?php echo htmlspecialchars($error_message); ?></p>
                        <a href="peminjaman_barang.php" class="btn-red">Kembali ke Daftar Peminjaman</a>
                    <?php endif; ?>
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
                    window.location.href = 'peminjaman_barang.php?status=added';
                }, 2000); // 2000 milidetik = 2 detik
            });
        <?php endif; ?>

        // Fungsi ini hanya untuk menutup modal error
        function closeModal() {
            document.getElementById('statusModal').style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const tanggalPinjamInput = document.getElementById('tanggal_pinjam');
            const tanggalPengembalianInput = document.getElementById('tanggal_pengembalian');

            if (tanggalPinjamInput) {
                const today = new Date();
                const year = today.getFullYear();
                let month = today.getMonth() + 1;
                let day = today.getDate();

                if (month < 10) month = '0' + month;
                if (day < 10) day = '0' + day;

                const todayString = `${year}-${month}-${day}`;
                tanggalPinjamInput.setAttribute('min', todayString);
                if (!tanggalPinjamInput.value) {
                    tanggalPinjamInput.value = todayString;
                }
            }

            if (tanggalPinjamInput && tanggalPengembalianInput) {
                tanggalPinjamInput.addEventListener('change', function() {
                    tanggalPengembalianInput.setAttribute('min', this.value);
                    if (tanggalPengembalianInput.value && tanggalPengembalianInput.value < this.value) {
                        tanggalPengembalianInput.value = '';
                    }
                });
                if (tanggalPinjamInput.value) {
                    tanggalPengembalianInput.setAttribute('min', tanggalPinjamInput.value);
                }
            }
        });
    </script>
</body>
</html>
<?php mysqli_close($koneksi); ?>