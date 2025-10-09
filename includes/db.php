<?php
if (session_status() === PHP_SESSION_NONE) session_start();

/**
 * ลำดับอ่านค่าการเชื่อมต่อ:
 * 1) DATABASE_URL (postgres://user:pass@host:port/db)
 * 2) ชุด DB_* (DB_DRIVER, DB_HOST, DB_NAME, DB_USER, DB_PASS)
 */

$pdo = null;
$databaseUrl = getenv('DATABASE_URL');

if ($databaseUrl) {
  // แปลง postgres://... → pgsql:host=...;port=...;dbname=...
  $parts = parse_url($databaseUrl);
  // บางระบบใช้ 'postgresql' แทน 'postgres' — รองรับทั้งคู่
  if (!isset($parts['scheme']) || ($parts['scheme'] !== 'postgres' && $parts['scheme'] !== 'postgresql')) {
    die('Invalid DATABASE_URL scheme.');
  }
  $host = $parts['host'] ?? '127.0.0.1';
  $port = $parts['port'] ?? '5432';
  $db   = ltrim($parts['path'] ?? '', '/');
  $user = $parts['user'] ?? null;
  $pass = $parts['pass'] ?? null;

  $dsn = "pgsql:host={$host};port={$port};dbname={$db}";
  $pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);

} else {
  // fallback: ค่าจาก DB_* (ใช้ได้ทั้ง local/docker-compose)
  $driver = getenv('DB_DRIVER') ?: 'pgsql';
  $host   = getenv('DB_HOST')   ?: '127.0.0.1';
  $name   = getenv('DB_NAME')   ?: 'mini_shop';
  $user   = getenv('DB_USER')   ?: 'postgres';
  $pass   = getenv('DB_PASS')   ?: '1234';
  $port   = getenv('DB_PORT')   ?: ($driver === 'mysql' ? '3306' : '5432');

  if ($driver === 'pgsql') {
    $dsn = "pgsql:host={$host};port={$port};dbname={$name}";
  } else {
    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
  }

  $pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
}

function cart_count(): int {
  if (empty($_SESSION['cart'])) return 0;
  return array_sum(array_map('intval', $_SESSION['cart']));
}
