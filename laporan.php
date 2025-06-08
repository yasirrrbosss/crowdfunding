<?php
// laporan.php
require_once 'config/koneksi.php';

$db = getDB();

// Statistik umum
$stats = [];

// Total kampanye
$query = "SELECT COUNT(*) as total FROM kampanye";
$stats['total_kampanye'] = $db->query($query)->fetchColumn();

// Kampanye aktif
$query = "SELECT COUNT(*) as total FROM kampanye WHERE status = 'aktif'";
$stats['kampanye_aktif'] = $db->query($query)->fetchColumn();

// Total donasi
$query = "SELECT COALESCE(SUM(jumlah), 0) as total FROM donasi";
$stats['total_donasi'] = $db->query($query)->fetchColumn();

// Total donatur
$query = "SELECT COUNT(*) as total FROM donasi";
$stats['total_donatur'] = $db->query($query)->fetchColumn();

// Kampanye dengan donasi terbanyak
$query = "SELECT k.id, k.judul, k.target_dana, COALESCE(SUM(d.jumlah), 0) as total_donasi,
          COUNT(d.id) as jumlah_donatur,
          (COALESCE(SUM(d.jumlah), 0) / k.target_dana * 100) as persentase
          FROM kampanye k 
          LEFT JOIN donasi d ON k.id = d.id_kampanye 
          GROUP BY k.id 
          ORDER BY total_donasi DESC 
          LIMIT 10";
$stmt = $db->prepare($query);
$stmt->execute();
$top_kampanye = $stmt->fetchAll();

// Donatur terbesar
$query = "SELECT nama_donatur, SUM(jumlah) as total_donasi, COUNT(*) as jumlah_donasi
          FROM donasi 
          GROUP BY nama_donatur 
          ORDER BY total_donasi DESC 
          LIMIT 10";
$stmt = $db->prepare($query);
$stmt->execute();
$top_donatur = $stmt->fetchAll();

// Donasi per bulan (6 bulan terakhir)
$query = "SELECT DATE_FORMAT(tanggal_donasi, '%Y-%m') as bulan,
          COUNT(*) as jumlah_donasi,
          SUM(jumlah) as total_amount
          FROM donasi 
          WHERE tanggal_donasi >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
          GROUP BY DATE_FORMAT(tanggal_donasi, '%Y-%m')
          ORDER BY bulan DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$donasi_bulanan = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - CrowdFund</title>
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
        
        .header {
            text-align: center;
            margin-bottom: 2rem;
            color: white;
        }
        
        .header h1 {
            font-size: 3rem;
            margin-bottom: 0.5rem;
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        
        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #666;
            font-weight: 500;
        }
        
        .report-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .section-title {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 0.5rem;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        .table th,
        .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e1e5e9;
        }
        
        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .table tr:hover {
            background: #f8f9fa;
        }
        
        .progress-mini {
            width: 100px;
            height: 6px;
            background: #e9ecef;
            border-radius: 3px;
            overflow: hidden;
        }
        
        .progress-mini-fill {
            height: 100%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 3px;
        }
        
        .amount {
            font-weight: bold;
            color: #667eea;
        }
        
        .chart-container {
            margin-top: 2rem;
        }
        
        .chart-bar {
            display: flex;
            align-items: center;
            margin: 1rem 0;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .chart-label {
            width: 100px;
            font-weight: 500;
            color: #333;
        }
        
        .chart-bar-fill {
            height: 20px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 10px;
            margin: 0 1rem;
            position: relative;
        }
        
        .chart-value {
            font-weight: bold;
            color: #667eea;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        
        .empty-state h3 {
            margin-bottom: 1rem;
            color: #333;
        }
        
        .kampanye-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .kampanye-link:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .table {
                font-size: 0.9rem;
            }
            
            .table th,
            .table td {
                padding: 8px 10px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
            
            .chart-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .chart-label {
                width: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">← Kembali ke Beranda</a>
        
        <div class="header">
            <h1>📊 Laporan Platform</h1>
            <p>Statistik dan analisis crowdfunding</p>
        </div>
        
        <!-- Statistik Umum -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🎯</div>
                <div class="stat-number"><?php echo number_format($stats['total_kampanye']); ?></div>
                <div class="stat-label">Total Kampanye</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🚀</div>
                <div class="stat-number"><?php echo number_format($stats['kampanye_aktif']); ?></div>
                <div class="stat-label">Kampanye Aktif</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-number">Rp <?php echo number_format($stats['total_donasi'], 0, ',', '.'); ?></div>
                <div class="stat-label">Total Donasi</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-number"><?php echo number_format($stats['total_donatur']); ?></div>
                <div class="stat-label">Total Donatur</div>
            </div>
        </div>
        
        <!-- Kampanye Teratas -->
        <div class="report-section">
            <h2 class="section-title">🏆 Kampanye dengan Donasi Terbanyak</h2>
            
            <?php if (empty($top_kampanye)): ?>
                <div class="empty-state">
                    <h3>Belum ada data kampanye</h3>
                    <p>Buat kampanye pertama untuk melihat statistik</p>
                    <a href="kampanye/tambah.php" style="color: #667eea; text-decoration: none; font-weight: 500;">Buat Kampanye →</a>
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ranking</th>
                            <th>Kampanye</th>
                            <th>Target</th>
                            <th>Terkumpul</th>
                            <th>Donatur</th>
                            <th>Progress</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_kampanye as $index => $kampanye): ?>
                            <tr>
                                <td>
                                    <strong>#<?php echo $index + 1; ?></strong>
                                    <?php if ($index < 3): ?>
                                        <?php echo ['🥇', '🥈', '🥉'][$index]; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($kampanye['judul']); ?></strong>
                                </td>
                                <td class="amount">
                                    Rp <?php echo number_format($kampanye['target_dana'], 0, ',', '.'); ?>
                                </td>
                                <td class="amount">
                                    Rp <?php echo number_format($kampanye['total_donasi'], 0, ',', '.'); ?>
                                </td>
                                <td><?php echo $kampanye['jumlah_donatur']; ?> orang</td>
                                <td>
                                    <div class="progress-mini">
                                        <div class="progress-mini-fill" 
                                             style="width: <?php echo min($kampanye['persentase'], 100); ?>%"></div>
                                    </div>
                                    <?php echo round($kampanye['persentase'], 1); ?>%
                                </td>
                                <td>
                                    <a href="kampanye/detail.php?id=<?php echo $kampanye['id']; ?>" class="kampanye-link">
                                        Lihat Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Donatur Teratas -->
        <div class="report-section">
            <h2 class="section-title">⭐ Donatur Terbesar</h2>
            
            <?php if (empty($top_donatur)): ?>
                <div class="empty-state">
                    <h3>Belum ada data donatur</h3>
                    <p>Tunggu donasi pertama untuk melihat statistik donatur</p>
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ranking</th>
                            <th>Nama Donatur</th>
                            <th>Total Donasi</th>
                            <th>Jumlah Donasi</th>
                            <th>Rata-rata Donasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_donatur as $index => $donatur): ?>
                            <tr>
                                <td>
                                    <strong>#<?php echo $index + 1; ?></strong>
                                    <?php if ($index < 3): ?>
                                        <?php echo ['🥇', '🥈', '🥉'][$index]; ?>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($donatur['nama_donatur']); ?></strong></td>
                                <td class="amount">
                                    Rp <?php echo number_format($donatur['total_donasi'], 0, ',', '.'); ?>
                                </td>
                                <td><?php echo $donatur['jumlah_donasi']; ?>x</td>
                                <td class="amount">
                                    Rp <?php echo number_format($donatur['total_donasi'] / $donatur['jumlah_donasi'], 0, ',', '.'); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Tren Donasi Bulanan -->
        <div class="report-section">
            <h2 class="section-title">📈 Tren Donasi 6 Bulan Terakhir</h2>
            
            <?php if (empty($donasi_bulanan)): ?>
                <div class="empty-state">
                    <h3>Belum ada data donasi bulanan</h3>
                    <p>Data akan muncul setelah ada transaksi donasi</p>
                </div>
            <?php else: ?>
                <div class="chart-container">
                    <?php 
                    $max_amount = max(array_column($donasi_bulanan, 'total_amount'));
                    foreach ($donasi_bulanan as $data): 
                        $width_percentage = $max_amount > 0 ? ($data['total_amount'] / $max_amount) * 100 : 0;
                        $bulan_nama = date('M Y', strtotime($data['bulan'] . '-01'));
                    ?>
                        <div class="chart-bar">
                            <div class="chart-label"><?php echo $bulan_nama; ?></div>
                            <div class="chart-bar-fill" style="width: <?php echo $width_percentage; ?>%"></div>
                            <div class="chart-value">
                                Rp <?php echo number_format($data['total_amount'], 0, ',', '.'); ?>
                                (<?php echo $data['jumlah_donasi']; ?> donasi)
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <table class="table" style="margin-top: 2rem;">
                    <thead>
                        <tr>
                            <th>Bulan</th>
                            <th>Total Donasi</th>
                            <th>Jumlah Transaksi</th>
                            <th>Rata-rata per Donasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donasi_bulanan as $data): ?>
                            <tr>
                                <td><strong><?php echo date('F Y', strtotime($data['bulan'] . '-01')); ?></strong></td>
                                <td class="amount">
                                    Rp <?php echo number_format($data['total_amount'], 0, ',', '.'); ?>
                                </td>
                                <td><?php echo $data['jumlah_donasi']; ?> transaksi</td>
                                <td class="amount">
                                    Rp <?php echo number_format($data['total_amount'] / $data['jumlah_donasi'], 0, ',', '.'); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Ringkasan -->
        <div class="report-section">
            <h2 class="section-title">📋 Ringkasan Platform</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                <div>
                    <h4 style="margin-bottom: 1rem; color: #667eea;">📊 Statistik Kampanye</h4>
                    <ul style="list-style: none; line-height: 2;">
                        <li>📋 Total kampanye dibuat: <strong><?php echo number_format($stats['total_kampanye']); ?></strong></li>
                        <li>🟢 Kampanye aktif: <strong><?php echo number_format($stats['kampanye_aktif']); ?></strong></li>
                        <li>🔴 Kampanye selesai: <strong><?php echo number_format($stats['total_kampanye'] - $stats['kampanye_aktif']); ?></strong></li>
                        <?php if ($stats['total_kampanye'] > 0): ?>
                            <li>📈 Tingkat aktivitas: <strong><?php echo round(($stats['kampanye_aktif'] / $stats['total_kampanye']) * 100, 1); ?>%</strong></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div>
                    <h4 style="margin-bottom: 1rem; color: #667eea;">💰 Statistik Donasi</h4>
                    <ul style="list-style: none; line-height: 2;">
                        <li>💵 Total dana terkumpul: <strong>Rp <?php echo number_format($stats['total_donasi'], 0, ',', '.'); ?></strong></li>
                        <li>👥 Total donatur: <strong><?php echo number_format($stats['total_donatur']); ?> orang</strong></li>
                        <?php if ($stats['total_donatur'] > 0): ?>
                            <li>📊 Rata-rata donasi: <strong>Rp <?php echo number_format($stats['total_donasi'] / $stats['total_donatur'], 0, ',', '.'); ?></strong></li>
                        <?php endif; ?>
                        <?php if ($stats['total_kampanye'] > 0): ?>
                            <li>📈 Rata-rata per kampanye: <strong>Rp <?php echo number_format($stats['total_donasi'] / $stats['total_kampanye'], 0, ',', '.'); ?></strong></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            
            <?php if ($stats['total_kampanye'] > 0 && $stats['total_donatur'] > 0): ?>
                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e1e5e9;">
                    <h4 style="margin-bottom: 1rem; color: #667eea;">🎯 Insight Platform</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                        <div style="background: #e7f3ff; padding: 1rem; border-radius: 8px; border-left: 4px solid #667eea;">
                            <strong>Partisipasi Donatur</strong><br>
                            <small>Rata-rata <?php echo round($stats['total_donatur'] / $stats['kampanye_aktif'], 1); ?> donatur per kampanye aktif</small>
                        </div>
                        
                        <div style="background: #fff2e7; padding: 1rem; border-radius: 8px; border-left: 4px solid #fd7e14;">
                            <strong>Efektivitas Fundraising</strong><br>
                            <small>Platform berhasil menggalang dana dengan partisipasi aktif komunitas</small>
                        </div>
                        
                        <div style="background: #e8f5e8; padding: 1rem; border-radius: 8px; border-left: 4px solid #28a745;">
                            <strong>Potensi Pertumbuhan</strong><br>
                            <small>Tingkat engagement yang baik menunjukkan potensi ekspansi</small>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>