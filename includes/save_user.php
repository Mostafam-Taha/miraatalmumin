<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$host = 'localhost';
$dbname = 'prayer_tracker';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $credential = $data['credential'] ?? '';
    
    if (empty($credential)) {
        throw new Exception('لم يتم استلام بيانات الاعتماد');
    }
    
    $parts = explode('.', $credential);
    if (count($parts) !== 3) {
        throw new Exception('بيانات الاعتماد غير صالحة');
    }
    
    $payload = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', $parts[1]))), true);
    
    $google_id = $payload['sub'];
    $email = $payload['email'];
    $name = $payload['name'] ?? '';
    $profile_picture = $payload['picture'] ?? '';
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE google_id = :google_id OR email = :email");
    $stmt->execute(['google_id' => $google_id, 'email' => $email]);
    $existing_user = $stmt->fetch();
    
    if ($existing_user) {
        $stmt = $pdo->prepare("UPDATE users SET name = :name, profile_picture = :profile_picture WHERE id = :id");
        $stmt->execute([
            'name' => $name,
            'profile_picture' => $profile_picture,
            'id' => $existing_user['id']
        ]);
        
        $user_id = $existing_user['id'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (google_id, email, name, profile_picture) VALUES (:google_id, :email, :name, :profile_picture)");
        $stmt->execute([
            'google_id' => $google_id,
            'email' => $email,
            'name' => $name,
            'profile_picture' => $profile_picture
        ]);
        
        $user_id = $pdo->lastInsertId();
    }
    
    // إنشاء جلسة المستخدم
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_picture'] = $profile_picture;
    
    echo json_encode([
        'success' => true,
        'message' => 'تم تسجيل الدخول بنجاح',
        'user_id' => $user_id
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'خطأ: ' . $e->getMessage()
    ]);
}
?>