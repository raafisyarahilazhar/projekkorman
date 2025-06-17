-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 18 Jun 2025 pada 00.30
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `koran_mandala`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `data_barang`
--

CREATE TABLE `data_barang` (
  `id` int(11) NOT NULL,
  `nama_barang` varchar(255) NOT NULL,
  `kode_barang` varchar(50) NOT NULL,
  `tanggal_beli` date DEFAULT NULL,
  `pembeli` varchar(255) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `stok_keseluruhan` int(11) DEFAULT 0,
  `stok_tersedia` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `data_barang`
--

INSERT INTO `data_barang` (`id`, `nama_barang`, `kode_barang`, `tanggal_beli`, `pembeli`, `keterangan`, `stok_keseluruhan`, `stok_tersedia`) VALUES
(1, 'Handphone', '#HP0111', '2025-06-05', 'Syakir', 'Tidak Bisa Dipinjam', 10, 10),
(3, 'laptop', '0098', '2008-09-01', 'gober', 'Bisa Dipinjam', 100, 1),
(4, 'rfr', 'fref', '2025-06-18', 'lalala', 'Bisa Dipinjam', 10, 9),
(5, 'wefewr', 'ewe', '2025-06-18', 'we', 'Tidak Bisa Dipinjam', 3, 3),
(6, 'rausft', 'sdcdcds', '2025-06-18', 'eeee', 'Bisa Dipinjam', 33, 33),
(7, 'rausft', 'k hjs dhv', '2025-06-18', 'eeee', 'Bisa Dipinjam', 33, 33),
(9, 'we', 'wewe', '2025-06-18', 'wewewe', 'Bisa Dipinjam', 3, 0),
(10, 'sklnfvj', '7hdshsdh', '2025-06-18', 'kjsdkjf', 'Bisa Dipinjam', 55, 0),
(11, 'sklnfvj', '7hdshsdhf', '2025-06-18', 'kjsdkjf', 'Bisa Dipinjam', 55, 0),
(12, 'sds', 'sdfsre', '2025-06-18', 'sr', 'Bisa Dipinjam', 56, 0),
(13, 'jkbascbhasdbc', 'jhadcv', '2025-06-18', 'n adsc', 'Tidak Bisa Dipinjam', 44, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id` int(11) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `qty_pinjam` int(11) NOT NULL,
  `tanggal_pinjam` date NOT NULL,
  `tanggal_kembali` date DEFAULT NULL,
  `status_peminjaman` enum('dipinjam','dikembalikan','terlambat') DEFAULT 'dipinjam',
  `peminjam_nama` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `peminjaman`
--

INSERT INTO `peminjaman` (`id`, `id_barang`, `qty_pinjam`, `tanggal_pinjam`, `tanggal_kembali`, `status_peminjaman`, `peminjam_nama`) VALUES
(2, 1, 8, '2025-06-18', '2025-06-27', 'dipinjam', 'Syakir'),
(3, 3, 2, '2025-06-18', '2025-07-03', 'dipinjam', 'df jvfbd'),
(4, 4, 1, '2025-06-18', '2025-06-28', 'dipinjam', 'Akbar'),
(5, 3, 1, '2025-06-18', '2025-07-12', 'dipinjam', 'raafi');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `data_barang`
--
ALTER TABLE `data_barang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_barang` (`kode_barang`);

--
-- Indeks untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_barang` (`id_barang`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `data_barang`
--
ALTER TABLE `data_barang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`id_barang`) REFERENCES `data_barang` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
