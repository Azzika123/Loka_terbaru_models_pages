<?php
require __DIR__ . '/config.php';
header('Content-Type: text/plain; charset=utf-8');
try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);
    echo "Terhubung ke " . DB_NAME . "\n\n";
    foreach (['games', 'menu', 'pages', 'users', 'orders', 'bookings', 'login_history'] as $t) {
        try {
            $cols = $pdo->query("SHOW COLUMNS FROM `$t`")->fetchAll(PDO::FETCH_COLUMN);
            echo "$t: " . implode(', ', $cols) . "\n";
        } catch (PDOException $e) {
            echo "$t: TIDAK ADA - " . $e->getMessage() . "\n";
        }
    }
} catch (PDOException $e) {
    echo 'GAGAL: ' . $e->getMessage();
}