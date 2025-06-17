<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Barang Yang Bisa Dipinjam - Koran Mandala</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php
    include_once(__DIR__ . '/../config/koneksi.php'); // Pastikan ini mengarah ke file koneksi Anda

    // Logika untuk pencarian (jika ada input search)
    $search_query = "";
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search_query = mysqli_real_escape_string($koneksi, $_GET['search']);
        $sql = "SELECT * FROM data_barang WHERE keterangan = 'Bisa Dipinjam' AND (nama_barang LIKE '%$search_query%' OR kode_barang LIKE '%$search_query%')";
    } else {
        $sql = "SELECT * FROM data_barang WHERE keterangan = 'Bisa Dipinjam'";
    }

    $result = mysqli_query($koneksi, $sql);
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
                <h1>Data Barang Yang Bisa Dipinjam</h1>
                <div class="header-right">
                    <div class="search-bar">
                        <form action="" method="GET" style="display: flex;">
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
                    <a href="list_barang.php" class="btn-red-outline">Kembali</a>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Barang</th>
                                <th>Kode Barang</th>
                                <th>Stok Keseluruhan</th>
                                <th>Stok Tersedia</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($result) > 0) {
                                $no = 1;
                                while($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>" . $no++ . "</td>";
                                    echo "<td>" . htmlspecialchars($row['nama_barang']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['kode_barang']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['stok_keseluruhan']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['stok_tersedia']) . "</td>"; // Menggunakan stok_tersedia dari database
                                    echo "<td class='actions'>";
                                    echo "<a href='detail_peminjaman.php?id=" . $row['id'] . "' class='btn-lihat'><i class='fas fa-eye'></i> Lihat</a>";
                                    $disable_pinjam = ($row['stok_tersedia'] <= 0) ? 'disabled' : ''; // Cek stok_tersedia dari database
                                    echo "<a href='input_peminjaman.php?id=" . $row['id'] . "' class='btn-pinjam " . ($row['stok_tersedia'] <= 0 ? 'disabled' : '') . "' " . ($row['stok_tersedia'] <= 0 ? 'aria-disabled="true"' : '') . "><i class='fas fa-hand-holding'></i> Pinjam</a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6'>Tidak ada data barang yang bisa dipinjam.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php mysqli_close($koneksi); ?>