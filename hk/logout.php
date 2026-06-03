<?php
require_once 'config.php';
require_once 'auth.php';

header('Content-Type: application/json');

$pdo = Config::getInstance()->getConnection();
$auth = new Auth($pdo);
$auth->logout();

jsonResponse(true);
?>