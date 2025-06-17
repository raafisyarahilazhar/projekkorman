<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Peminjaman Barang - Koran Mandala</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php
    include_once(__DIR__ . '/../config/koneksi.php');

    $peminjaman_data = null;
    $barang_id_terkait = null; // Untuk kembali ke detail barang yang benar
    $success_message = '';
    $error_message = '';

    // Ambil ID peminjaman dari URL
    if (isset($_GET['id']) && !empty($_GET['id']) && $_SERVER["REQUEST_METHOD"] != "POST") {
        $peminjaman_id = mysqli_real_escape_string($koneksi, $_GET['id']);
        
        // Query untuk mendapatkan data peminjaman beserta nama barang
        $sql_fetch = "SELECT p.*, b.nama_barang, b.kode_barang, b.stok_tersedia AS stok_tersedia_barang
                      FROM peminjaman p
                      JOIN data_barang b ON p.id_barang = b.id
                      WHERE p.id = '$peminjaman_id'";
        $result_fetch = mysqli_query($koneksi, $sql_fetch);

        if (mysqli_num_rows($result_fetch) > 0) {
            $peminjaman_data = mysqli_fetch_assoc($result_fetch);
            $barang_id_terkait = $peminjaman_data['id_barang']; // Simpan ID barang terkait
        } else {
            $error_message = "Data peminjaman tidak ditemukan.";
        }
    } elseif (!isset($_GET['id']) && !isset($_POST['peminjaman_id'])) {
        // Jika tidak ada ID di GET dan bukan POST, arahkan kembali
        header("Location: peminjaman_barang.php"); // Atau halaman daftar peminjaman
        exit();
    }

    // Tangani form submission untuk update
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $peminjaman_id = mysqli_real_escape_string($koneksi, $_POST['peminjaman_id']);
        $id_barang_form = mysqli_real_escape_string($koneksi, $_POST['id_barang']); // ID barang dari hidden field
        $qty_pinjam_baru = (int) mysqli_real_escape_string($koneksi, $_POST['qty_pinjam']);
        $tanggal_pinjam_baru = mysqli_real_escape_string($koneksi, $_POST['tanggal_pinjam']);
        $tanggal_kembali_target_baru = mysqli_real_escape_string($koneksi, $_POST['tanggal_kembali']);
        $peminjam_nama_baru = mysqli_real_escape_string($koneksi, $_POST['peminjam_nama']); // Ambil dari input baru
        $status_peminjaman_baru = mysqli_real_escape_string($koneksi, $_POST['status_peminjaman']);

        // Ambil data peminjaman lama dan stok barang saat ini
        $sql_old_data = "SELECT p.qty_pinjam, b.stok_tersedia
                         FROM peminjaman p
                         JOIN data_barang b ON p.id_barang = b.id
                         WHERE p.id = '$peminjaman_id'";
        $result_old_data = mysqli_query($koneksi, $sql_old_data);
        $old_data = mysqli_fetch_assoc($result_old_data);
        $qty_pinjam_lama = $old_data['qty_pinjam'];
        $stok_tersedia_saat_ini = $old_data['stok_tersedia'];

        // Hitung perbedaan QTY dan sesuaikan stok
        $qty_diff = $qty_pinjam_baru - $qty_pinjam_lama;

        // Validasi
        if ($qty_pinjam_baru <= 0) {
            $error_message = "Kuantitas pinjam harus lebih dari 0.";
        } elseif (($stok_tersedia_saat_ini - $qty_diff) < 0) { // Cek apakah stok cukup setelah perubahan
            $error_message = "Stok yang tersedia tidak mencukupi untuk perubahan ini. Stok saat ini: " . $stok_tersedia_saat_ini;
        } elseif (empty($tanggal_pinjam_baru) || empty($tanggal_kembali_target_baru)) {
            $error_message = "Tanggal Pinjam dan Tanggal Pengembalian harus diisi.";
        } else {
            // Mulai transaksi
            mysqli_autocommit($koneksi, FALSE);
            $transaction_successful = true;

            // 1. Perbarui stok_tersedia di data_barang jika qty_pinjam berubah
            if ($qty_diff != 0) {
                $sql_update_stok = "UPDATE data_barang SET stok_tersedia = stok_tersedia - $qty_diff WHERE id = '$id_barang_form'";
                if (!mysqli_query($koneksi, $sql_update_stok)) {
                    $error_message = "Gagal memperbarui stok barang: " . mysqli_error($koneksi);
                    $transaction_successful = false;
                }
            }
            
            // 2. Perbarui catatan di tabel peminjaman
            if ($transaction_successful) {
                $sql_update_peminjaman = "UPDATE peminjaman SET
                                        qty_pinjam = '$qty_pinjam_baru',
                                        tanggal_pinjam = '$tanggal_pinjam_baru',
                                        tanggal_kembali = '$tanggal_kembali_target_baru',
                                        peminjam_nama = '$peminjam_nama_baru',
                                        status_peminjaman = '$status_peminjaman_baru'
                                        WHERE id = '$peminjaman_id'";
                if (!mysqli_query($koneksi, $sql_update_peminjaman)) {
                    $error_message = "Gagal memperbarui peminjaman: " . mysqli_error($koneksi);
                    $transaction_successful = false;
                }
            }

            // Commit atau Rollback transaksi
            if ($transaction_successful) {
                mysqli_commit($koneksi);
                $success_message = "Data peminjaman berhasil diperbarui!";
                // Update $peminjaman_data for display
                $peminjaman_data = [
                    'id' => $peminjaman_id,
                    'id_barang' => $id_barang_form,
                    'nama_barang' => $_POST['nama_barang_display'], // Ambil dari hidden field
                    'kode_barang' => $_POST['kode_barang_display'], // Ambil dari hidden field
                    'qty_pinjam' => $qty_pinjam_baru,
                    'tanggal_pinjam' => $tanggal_pinjam_baru,
                    'tanggal_kembali' => $tanggal_kembali_target_baru,
                    'peminjam_nama' => $peminjam_nama_baru,
                    'status_peminjaman' => $status_peminjaman_baru,
                    'stok_tersedia_barang' => $stok_tersedia_saat_ini - $qty_diff // Update stok di tampilan
                ];
            } else {
                mysqli_rollback($koneksi);
                // Error message already set
                // Re-populate form with submitted data if there's an error
                $peminjaman_data = [
                    'id' => $peminjaman_id,
                    'id_barang' => $id_barang_form,
                    'nama_barang' => $_POST['nama_barang_display'],
                    'kode_barang' => $_POST['kode_barang_display'],
                    'qty_pinjam' => $qty_pinjam_baru,
                    'tanggal_pinjam' => $tanggal_pinjam_baru,
                    'tanggal_kembali' => $tanggal_kembali_target_baru,
                    'peminjam_nama' => $peminjam_nama_baru,
                    'status_peminjaman' => $status_peminjaman_baru,
                    'stok_tersedia_barang' => $stok_tersedia_saat_ini // Kembali ke stok awal jika error
                ];
            }
            mysqli_autocommit($koneksi, TRUE); // Kembalikan auto-commit
        }
    }
    // Jika ada error_message dari awal load page (misal ID tidak valid), tampilkan itu
    // Ini memastikan $peminjaman_data tidak null saat form ditampilkan
    if ($peminjaman_data === null && !empty($error_message)) {
        // Do nothing, the error message will be displayed below
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
                <h1>Update Peminjaman Barang</h1>
                <div class="header-right">
                    <div class="user-profile">
                        <img src="../assets/img/profile-placeholder.jpg" alt="User Profile">
                    </div>
                </div>
            </header>

            <div class="content-area">
                <div class="form-card">
                    <?php if ($peminjaman_data): ?>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . htmlspecialchars($peminjaman_data['id']); ?>" method="POST">
                        <input type="hidden" name="peminjaman_id" value="<?php echo htmlspecialchars($peminjaman_data['id']); ?>">
                        <input type="hidden" name="id_barang" value="<?php echo htmlspecialchars($peminjaman_data['id_barang']); ?>">
                        <input type="hidden" name="nama_barang_display" value="<?php echo htmlspecialchars($peminjaman_data['nama_barang']); ?>">
                        <input type="hidden" name="kode_barang_display" value="<?php echo htmlspecialchars($peminjaman_data['kode_barang']); ?>">

                        <div class="form-group">
                            <label>Nama Barang</label>
                            <input type="text" value="<?php echo htmlspecialchars($peminjaman_data['nama_barang']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>Kode Barang</label>
                            <input type="text" value="<?php echo htmlspecialchars($peminjaman_data['kode_barang']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="peminjam_nama">Nama Peminjam</label>
                            <input type="text" id="peminjam_nama" name="peminjam_nama" placeholder="Masukan Nama Peminjam" required value="<?php echo htmlspecialchars($peminjaman_data['peminjam_nama']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="qty_pinjam">Stok Yang Dipinjam</label>
                            <input type="number" id="qty_pinjam" name="qty_pinjam" placeholder="Masukan Qty" min="1" required value="<?php echo htmlspecialchars($peminjaman_data['qty_pinjam']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="tanggal_pinjam">Tanggal Pinjam</label>
                            <input type="date" id="tanggal_pinjam" name="tanggal_pinjam" placeholder="dd/mm/yyyy" required value="<?php echo htmlspecialchars($peminjaman_data['tanggal_pinjam']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="tanggal_kembali">Tanggal Pengembalian</label>
                            <input type="date" id="tanggal_kembali" name="tanggal_kembali" placeholder="dd/mm/yyyy" required value="<?php echo htmlspecialchars($peminjaman_data['tanggal_kembali']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="status_peminjaman">Status</label>
                            <select id="status_peminjaman" name="status_peminjaman" class="select-field">
                                <option value="dipinjam" <?php echo ($peminjaman_data['status_peminjaman'] == 'dipinjam') ? 'selected' : ''; ?>>Dipinjam</option>
                                <option value="dikembalikan" <?php echo ($peminjaman_data['status_peminjaman'] == 'dikembalikan') ? 'selected' : ''; ?>>Dikembalikan</option>
                                <option value="terlambat" <?php echo ($peminjaman_data['status_peminjaman'] == 'terlambat') ? 'selected' : ''; ?>>Terlambat</option>
                            </select>
                        </div>
                        <div class="form-actions">
                            <a href="detail_peminjaman.php?id=<?php echo htmlspecialchars($barang_id_terkait); ?>" class="btn-red">Kembali</a>
                            <button type="submit" class="btn-red">Update</button>
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

    <div id="statusModal" class="modal-overlay" style="<?php echo ($success_message || $error_message) ? 'display: flex;' : 'display: none;'; ?>">
        <div class="modal-content">
            <?php if ($success_message): ?>
                <h2>Update Berhasil</h2>
                <i class="fas fa-check-circle icon-success"></i>
                <p><?php echo htmlspecialchars($success_message); ?></p>
            <?php elseif ($error_message): ?>
                <h2 style="color: #dc3545;">Update Gagal</h2>
                <i class="fas fa-times-circle" style="color: #dc3545; font-size: 4em; margin-bottom: 20px;"></i>
                <p><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>
            <button class="btn-red" onclick="closeModal()">OK</button>
        </div>
    </div>

    <script>
        function closeModal() {
            document.getElementById('statusModal').style.display = 'none';
            <?php if ($success_message && $barang_id_terkait): ?>
                // Redirect back to the detail page of the specific item after success
                window.location.href = 'detail_peminjaman.php?id=<?php echo htmlspecialchars($barang_id_terkait); ?>';
            <?php elseif ($success_message): ?>
                 // Fallback if $barang_id_terkait is somehow lost
                window.location.href = 'peminjaman_barang.php';
            <?php endif; ?>
        }

        document.addEventListener('DOMContentLoaded', function() {
            const tanggalPinjamInput = document.getElementById('tanggal_pinjam');
            const tanggalKembaliInput = document.getElementById('tanggal_kembali');

            if (tanggalPinjamInput && tanggalKembaliInput) {
                // Set min date for Tanggal Kembali based on Tanggal Pinjam
                tanggalPinjamInput.addEventListener('change', function() {
                    tanggalKembaliInput.setAttribute('min', this.value);
                    if (tanggalKembaliInput.value && tanggalKembaliInput.value < this.value) {
                        tanggalKembaliInput.value = ''; // Clear if invalid
                    }
                });

                // Initial set on load
                if (tanggalPinjamInput.value) {
                    tanggalKembaliInput.setAttribute('min', tanggalPinjamInput.value);
                }
            }
        });
    </script>
</body>
</html>
<?php mysqli_close($koneksi); ?>