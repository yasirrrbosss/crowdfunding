<?php
// donasi/sukses.php
require_once '../config/koneksi.php';

$id_kampanye = $_GET['id'] ?? 0;
$db = getDB();

// Ambil data kampanye
$query = "SELECT judul FROM kampanye WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id_kampanye]);
$kampanye = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donasi Berhasil - CrowdFund</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .success-container {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
        }
        
        .success-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            color: #28a745;
        }
        
        .success-title {
            font-size: 2rem;
            color: #333;
            margin-bottom: 1rem;
        }
        
        .success-message {
            color: #666;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin: 0.5rem;
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
        
        .btn-secondary {
            background: #f8f9fa;
            color: #333;
            border: 2px solid #e1e5e9;
        }
        
        .btn-secondary:hover {
            background: #e9ecef;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">✅</div>
        <h1 class="success-title">Donasi Berhasil!</h1>
        <p class="success-message">
            Terima kasih atas donasi Anda untuk kampanye 
            <strong><?php echo htmlspecialchars($kampanye['judul'] ?? 'Unknown'); ?></strong>. 
            Kontribusi Anda sangat berarti untuk kesuksesan kampanye ini.
        </p>
        
        <div>
            <a href="../kampanye/detail.php?id=<?php echo $id_kampanye; ?>" class="btn btn-primary">
                Kembali ke Kampanye
            </a>
            <a href="../index.php" class="btn btn-secondary">
                Lihat Kampanye Lain
            </a>
        </div>
    </div>
</body>
</html>