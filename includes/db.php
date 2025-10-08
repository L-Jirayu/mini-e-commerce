<?php
if (session_status() === PHP_SESSION_NONE) session_start();

/* เลือกไดรเวอร์จาก ENV: 'pgsql' หรือ 'mysql' */
$driver = getenv('DB_DRIVER') ?: 'pgsql';              // ใช้ 'pgsql' เป็นค่าเริ่มต้นถ้ารันใน Docker
$host   = getenv('DB_HOST')   ?: '127.0.0.1';
$name   = getenv('DB_NAME')   ?: 'mini_shop';
$user   = getenv('DB_USER')   ?: 'postgres';
$pass   = getenv('DB_PASS')   ?: '1234';

if ($driver === 'pgsql') {
  $dsn = "pgsql:host={$host};dbname={$name}";
} else {
  $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
}

try {
  $pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
} catch (PDOException $e) {
  die('DB connect error: ' . htmlspecialchars($e->getMessage()));
}

function cart_count(): int {
  if (empty($_SESSION['cart'])) return 0;
  return array_sum(array_map('intval', $_SESSION['cart']));
}
