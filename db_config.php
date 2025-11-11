<?php
// db_config.php

$host = '127.0.0.1';
$dbname = 'learn_db';  // ←データベース名
$username = 'root';
$password = '';         // XAMPPでは初期設定で空

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("データベース接続失敗: " . $e->getMessage());
}
?>
