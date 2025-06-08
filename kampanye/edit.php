<?php
// kampanye/edit.php
require_once '../config/koneksi.php';

$db = getDB();
$id = $_GET['id'] ?? 0;
$message = '';
$error = '';

// Ambil data kampanye
$query = "SELECT * FROM kampanye WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$kampanye = $stmt->fetch();

if (!$kampanye) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $judul = trim($_POST['judul']);
        $deskripsi = trim($_POST['deskripsi']);
        $target_dana = (int)$_POST['target_dana'];
        $tanggal_mulai = $_POST['tanggal_mulai'];
        $tanggal_berakhir = $_POST['tanggal_berakhir'];
        $status = $_POST['status'];
        
        // Handle file upload
        $gambar = $kampanye['gambar']; // Keep existing image by default
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['gambar']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                // Delete old image if exists
                if ($kampanye['gambar'] && file_exists('../assets/' . $kampanye['gambar'])) {
                    unlink('../assets/' . $kampanye['gambar']);
                }
                
                $gambar = time() . '_' . $filename;
                move_uploaded_file($_FILES['gambar']['tmp_name'], '../assets/' . $gambar);
            }
        }
        
        $query = "UPDATE kampanye 
                  SET judul = ?, deskripsi = ?, target_dana = ?, tanggal_mulai = ?, 
                      tanggal_berakhir = ?, gambar = ?, status = ? 
                  WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$judul, $deskripsi, $target_dana, $tanggal_mulai, $tanggal_berakhir, $gambar, $status, $id]);
        
        $message = "Kampanye berhasil diperbarui!";
        
        // Refresh data kampanye
        $stmt = $db->prepare("SELECT * FROM kampanye WHERE id = ?");
        $stmt->execute([$id]);
        $kampanye = $stmt->fetch();
        
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
    <title>Edit Kampanye - <?php echo htmlspecialchars($kampanye['judul']); ?></title>
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
        select,
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
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        select {
            cursor: pointer;
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
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
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
        
        .current-image {
            margin-top: 0.5rem;
            text-align: center;
        }
        
        .current-image img {
            max-width: 200px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #e1e5e9;
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
        
        .info-box {
            background: #e7f3ff;
            border: 1px solid #b8daff;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            color: #004085;
        }
        
        .status-info {
            font-size: 0.9rem;
            color: #666;
            margin-top: 0.5rem;
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
        <a href="detail.php?id=<?php echo $kampanye['id']; ?>" class="back-link">← Kembali ke Detail Kampanye</a>
        
        <div class="form-container">
            <div class="header">
                <h1>Edit Kampanye</h1>
                <p>Perbarui informasi kampanye Anda</p>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="info-box">
                <strong>📊 Informasi Kampanye:</strong><br>
                ID: #<?php echo $kampanye['id']; ?> | 
                Status: <?php echo ucfirst($kampanye['status']); ?> | 
                Dibuat: <?php echo date('d M Y', strtotime($kampanye['created_at'] ?? 'now')); ?>
            </div>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="judul">Judul Kampanye *</label>
                    <input type="text" id="judul" name="judul" required 
                           value="<?php echo htmlspecialchars($kampanye['judul']); ?>"
                           placeholder="Masukkan judul kampanye yang menarik">
                </div>
                
                <div class="form-group">
                    <label for="deskripsi">Deskripsi Kampanye *</label>
                    <textarea id="deskripsi" name="deskripsi" required 
                              placeholder="Ceritakan tentang kampanye Anda secara detail..."><?php echo htmlspecialchars($kampanye['deskripsi']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="target_dana">Target Dana (Rp) *</label>
                    <input type="number" id="target_dana" name="target_dana" required min="1"
                           value="<?php echo $kampanye['target_dana']; ?>"
                           placeholder="1000000">
                </div>
                
                <div class="form-group">
                    <label for="tanggal_mulai">Tanggal Mulai *</label>
                    <input type="date" id="tanggal_mulai" name="tanggal_mulai" required
                           value="<?php echo $kampanye['tanggal_mulai']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="tanggal_berakhir">Tanggal Berakhir *</label>
                    <input type="date" id="tanggal_berakhir" name="tanggal_berakhir" required
                           value="<?php echo $kampanye['tanggal_berakhir']; ?>">
                </div>
                
                <div class="form-group">
                    <label for="status">Status Kampanye *</label>
                    <select id="status" name="status" required>
                        <option value="aktif" <?php echo $kampanye['status'] == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="selesai" <?php echo $kampanye['status'] == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                        <option value="ditutup" <?php echo $kampanye['status'] == 'ditutup' ? 'selected' : ''; ?>>Ditutup</option>
                    </select>
                    <div class="status-info">
                        <small>
                            <strong>Aktif:</strong> Kampanye dapat menerima donasi | 
                            <strong>Selesai:</strong> Target tercapai | 
                            <strong>Ditutup:</strong> Kampanye dihentikan
                        </small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="gambar">Gambar Kampanye</label>
                    <input type="file" id="gambar" name="gambar" accept="image/*">
                    
                    <?php if ($kampanye['gambar']): ?>
                        <div class="current-image">
                            <p><strong>Gambar saat ini:</strong></p>
                            <img src="../assets/<?php echo htmlspecialchars($kampanye['gambar']); ?>" 
                                 alt="<?php echo htmlspecialchars($kampanye['judul']); ?>">
                            <p><small>Upload file baru untuk mengganti gambar</small></p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
                    <a href="detail.php?id=<?php echo $kampanye['id']; ?>" class="btn btn-secondary">Batal</a>
                    <a href="hapus.php?id=<?php echo $kampanye['id']; ?>" class="btn btn-danger" 
                       onclick="return confirm('Yakin ingin menghapus kampanye ini? Tindakan ini tidak dapat dibatalkan!')">
                       🗑️ Hapus Kampanye
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Date validation
        document.getElementById('tanggal_mulai').addEventListener('change', function() {
            document.getElementById('tanggal_berakhir').setAttribute('min', this.value);
        });
        
        // File preview
        document.getElementById('gambar').addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const file = e.target.files[0];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    // Create preview if no current image exists
                    if (!document.querySelector('.current-image')) {
                        const preview = document.createElement('div');
                        preview.className = 'current-image';
                        preview.innerHTML = `
                            <p><strong>Preview:</strong></p>
                            <img src="${e.target.result}" alt="Preview" style="max-width: 200px; height: 120px; object-fit: cover; border-radius: 10px; border: 2px solid #e1e5e9;">
                        `;
                        document.getElementById('gambar').parentNode.appendChild(preview);
                    } else {
                        // Update existing preview
                        document.querySelector('.current-image img').src = e.target.result;
                    }
                };
                
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>