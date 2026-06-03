<?php
require_once 'config.php';
require_once 'auth.php';

header('Content-Type: application/json');

$pdo = Config::getInstance()->getConnection();
$auth = new Auth($pdo);
$userId = $auth->validateSession();

if ($userId) {
    $user = $auth->getUser($userId);
    if ($user) {
        jsonResponse(true, [
            'user_id' => $user['id'],
            'username' => $user['username']
        ]);
    }
}

jsonResponse(false);
?>