<?php
// donasi/kirim.php
require_once '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = getDB();
    
    try {
        $id_kampanye = (int)$_POST['id_kampanye'];
        $nama_donatur = trim($_POST['nama_donatur']);
        $jumlah = (int)$_POST['jumlah'];
        $pesan = trim($_POST['pesan']);
        
        // Validasi input
        if (empty($nama_donatur) || $jumlah < 1000) {
            throw new Exception("Data tidak valid");
        }
        
        // Cek apakah kampanye masih aktif
        $query_check = "SELECT status FROM kampanye WHERE id = ?";
        $stmt_check = $db->prepare($query_check);
        $stmt_check->execute([$id_kampanye]);
        $kampanye = $stmt_check->fetch();
        
        if (!$kampanye || $kampanye['status'] != 'aktif') {
            throw new Exception("Kampanye tidak aktif atau tidak ditemukan");
        }
        
        // Insert donasi
        $query = "INSERT INTO donasi (id_kampanye, nama_donatur, jumlah, pesan, tanggal_donasi) 
                  VALUES (?, ?, ?, ?, NOW())";
        $stmt = $db->prepare($query);
        $stmt->execute([$id_kampanye, $nama_donatur, $jumlah, $pesan]);
        
        // Redirect ke halaman sukses
        header("Location: sukses.php?id=" . $id_kampanye);
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        header("Location: ../kampanye/detail.php?id=" . $id_kampanye . "&error=" . urlencode($error));
        exit;
    }
} else {
    header("Location: ../index.php");
    exit;
}
?>