<?php
require_once 'config.php';

class Auth {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function register($username, $email, $password) {
        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
            return ['success' => false, 'error' => 'Invalid username format'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Invalid email format'];
        }
        
        if (strlen($password) < 8) {
            return ['success' => false, 'error' => 'Password must be at least 8 characters'];
        }
        
        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO users (username, email, password_hash, registration_ip, user_agent)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([$username, $email, $passwordHash, $ip, $userAgent]);
            
            $userId = $this->pdo->lastInsertId();
            $this->createSession($userId);
            
            return ['success' => true, 'data' => ['user_id' => $userId, 'username' => $username]];
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                return ['success' => false, 'error' => 'Username or email already exists'];
            }
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Registration failed'];
        }
    }
    
    public function login($username, $password) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, username, email, password_hash 
                FROM users 
                WHERE username = ? OR email = ?
            ");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($password, $user['password_hash'])) {
                return ['success' => false, 'error' => 'Invalid credentials'];
            }
            
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            
            $updateStmt = $this->pdo->prepare("
                UPDATE users 
                SET last_login_ip = ?, last_login_at = NOW(), user_agent = ?
                WHERE id = ?
            ");
            $updateStmt->execute([$ip, $userAgent, $user['id']]);
            
            $this->createSession($user['id']);
            
            return [
                'success' => true, 
                'data' => [
                    'user_id' => $user['id'],
                    'username' => $user['username']
                ]
            ];
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Login failed'];
        }
    }
    
    private function createSession($userId) {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+6 months'));
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO sessions (session_token, user_id, ip_address, user_agent, expires_at)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$token, $userId, $ip, $userAgent, $expiresAt]);
            
            setcookie(
                'session_token',
                $token,
                [
                    'expires' => strtotime($expiresAt),
                    'path' => '/',
                    'httponly' => true,
                    'samesite' => 'Strict',
                    'secure' => isset($_SERVER['HTTPS'])
                ]
            );
            
            $_SESSION['user_id'] = $userId;
            $_SESSION['session_token'] = $token;
        } catch (PDOException $e) {
            error_log("Session creation error: " . $e->getMessage());
        }
    }
    
    public function validateSession() {
        if (isset($_COOKIE['session_token'])) {
            $token = $_COOKIE['session_token'];
            
            try {
                $stmt = $this->pdo->prepare("
                    SELECT user_id, expires_at 
                    FROM sessions 
                    WHERE session_token = ? AND expires_at > NOW()
                ");
                $stmt->execute([$token]);
                $session = $stmt->fetch();
                
                if ($session) {
                    $_SESSION['user_id'] = $session['user_id'];
                    return $session['user_id'];
                }
            } catch (PDOException $e) {
                error_log("Session validation error: " . $e->getMessage());
            }
        }
        
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    }
    
    public function getUser($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, username, email, created_at, last_login_at
                FROM users 
                WHERE id = ?
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Get user error: " . $e->getMessage());
            return null;
        }
    }
    
    public function getUsers($currentUserId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, username, created_at
                FROM users 
                WHERE id != ?
                ORDER BY username
            ");
            $stmt->execute([$currentUserId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get users error: " . $e->getMessage());
            return [];
        }
    }
    
    public function logout() {
        if (isset($_COOKIE['session_token'])) {
            try {
                $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE session_token = ?");
                $stmt->execute([$_COOKIE['session_token']]);
            } catch (PDOException $e) {
                error_log("Logout error: " . $e->getMessage());
            }
            
            setcookie('session_token', '', time() - 3600, '/');
        }
        
        session_destroy();
    }
}
?>