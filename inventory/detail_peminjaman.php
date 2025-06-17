<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Data Barang - Koran Mandala</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php
    include_once(__DIR__ . '/../config/koneksi.php');

    $barang_id = null;
    $barang_nama = "Detail Data Barang"; // Default title
    $search_query = "";
    $success_message = '';
    $error_message = '';

    // --- HANDLE DELETE OPERATION ---
    if (isset($_GET['delete_peminjaman_id']) && isset($_GET['barang_id_for_delete'])) {
        $peminjaman_id_to_delete = mysqli_real_escape_string($koneksi, $_GET['delete_peminjaman_id']);
        $barang_id_for_stock_update = mysqli_real_escape_string($koneksi, $_GET['barang_id_for_delete']);

        // 1. Ambil qty_pinjam dari catatan peminjaman yang akan dihapus
        $sql_get_qty = "SELECT qty_pinjam FROM peminjaman WHERE id = '$peminjaman_id_to_delete'";
        $result_get_qty = mysqli_query($koneksi, $sql_get_qty);
        
        if (mysqli_num_rows($result_get_qty) > 0) {
            $row_qty = mysqli_fetch_assoc($result_get_qty);
            $qty_to_return = $row_qty['qty_pinjam'];

            // Mulai transaksi
            mysqli_autocommit($koneksi, FALSE);
            $transaction_successful = true;

            // 2. Hapus catatan peminjaman
            $delete_sql = "DELETE FROM peminjaman WHERE id = '$peminjaman_id_to_delete'";
            if (!mysqli_query($koneksi, $delete_sql)) {
                $error_message = "Gagal menghapus catatan peminjaman: " . mysqli_error($koneksi);
                $transaction_successful = false;
            }

            // 3. Tambahkan kembali stok_tersedia di data_barang
            if ($transaction_successful) {
                $update_stock_sql = "UPDATE data_barang SET stok_tersedia = stok_tersedia + $qty_to_return WHERE id = '$barang_id_for_stock_update'";
                if (!mysqli_query($koneksi, $update_stock_sql)) {
                    $error_message = "Gagal mengembalikan stok barang: " . mysqli_error($koneksi);
                    $transaction_successful = false;
                }
            }

            // Commit atau Rollback transaksi
            if ($transaction_successful) {
                mysqli_commit($koneksi);
                $success_message = "Peminjaman berhasil dihapus dan stok dikembalikan!";
                header("Location: detail_peminjaman.php?id=" . $barang_id_for_stock_update . "&status=deleted_peminjaman");
                exit();
            } else {
                mysqli_rollback($koneksi);
                // Error message already set
            }
            mysqli_autocommit($koneksi, TRUE); // Kembalikan auto-commit
        } else {
            $error_message = "Catatan peminjaman tidak ditemukan untuk dihapus.";
        }
    }
    // --- END HANDLE DELETE OPERATION ---


    // Ambil ID Barang dari URL (setelah operasi delete jika ada)
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $barang_id = mysqli_real_escape_string($koneksi, $_GET['id']);

        // Ambil nama barang untuk judul halaman
        $sql_get_barang_name = "SELECT nama_barang FROM data_barang WHERE id = '$barang_id'";
        $result_get_barang_name = mysqli_query($koneksi, $sql_get_barang_name);
        if (mysqli_num_rows($result_get_barang_name) > 0) {
            $row_barang_name = mysqli_fetch_assoc($result_get_barang_name);
            $barang_nama = "Detail Barang: " . htmlspecialchars($row_barang_name['nama_barang']);
        } else {
            $barang_nama = "Detail Data Barang (Barang Tidak Ditemukan)";
        }

        // Handle Search Operation
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search_query = mysqli_real_escape_string($koneksi, $_GET['search']);
            $sql_peminjaman = "SELECT p.*, b.nama_barang, b.kode_barang
                               FROM peminjaman p
                               JOIN data_barang b ON p.id_barang = b.id
                               WHERE p.id_barang = '$barang_id'
                                 AND (p.peminjam_nama LIKE '%$search_query%' OR b.nama_barang LIKE '%$search_query%' OR b.kode_barang LIKE '%$search_query%')
                               ORDER BY p.tanggal_pinjam DESC";
        } else {
            // Query untuk mendapatkan data peminjaman untuk barang ini
            $sql_peminjaman = "SELECT p.*, b.nama_barang, b.kode_barang
                               FROM peminjaman p
                               JOIN data_barang b ON p.id_barang = b.id
                               WHERE p.id_barang = '$barang_id'
                               ORDER BY p.tanggal_pinjam DESC";
        }

        $result_peminjaman = mysqli_query($koneksi, $sql_peminjaman);

    } else {
        // Jika ID barang tidak diberikan di URL, arahkan kembali atau tampilkan pesan error
        header("Location: peminjaman_barang.php"); // Atau halaman daftar barang umum
        exit();
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
                <h1><?php echo htmlspecialchars($barang_nama); ?></h1>
                <div class="header-right">
                    <div class="search-bar">
                        <form action="" method="GET" style="display: flex;">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($barang_id); ?>">
                            <input type="text" name="search" placeholder="Search" value="<?php echo htmlspecialchars($search_query); ?>">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </form>
                    </div>
                    <div class="user-profile">
                        <img src="../assets/img/profile-placeholder.jpg" alt="User Profile">
                    </div>
                </div>
            </header>

            <div class="content-area">
                <div class="top-actions">
                    <a href="peminjaman_barang.php" class="btn-red-outline">Kembali</a>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Barang</th>
                                <th>Kode Barang</th>
                                <th>Nama Peminjam</th>
                                <th>Stok Yang Dipinjam</th>
                                <th>Tanggal Pinjam</th>
                                <th>Tanggal Pengembalian</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($barang_id === null) {
                                echo "<tr><td colspan='8'>ID Barang tidak ditemukan atau tidak valid.</td></tr>";
                            } elseif (mysqli_num_rows($result_peminjaman) > 0) {
                                $no = 1;
                                while($row = mysqli_fetch_assoc($result_peminjaman)) {
                                    echo "<tr>";
                                    echo "<td>" . $no++ . "</td>";
                                    echo "<td>" . htmlspecialchars($row['nama_barang']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['kode_barang']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['peminjam_nama']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['qty_pinjam']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['tanggal_pinjam']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['tanggal_kembali']) . "</td>";
                                    echo "<td class='actions'>";
                                    echo "<a href='edit_peminjaman.php?id=" . $row['id'] . "' class='edit-btn'><i class='fas fa-edit'></i></a>";
                                    // Tombol Delete Peminjaman
                                    echo "<button class='delete-btn' onclick=\"confirmDeletePeminjaman(" . $row['id'] . ", " . $barang_id . ", '" . htmlspecialchars($row['nama_barang']) . "', '" . htmlspecialchars($row['peminjam_nama']) . "')\"><i class='fas fa-trash-alt'></i></button>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8'>Tidak ada data peminjaman untuk barang ini.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="statusModal" class="modal-overlay" style="<?php echo ($success_message || $error_message) ? 'display: flex;' : 'display: none;'; ?>">
        <div class="modal-content">
            <?php if ($success_message): ?>
                <h2><?php echo htmlspecialchars($success_message); ?></h2>
                <i class="fas fa-check-circle icon-success"></i>
            <?php elseif ($error_message): ?>
                <h2 style="color: #dc3545;">Operasi Gagal</h2>
                <i class="fas fa-times-circle" style="color: #dc3545; font-size: 4em; margin-bottom: 20px;"></i>
                <p><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>
            <button class="btn-red" onclick="closeModal()">OK</button>
        </div>
    </div>

    <script>
        function confirmDeletePeminjaman(peminjamanId, barangId, namaBarang, namaPeminjam) {
            if (confirm("Apakah Anda yakin ingin menghapus catatan peminjaman barang '" + namaBarang + "' oleh '" + namaPeminjam + "'? Ini akan mengembalikan stok barang.")) {
                window.location.href = 'detail_peminjaman.php?delete_peminjaman_id=' + peminjamanId + '&barang_id_for_delete=' + barangId + '&id=<?php echo htmlspecialchars($barang_id); ?>';
            }
        }

        function closeModal() {
            document.getElementById('statusModal').style.display = 'none';
            // Redirect to remove status parameters from URL
            const urlParams = new URLSearchParams(window.location.search);
            const currentId = urlParams.get('id'); // Get the current barang_id
            const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + "?id=" + currentId;
            window.history.replaceState({ path: newUrl }, '', newUrl);
        }

        // Display success/error message on page load if status param exists
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('status') === 'deleted_peminjaman') {
                document.getElementById('statusModal').style.display = 'flex';
                document.querySelector('#statusModal h2').textContent = 'Peminjaman Berhasil Dihapus!';
                document.querySelector('#statusModal .icon-success').style.display = 'block';
                document.querySelector('#statusModal .fas.fa-times-circle').style.display = 'none';
                document.querySelector('#statusModal p').textContent = ''; // Clear potential error text
                // Clean up URL after displaying message
                const currentId = urlParams.get('id');
                const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + "?id=" + currentId;
                window.history.replaceState({ path: newUrl }, '', newUrl);
            }
            // If there's an error message from PHP after a failed delete, display it
            <?php if (!empty($error_message)): ?>
                document.getElementById('statusModal').style.display = 'flex';
            <?php endif; ?>
        };
    </script>
</body>
</html>
<?php mysqli_close($koneksi); ?>