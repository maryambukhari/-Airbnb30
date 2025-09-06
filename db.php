<?php
$dsn = 'mysql:host=localhost;dbname=dbzdqnfkgpm781';
$user = 'uxhc7qjwxxfub';
$pass = 'g4t0vezqttq6';

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}
?>
