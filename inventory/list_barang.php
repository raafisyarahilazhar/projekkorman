<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Barang - Koran Mandala</title>
    <link rel="stylesheet" href="../assets/style.css"> <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php
    include_once(__DIR__ . '/../config/koneksi.php'); // Pastikan ini mengarah ke file koneksi Anda

    // --- Handle Delete Operation ---
    if (isset($_GET['delete_id'])) {
        $id_to_delete = mysqli_real_escape_string($koneksi, $_GET['delete_id']);
        $error_message_delete = ""; // Variabel lokal untuk pesan error delete

        // Check if there are any active borrowings for this item
        $check_borrow_sql = "SELECT COUNT(*) AS active_borrows FROM peminjaman WHERE id_barang = '$id_to_delete' AND status_peminjaman = 'dipinjam'";
        $check_borrow_result = mysqli_query($koneksi, $check_borrow_sql);
        $active_borrows = mysqli_fetch_assoc($check_borrow_result)['active_borrows'];

        if ($active_borrows > 0) {
            // Jika masih ada peminjaman aktif, tampilkan error dan jangan lanjutkan penghapusan
            echo "<script>alert('Gagal menghapus barang: Masih ada " . $active_borrows . " peminjaman aktif untuk barang ini. Mohon selesaikan peminjaman terlebih dahulu.'); window.location.href='list_barang.php';</script>";
            exit(); // Penting: Hentikan eksekusi script setelah alert
        } else {
            // Jika tidak ada peminjaman aktif, lanjutkan dengan penghapusan
            mysqli_autocommit($koneksi, FALSE); // Mulai transaksi
            $transaction_successful = true;

            // 1. Hapus semua catatan peminjaman terkait dari tabel 'peminjaman'
            // Ini harus dilakukan SEBELUM menghapus dari 'data_barang'
            $delete_peminjaman_sql = "DELETE FROM peminjaman WHERE id_barang = '$id_to_delete'";
            if (!mysqli_query($koneksi, $delete_peminjaman_sql)) {
                $error_message_delete = "Gagal menghapus catatan peminjaman terkait: " . mysqli_error($koneksi);
                $transaction_successful = false;
            }

            // 2. Hapus barang dari tabel 'data_barang'
            if ($transaction_successful) {
                $delete_barang_sql = "DELETE FROM data_barang WHERE id = '$id_to_delete'";
                if (!mysqli_query($koneksi, $delete_barang_sql)) {
                    $error_message_delete = "Gagal menghapus barang: " . mysqli_error($koneksi);
                    $transaction_successful = false;
                }
            }

            // Commit atau Rollback transaksi
            if ($transaction_successful) {
                mysqli_commit($koneksi);
                header("Location: list_barang.php?status=deleted");
                exit();
            } else {
                mysqli_rollback($koneksi);
                // Jika transaksi gagal, tampilkan alert dengan pesan error
                echo "<script>alert('Error menghapus barang: " . $error_message_delete . "'); window.location.href='list_barang.php';</script>";
                exit(); // Penting: Hentikan eksekusi script setelah alert
            }
            mysqli_autocommit($koneksi, TRUE); // Kembalikan auto-commit
        }
    }
    // --- END Handle Delete Operation ---
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
                    <li class="active"><a href="#"><i class="fas fa-boxes"></i> Aset Inventaris</a></li>
                    <li><a href="#"><i class="fas fa-briefcase"></i> Bisnis</a></li>
                    <li><a href="#"><i class="fas fa-users"></i> Kelola User</a></li>
                </ul>
            </nav>
        </div>
        <div class="main-content">
            <header class="header">
                <div class="header-left">
                    <h1>Data Barang</h1>
                    <a href="peminjaman_barang.php" class="add-item-btn pinjam"><i class="fas fa-hand-holding"></i> Peminjaman</a> 
                </div>

                <div class="header-right">
                    <div class="user-profile">
                        <img src="../assets/img/profile-placeholder.jpg" alt="User Profile">
                    </div>
                    <div class="header-actions-row">
                        <div class="search-bar">
                            <input type="text" placeholder="Search">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </div>
                        <a href="add_barang.php" class="add-item-btn"><i class="fas fa-plus"></i> Tambah Barang</a>
                    </div>
                </div>
            </header>

            <div class="content-area">
                <div class="tabs">
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Barang</th>
                                <th>Kode Barang</th>
                                <th>Tanggal Beli</th>
                                <th>Pembeli</th>
                                <th>Stok Keseluruhan</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch data from the database
                            $sql = "SELECT * FROM data_barang";
                            $result = mysqli_query($koneksi, $sql);

                            if (mysqli_num_rows($result) > 0) {
                                $no = 1;
                                while($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>" . $no++ . "</td>";
                                    echo "<td>" . htmlspecialchars($row['nama_barang']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['kode_barang']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['tanggal_beli']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['pembeli']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['stok_keseluruhan']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['keterangan']) . "</td>";
                                    echo "<td class='actions'>";
                                    echo "<a href='edit_barang.php?id=" . $row['id'] . "' class='edit-btn'><i class='fas fa-edit'></i></a>";
                                    echo "<button class='delete-btn' onclick=\"showConfirmModal('list_barang.php?delete_id=" . $row['id'] . "')\"><i class='fas fa-trash-alt'></i></button>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='9'>Tidak ada data barang.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="confirm-delete-modal" class="modal-overlay-new" style="display: none;">
  <div class="modal-content-new">
    <h2>Yakin Hapus ?</h2>
    <div class="modal-footer-new">
      <button class="btn-modal secondary" onclick="closeConfirmModal()">Tidak</button>
      <button class="btn-modal danger" id="confirm-delete-btn">Ya</button>
    </div>
  </div>
</div>

<div id="success-delete-modal" class="modal-overlay-new" style="display: none;">
  <div class="modal-content-new">
    <h2>DATA BERHASIL DI HAPUS</h2>
    <div class="icon-container-new delete">
        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
    </div>
  </div>
</div>

    <script>
        // Fungsi untuk memunculkan modal konfirmasi
        function showConfirmModal(deleteUrl) {
            const modal = document.getElementById('confirm-delete-modal');
            modal.style.display = 'flex';

            // Saat tombol "Ya" diklik, arahkan ke URL hapus
            document.getElementById('confirm-delete-btn').onclick = function() {
                window.location.href = deleteUrl;
            };
        }

        // Fungsi untuk menutup modal konfirmasi
        function closeConfirmModal() {
            document.getElementById('confirm-delete-modal').style.display = 'none';
        }

        // Cek jika ada parameter status=deleted di URL (setelah redirect dari proses hapus)
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('status') === 'deleted') {
                const modal = document.getElementById('success-delete-modal');
                modal.style.display = 'flex';

                // Sembunyikan modal setelah 2 detik
                setTimeout(function() {
                    modal.style.display = 'none';
                    // Hapus parameter status dari URL agar tidak muncul lagi saat refresh
                    window.history.replaceState({}, document.title, window.location.pathname); 
                }, 2000);
            }
        });

        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('status') === 'deleted') {
                alert('Data barang berhasil dihapus!');
                const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                window.history.replaceState({ path: newUrl }, '', newUrl);
            }
            if (urlParams.get('status') === 'added') {
                alert('Data barang berhasil ditambahkan!');
                const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                window.history.replaceState({ path: newUrl }, '', newUrl);
            }
             if (urlParams.get('status') === 'updated') {
                alert('Data barang berhasil diperbarui!');
                const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                window.history.replaceState({ path: newUrl }, '', newUrl);
            }
        };
    </script>
</body>
</html>
<?php mysqli_close($koneksi); ?>