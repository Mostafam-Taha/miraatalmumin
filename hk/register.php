<?php
require_once 'config.php';
require_once 'auth.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['username'], $data['email'], $data['password'])) {
    jsonResponse(false, null, 'Missing required fields');
}

$pdo = Config::getInstance()->getConnection();
$auth = new Auth($pdo);
$result = $auth->register(
    $data['username'],
    $data['email'],
    $data['password']
);

if ($result['success']) {
    jsonResponse(true, $result['data']);
} else {
    jsonResponse(false, null, $result['error']);
}
?>