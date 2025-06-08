<?php
// kampanye/tambah.php
require_once '../config/koneksi.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = getDB();
    
    try {
        $judul = trim($_POST['judul']);
        $deskripsi = trim($_POST['deskripsi']);
        $target_dana = (int)$_POST['target_dana'];
        $tanggal_mulai = $_POST['tanggal_mulai'];
        $tanggal_berakhir = $_POST['tanggal_berakhir'];
        
        // Handle file upload
        $gambar = '';
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['gambar']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $gambar = time() . '_' . $filename;
                move_uploaded_file($_FILES['gambar']['tmp_name'], '../assets/' . $gambar);
            }
        }
        
        $query = "INSERT INTO kampanye (judul, deskripsi, target_dana, tanggal_mulai, tanggal_berakhir, gambar, status) 
                  VALUES (?, ?, ?, ?, ?, ?, 'aktif')";
        $stmt = $db->prepare($query);
        $stmt->execute([$judul, $deskripsi, $target_dana, $tanggal_mulai, $tanggal_berakhir, $gambar]);
        
        $message = "Kampanye berhasil dibuat!";
        
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Kampanye - CrowdFund</title>
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
            padding: 2rem 0;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .form-container {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .header h1 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .header p {
            color: #666;
            font-size: 1.1rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        input[type="text"],
        input[type="number"],
        input[type="date"],
        input[type="file"],
        textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
            background: white;
        }
        
        input:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        textarea {
            resize: vertical;
            min-height: 120px;
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
        
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d1edff;
            color: #0c5460;
            border: 1px solid #b6effb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }
        
        .file-input-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }
        
        .file-input-label {
            display: block;
            padding: 12px 16px;
            border: 2px dashed #e1e5e9;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #f8f9fa;
        }
        
        .file-input-label:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 1rem;
            color: white;
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="../index.php" class="back-link">← Kembali ke Beranda</a>
        
        <div class="form-container">
            <div class="header">
                <h1>Buat Kampanye Baru</h1>
                <p>Mulai penggalangan dana untuk impian Anda</p>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <?php echo $message; ?>
                    <br><a href="../index.php">Kembali ke beranda</a>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="judul">Judul Kampanye *</label>
                    <input type="text" id="judul" name="judul" required 
                           placeholder="Masukkan judul kampanye yang menarik">
                </div>
                
                <div class="form-group">
                    <label for="deskripsi">Deskripsi Kampanye *</label>
                    <textarea id="deskripsi" name="deskripsi" required 
                              placeholder="Ceritakan tentang kampanye Anda secara detail..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="target_dana">Target Dana (Rp) *</label>
                    <input type="number" id="target_dana" name="target_dana" required min="1"
                           placeholder="1000000">
                </div>
                
                <div class="form-group">
                    <label for="tanggal_mulai">Tanggal Mulai *</label>
                    <input type="date" id="tanggal_mulai" name="tanggal_mulai" required>
                </div>
                
                <div class="form-group">
                    <label for="tanggal_berakhir">Tanggal Berakhir *</label>
                    <input type="date" id="tanggal_berakhir" name="tanggal_berakhir" required>
                </div>
                
                <div class="form-group">
                    <label for="gambar">Gambar Kampanye</label>
                    <div class="file-input-wrapper">
                        <input type="file" id="gambar" name="gambar" accept="image/*">
                        <label for="gambar" class="file-input-label">
                            📸 Pilih gambar untuk kampanye (Opsional)
                        </label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Buat Kampanye</button>
                    <a href="../index.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // File input enhancement
        document.getElementById('gambar').addEventListener('change', function(e) {
            const label = document.querySelector('.file-input-label');
            if (e.target.files.length > 0) {
                label.textContent = '📸 ' + e.target.files[0].name;
            } else {
                label.textContent = '📸 Pilih gambar untuk kampanye (Opsional)';
            }
        });
        
        // Date validation
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('tanggal_mulai').setAttribute('min', today);
        
        document.getElementById('tanggal_mulai').addEventListener('change', function() {
            document.getElementById('tanggal_berakhir').setAttribute('min', this.value);
        });
    </script>
</body>
</html>