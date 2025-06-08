<?php
// kampanye/detail.php
require_once '../config/koneksi.php';

$db = getDB();
$id = $_GET['id'] ?? 0;

// Ambil detail kampanye
$query = "SELECT k.*, 
          COALESCE(SUM(d.jumlah), 0) as total_donasi,
          COUNT(d.id) as jumlah_donatur
          FROM kampanye k 
          LEFT JOIN donasi d ON k.id = d.id_kampanye 
          WHERE k.id = ? 
          GROUP BY k.id";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$kampanye = $stmt->fetch();

if (!$kampanye) {
    header('Location: ../index.php');
    exit;
}

// Ambil daftar donatur
$query_donatur = "SELECT nama_donatur, jumlah, pesan, tanggal_donasi 
                  FROM donasi 
                  WHERE id_kampanye = ? 
                  ORDER BY tanggal_donasi DESC";
$stmt_donatur = $db->prepare($query_donatur);
$stmt_donatur->execute([$id]);
$donatur_list = $stmt_donatur->fetchAll();

$progress = $kampanye['target_dana'] > 0 ? 
           ($kampanye['total_donasi'] / $kampanye['target_dana']) * 100 : 0;
$progress = min($progress, 100);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($kampanye['judul']); ?> - CrowdFund</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 20px;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 1rem;
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
        
        .campaign-detail {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .campaign-image {
            width: 100%;
            height: 400px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
        }
        
        .campaign-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .campaign-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            padding: 2rem;
        }
        
        .main-content h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #333;
        }
        
        .campaign-meta {
            display: flex;
            gap: 2rem;
            margin-bottom: 1.5rem;
            color: #666;
            font-size: 0.9rem;
        }
        
        .description {
            line-height: 1.8;
            color: #555;
            margin-bottom: 2rem;
        }
        
        .sidebar {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 15px;
            height: fit-content;
        }
        
        .progress-section {
            margin-bottom: 2rem;
        }
        
        .amount-display {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        
        .target-display {
            color: #666;
            margin-bottom: 1rem;
        }
        
        .progress-bar {
            background: #e9ecef;
            height: 12px;
            border-radius: 6px;
            margin: 1rem 0;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 6px;
            transition: width 0.3s;
        }
        
        .stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-item {
            text-align: center;
            padding: 1rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #666;
        }
        
        .btn-donate {
            width: 100%;
            padding: 15px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
            text-align: center;
        }
        
        .btn-donate:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .donatur-section {
            margin-top: 3rem;
            grid-column: 1 / -1;
        }
        
        .donatur-section h3 {
            margin-bottom: 1.5rem;
            color: #333;
        }
        
        .donatur-list {
            display: grid;
            gap: 1rem;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .donatur-item {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        
        .donatur-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .donatur-name {
            font-weight: bold;
            color: #333;
        }
        
        .donatur-amount {
            color: #667eea;
            font-weight: bold;
        }
        
        .donatur-message {
            color: #666;
            font-style: italic;
            margin-bottom: 0.5rem;
        }
        
        .donatur-date {
            font-size: 0.8rem;
            color: #999;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            position: relative;
        }
        
        .close {
            position: absolute;
            right: 1rem;
            top: 1rem;
            font-size: 2rem;
            cursor: pointer;
            color: #999;
        }
        
        .close:hover {
            color: #333;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        @media (max-width: 768px) {
            .campaign-content {
                grid-template-columns: 1fr;
            }
            
            .campaign-meta {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="../index.php" class="back-link">← Kembali ke Beranda</a>
        
        <div class="campaign-detail">
            <div class="campaign-image">
                <?php if ($kampanye['gambar']): ?>
                    <img src="../assets/<?php echo htmlspecialchars($kampanye['gambar']); ?>" 
                         alt="<?php echo htmlspecialchars($kampanye['judul']); ?>">
                <?php else: ?>
                    📸 Foto Kampanye
                <?php endif; ?>
            </div>
            
            <div class="campaign-content">
                <div class="main-content">
                    <h1><?php echo htmlspecialchars($kampanye['judul']); ?></h1>
                    
                    <div class="campaign-meta">
                        <span>📅 Mulai: <?php echo date('d M Y', strtotime($kampanye['tanggal_mulai'])); ?></span>
                        <span>⏰ Berakhir: <?php echo date('d M Y', strtotime($kampanye['tanggal_berakhir'])); ?></span>
                        <span>📊 Status: <?php echo ucfirst($kampanye['status']); ?></span>
                    </div>
                    
                    <div class="description">
                        <?php echo nl2br(htmlspecialchars($kampanye['deskripsi'])); ?>
                    </div>
                </div>
                
                <div class="sidebar">
                    <div class="progress-section">
                        <div class="amount-display">
                            Rp <?php echo number_format($kampanye['total_donasi'], 0, ',', '.'); ?>
                        </div>
                        <div class="target-display">
                            dari target Rp <?php echo number_format($kampanye['target_dana'], 0, ',', '.'); ?>
                        </div>
                        
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $progress; ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="stats">
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $kampanye['jumlah_donatur']; ?></div>
                            <div class="stat-label">Donatur</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number"><?php echo round($progress, 1); ?>%</div>
                            <div class="stat-label">Tercapai</div>
                        </div>
                    </div>
                    
                    <?php if ($kampanye['status'] == 'aktif'): ?>
                        <button class="btn-donate" onclick="openDonateModal()">
                            💝 Donasi Sekarang
                        </button>
                    <?php else: ?>
                        <div style="text-align: center; padding: 1rem; color: #666;">
                            Kampanye telah berakhir
                        </div>
                    <?php endif; ?>
                    
                    <!-- Admin Actions -->
                    <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                        <a href="edit.php?id=<?php echo $kampanye['id']; ?>" 
                           style="flex: 1; padding: 10px; background: #28a745; color: white; text-decoration: none; border-radius: 8px; text-align: center; font-size: 0.9rem;">
                            ✏️ Edit
                        </a>
                        <a href="hapus.php?id=<?php echo $kampanye['id']; ?>" 
                           style="flex: 1; padding: 10px; background: #dc3545; color: white; text-decoration: none; border-radius: 8px; text-align: center; font-size: 0.9rem;"
                           onclick="return confirm('Yakin ingin menghapus kampanye ini?')">
                            🗑️ Hapus
                        </a>
                    </div>
                </div>
                
                <div class="donatur-section">
                    <h3>Daftar Donatur (<?php echo count($donatur_list); ?>)</h3>
                    
                    <?php if (empty($donatur_list)): ?>
                        <div style="text-align: center; padding: 2rem; color: #666;">
                            Belum ada donatur. Jadilah yang pertama!
                        </div>
                    <?php else: ?>
                        <div class="donatur-list">
                            <?php foreach ($donatur_list as $donatur): ?>
                                <div class="donatur-item">
                                    <div class="donatur-header">
                                        <span class="donatur-name"><?php echo htmlspecialchars($donatur['nama_donatur']); ?></span>
                                        <span class="donatur-amount">
                                            Rp <?php echo number_format($donatur['jumlah'], 0, ',', '.'); ?>
                                        </span>
                                    </div>
                                    <?php if ($donatur['pesan']): ?>
                                        <div class="donatur-message">
                                            "<?php echo htmlspecialchars($donatur['pesan']); ?>"
                                        </div>
                                    <?php endif; ?>
                                    <div class="donatur-date">
                                        <?php echo date('d M Y H:i', strtotime($donatur['tanggal_donasi'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Donasi -->
    <div id="donateModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeDonateModal()">&times;</span>
            <h2 style="margin-bottom: 1.5rem; color: #333;">Donasi untuk Kampanye</h2>
            
            <form action="../donasi/kirim.php" method="POST">
                <input type="hidden" name="id_kampanye" value="<?php echo $kampanye['id']; ?>">
                
                <div class="form-group">
                    <label for="nama_donatur">Nama Anda *</label>
                    <input type="text" id="nama_donatur" name="nama_donatur" required 
                           placeholder="Masukkan nama Anda">
                </div>
                
                <div class="form-group">
                    <label for="jumlah">Jumlah Donasi (Rp) *</label>
                    <input type="number" id="jumlah" name="jumlah" required min="1000" 
                           placeholder="10000">
                </div>
                
                <div class="form-group">
                    <label for="pesan">Pesan (Opsional)</label>
                    <textarea id="pesan" name="pesan" rows="3" 
                              placeholder="Tinggalkan pesan dukungan..."></textarea>
                </div>
                
                <button type="submit" class="btn-donate">
                    Kirim Donasi
                </button>
            </form>
        </div>
    </div>
    
    <script>
        function openDonateModal() {
            document.getElementById('donateModal').style.display = 'block';
        }
        
        function closeDonateModal() {
            document.getElementById('donateModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('donateModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>