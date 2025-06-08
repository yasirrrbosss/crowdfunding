<?php
// config/koneksi.php - AWS RDS Configuration
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    private $conn;
    
    public function __construct() {
        // AWS RDS Stockholm Configuration
        $this->host = $_ENV['DB_HOST'] ?? 'database-1.cf4os2008eu1.eu-north-1.rds.amazonaws.com';
        $this->port = $_ENV['DB_PORT'] ?? '3306';
        $this->db_name = $_ENV['DB_NAME'] ?? 'crowdfunding_db';
        $this->username = $_ENV['DB_USER'] ?? 'admin';
        $this->password = $_ENV['DB_PASS'] ?? 'jancok123'; // GANTI dengan password Anda
    }
    
    public function getConnection() {
        $this->conn = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            
            $options = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 30,
                PDO::ATTR_PERSISTENT => false
            );
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
            // Set timezone to Jakarta (WIB)
            $this->conn->exec("SET time_zone = '+07:00'");
            
            // Set charset
            $this->conn->exec("SET NAMES utf8mb4");
            
        } catch(PDOException $exception) {
            error_log("AWS RDS connection error: " . $exception->getMessage());
            
            // Show detailed error in development
            if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
                echo "❌ Connection error: " . $exception->getMessage() . "<br>";
                echo "🔍 Host: " . $this->host . "<br>";
                echo "🗄️ Database: " . $this->db_name . "<br>";
                echo "👤 User: " . $this->username . "<br>";
            } else {
                echo "Database temporarily unavailable. Please try again later.";
            }
        }
        
        return $this->conn;
    }
    
    public function testConnection() {
        $conn = $this->getConnection();
        if ($conn) {
            try {
                // Test query
                $stmt = $conn->query("SELECT 1 as test, NOW() as server_time, @@version as mysql_version");
                $result = $stmt->fetch();
                
                echo "✅ AWS RDS connection successful!<br>";
                echo "📍 Endpoint: " . $this->host . "<br>";
                echo "🗄️ Database: " . $this->db_name . "<br>";
                echo "🌍 Region: eu-north-1 (Stockholm)<br>";
                echo "⚡ MySQL Version: " . $result['mysql_version'] . "<br>";
                echo "🕒 Server Time: " . $result['server_time'] . "<br>";
                echo "🎯 Status: Connected and ready!<br>";
                
                // Test if tables exist
                $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                echo "📊 Tables found: " . count($tables) . "<br>";
                if (count($tables) > 0) {
                    echo "📋 Tables: " . implode(", ", $tables) . "<br>";
                } else {
                    echo "⚠️ No tables found. Please import database schema.<br>";
                }
                
                return true;
            } catch (Exception $e) {
                echo "❌ Connected but query failed: " . $e->getMessage();
                return false;
            }
        } else {
            echo "❌ AWS RDS connection failed!";
            return false;
        }
    }
}

// Helper function for quick database access
function getDB() {
    static $database = null;
    if ($database === null) {
        $database = new Database();
    }
    return $database->getConnection();
}

// Test connection endpoint
if (isset($_GET['test_db']) && $_GET['test_db'] === 'true') {
    echo "<!DOCTYPE html>";
    echo "<html><head><title>Database Connection Test</title></head><body>";
    echo "<h1>🧪 AWS RDS Connection Test</h1>";
    
    $db = new Database();
    $db->testConnection();
    
    echo "<br><hr>";
    echo "<p><a href='index.php'>← Back to Application</a></p>";
    echo "</body></html>";
    exit;
}
?>
