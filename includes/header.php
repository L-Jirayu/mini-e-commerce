<?php
require_once __DIR__ . '/db.php';
$BASE = rtrim(dirname($_SERVER['PHP_SELF']), '/\\'); // ex: /PROJECT_F
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' · ' : '' ?>Mini Shop</title>
  <link rel="stylesheet" href="<?= $BASE ?>/assets/style.css?v=<?= time() ?>">
</head>
<body>
<header class="site-header">
  <div class="container header-inner">
    <a class="brand" href="<?= $BASE ?>/index.php">Mini<span>Shop</span></a>
    <nav class="nav">
      <a href="<?= $BASE ?>/index.php">สินค้า</a>
      <a href="<?= $BASE ?>/cart.php" class="cart-link">
        ตะกร้า <span id="cart-badge" class="badge"><?= cart_count(); ?></span>
      </a>
    </nav>
  </div>
</header>
<main class="container">
