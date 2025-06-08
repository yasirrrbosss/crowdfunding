<?php
// config/koneksi.php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;
    
    public function __construct() {
        // Gunakan environment variables untuk keamanan
        $this->host = $_ENV['DB_HOST'] ?? 'your-database-host.com';
        $this->db_name = $_ENV['DB_NAME'] ?? 'crowdfunding_db';
        $this->username = $_ENV['DB_USER'] ?? 'your_username';
        $this->password = $_ENV['DB_PASS'] ?? 'your_password';
    }
    
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                )
            );
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
}

// Fungsi helper untuk koneksi cepat
function getDB() {
    $database = new Database();
    return $database->getConnection();
}
?>