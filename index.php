<?php
// index.php
require_once 'config/koneksi.php';

$db = getDB();

// Ambil data kampanye aktif
$query = "SELECT k.*, 
          COALESCE(SUM(d.jumlah), 0) as total_donasi,
          COUNT(d.id) as jumlah_donatur
          FROM kampanye k 
          LEFT JOIN donasi d ON k.id = d.id_kampanye 
          WHERE k.status = 'aktif' 
          GROUP BY k.id 
          ORDER BY k.tanggal_mulai DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$kampanyes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CrowdFund - Platform Penggalangan Dana</title>
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
            padding: 0 20px;
        }
        
        /* Header */
        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: #667eea;
        }
        
        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }
        
        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-links a:hover {
            color: #667eea;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        /* Hero Section */
        .hero {
            text-align: center;
            padding: 4rem 0;
            color: white;
        }
        
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        /* Campaign Grid */
        .campaigns {
            background: white;
            padding: 4rem 0;
        }
        
        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 3rem;
            color: #333;
        }
        
        .campaign-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }
        
        .campaign-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .campaign-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .campaign-image {
            height: 200px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }
        
        .campaign-content {
            padding: 1.5rem;
        }
        
        .campaign-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .campaign-description {
            color: #666;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .progress-bar {
            background: #f0f0f0;
            height: 8px;
            border-radius: 4px;
            margin: 1rem 0;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 4px;
            transition: width 0.3s;
        }
        
        .campaign-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #666;
        }
        
        .amount-raised {
            font-weight: bold;
            color: #667eea;
        }
        
        .btn-donate {
            width: 100%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-donate:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        /* Footer */
        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 2rem 0;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .hero h1 {
                font-size: 2rem;
            }
            
            .campaign-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav class="container">
            <div class="logo">CrowdFund</div>
            <ul class="nav-links">
                <li><a href="#home">Beranda</a></li>
                <li><a href="#campaigns">Kampanye</a></li>
                <li><a href="kampanye/tambah.php">Buat Kampanye</a></li>
                <li><a href="laporan.php">Laporan</a></li>
            </ul>
            <a href="kampanye/tambah.php" class="btn btn-primary">Mulai Kampanye</a>
        </nav>
    </header>

    <section class="hero" id="home">
        <div class="container">
            <h1>Wujudkan Impian Bersama</h1>
            <p>Platform crowdfunding terpercaya untuk menggalang dana dan mewujudkan mimpi</p>
            <a href="#campaigns" class="btn btn-primary">Lihat Kampanye</a>
        </div>
    </section>

    <section class="campaigns" id="campaigns">
        <div class="container">
            <h2 class="section-title">Kampanye Aktif</h2>
            <div class="campaign-grid">
                <?php if (empty($kampanyes)): ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 2rem; color: #666;">
                        <h3>Belum ada kampanye aktif</h3>
                        <p>Jadilah yang pertama membuat kampanye!</p>
                        <a href="kampanye/tambah.php" class="btn btn-primary" style="margin-top: 1rem;">Buat Kampanye</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($kampanyes as $kampanye): ?>
                        <?php 
                        $progress = $kampanye['target_dana'] > 0 ? 
                                   ($kampanye['total_donasi'] / $kampanye['target_dana']) * 100 : 0;
                        $progress = min($progress, 100);
                        ?>
                        <div class="campaign-card">
                            <div class="campaign-image">
                                <?php if ($kampanye['gambar']): ?>
                                    <img src="assets/<?php echo htmlspecialchars($kampanye['gambar']); ?>" 
                                         alt="<?php echo htmlspecialchars($kampanye['judul']); ?>"
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    📸 Foto Kampanye
                                <?php endif; ?>
                            </div>
                            <div class="campaign-content">
                                <h3 class="campaign-title"><?php echo htmlspecialchars($kampanye['judul']); ?></h3>
                                <p class="campaign-description"><?php echo htmlspecialchars($kampanye['deskripsi']); ?></p>
                                
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $progress; ?>%"></div>
                                </div>
                                
                                <div class="campaign-stats">
                                    <span class="amount-raised">
                                        Rp <?php echo number_format($kampanye['total_donasi'], 0, ',', '.'); ?>
                                    </span>
                                    <span>
                                        Target: Rp <?php echo number_format($kampanye['target_dana'], 0, ',', '.'); ?>
                                    </span>
                                </div>
                                
                                <div class="campaign-stats">
                                    <span><?php echo $kampanye['jumlah_donatur']; ?> donatur</span>
                                    <span><?php echo round($progress, 1); ?>% tercapai</span>
                                </div>
                                
                                <a href="kampanye/detail.php?id=<?php echo $kampanye['id']; ?>" class="btn-donate">
                                    Lihat Detail & Donasi
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; 2025 CrowdFund. Platform penggalangan dana terpercaya.</p>
        </div>
    </footer>
</body>
</html>