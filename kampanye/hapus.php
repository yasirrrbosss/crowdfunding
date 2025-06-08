<?php
// kampanye/hapus.php
require_once '../config/koneksi.php';

$db = getDB();
$id = $_GET['id'] ?? 0;

// Ambil data kampanye
$query = "SELECT * FROM kampanye WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$kampanye = $stmt->fetch();

if (!$kampanye) {
    header('Location: ../index.php');
    exit;
}

// Cek apakah ada donasi
$query_donasi = "SELECT COUNT(*) as total FROM donasi WHERE id_kampanye = ?";
$stmt_donasi = $db->prepare($query_donasi);
$stmt_donasi->execute([$id]);
$jumlah_donasi = $stmt_donasi->fetchColumn();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['confirm_delete'])) {
        try {
            $db->beginTransaction();
            
            // Hapus semua donasi terkait
            $query_delete_donasi = "DELETE FROM donasi WHERE id_kampanye = ?";
            $stmt_delete_donasi = $db->prepare($query_delete_donasi);
            $stmt_delete_donasi->execute([$id]);
            
            // Hapus gambar jika ada
            if ($kampanye['gambar'] && file_exists('../assets/' . $kampanye['gambar'])) {
                unlink('../assets/' . $kampanye['gambar']);
            }
            
            // Hapus kampanye
            $query_delete = "DELETE FROM kampanye WHERE id = ?";
            $stmt_delete = $db->prepare($query_delete);
            $stmt_delete->execute([$id]);
            
            $db->commit();
            
            // Redirect ke homepage dengan pesan sukses
            header('Location: ../index.php?deleted=1');
            exit;
            
        } catch (Exception $e) {
            $db->rollback();
            $error = "Error: " . $e->getMessage();
        }
    } else {
        header('Location: detail.php?id=' . $id);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus Kampanye - <?php echo htmlspecialchars($kampanye['judul']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .delete-container {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        
        .warning-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 1.5rem;
        }
        
        .delete-title {
            color: #333;
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .campaign-info {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            text-align: left;
        }
        
        .campaign-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 1rem;
        }
        
        .campaign-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .meta-item {
            background: white;
            padding: 0.8rem;
            border-radius: 8px;
            text-align: center;
        }
        
        .meta-label {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 0.2rem;
        }
        
        .meta-value {
            font-weight: bold;
            color: #333;
        }
        
        .warning-text {
            color: #666;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .danger-warning {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 10px;
            padding: 1rem;
            margin: 1.5rem 0;
            color: #721c24;
        }
        
        .danger-warning strong {
            color: #dc3545;
        }
        
        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            margin: 0.5rem;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(220, 53, 69, 0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .form-actions {
            margin-top: 2rem;
        }
        
        .back-link {
            position: absolute;
            top: 2rem;
            left: 2rem;
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 25px;
            transition: all 0.3s;
        }
        
        .back-link:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .back-link {
                position: relative;
                top: auto;
                left: auto;
                margin-bottom: 1rem;
                display: inline-block;
            }
            
            body {
                padding: 1rem;
            }
            
            .delete-container {
                padding: 2rem;
            }
            
            .campaign-meta {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <a href="detail.php?id=<?php echo $kampanye['id']; ?>" class="back-link">← Kembali</a>
    
    <div class="delete-container">
        <div class="warning-icon">⚠️</div>
        <h1 class="delete-title">Hapus Kampanye</h1>
        
        <?php if ($error): ?>
            <div class="alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <p class="warning-text">
            Anda akan menghapus kampanye berikut ini. Tindakan ini tidak dapat dibatalkan!
        </p>
        
        <div class="campaign-info">
            <div class="campaign-title">
                <?php echo htmlspecialchars($kampanye['judul']); ?>
            </div>
            
            <div class="campaign-meta">
                <div class="meta-item">
                    <div class="meta-label">Target Dana</div>
                    <div class="meta-value">Rp <?php echo number_format($kampanye['target_dana'], 0, ',', '.'); ?></div>
                </div>
                
                <div class="meta-item">
                    <div class="meta-label">Status</div>
                    <div class="meta-value"><?php echo ucfirst($kampanye['status']); ?></div>
                </div>
                
                <div class="meta-item">
                    <div class="meta-label">Tanggal Mulai</div>
                    <div class="meta-value"><?php echo date('d M Y', strtotime($kampanye['tanggal_mulai'])); ?></div>
                </div>
                
                <div class="meta-item">
                    <div class="meta-label">Tanggal Berakhir</div>
                    <div class="meta-value"><?php echo date('d M Y', strtotime($kampanye['tanggal_berakhir'])); ?></div>
                </div>
            </div>
        </div>
        
        <?php if ($jumlah_donasi > 0): ?>
            <div class="danger-warning">
                <strong>⚠️ PERINGATAN PENTING!</strong><br>
                Kampanye ini memiliki <strong><?php echo $jumlah_donasi; ?> donasi</strong> yang akan ikut terhapus. 
                Pastikan Anda telah mencatat semua informasi donatur sebelum melanjutkan.
            </div>
        <?php endif; ?>
        
        <div class="danger-warning">
            <strong>Data yang akan dihapus:</strong><br>
            ✗ Informasi kampanye<br>
            ✗ Semua donasi terkait (<?php echo $jumlah_donasi; ?> donasi)<br>
            ✗ Gambar kampanye<br>
            ✗ Riwayat transaksi
        </div>
        
        <form method="POST">
            <div class="form-actions">
                <button type="submit" name="confirm_delete" value="1" class="btn btn-danger"
                        onclick="return confirm('YAKIN INGIN MENGHAPUS? Tindakan ini tidak dapat dibatalkan!')">
                    🗑️ Ya, Hapus Kampanye
                </button>
                <a href="detail.php?id=<?php echo $kampanye['id']; ?>" class="btn btn-secondary">
                    ↩️ Batal
                </a>
            </div>
        </form>
    </div>
</body>
</html>