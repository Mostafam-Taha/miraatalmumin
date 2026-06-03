<?php
// config.php - Database configuration for InfinityFree
date_default_timezone_set('Africa/Cairo');

class Config {
    private static $instance = null;
    private $pdo;
    
    private $host = 'sql207.infinityfree.com';
    private $dbname = 'if0_39304815_hk';
    private $username = 'if0_39304815';
    private $password = 'NIHOIGYPkLGsq0';
    
    private function __construct() {
        try {
            $this->pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
}

function jsonResponse($success, $data = null, $error = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'error' => $error
    ]);
    exit;
}

function startSecureSession() {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    if (isset($_SERVER['HTTPS'])) {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}
?>