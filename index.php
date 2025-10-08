<?php
$page_title = "สินค้าทั้งหมด";
require_once __DIR__ . '/includes/header.php';

$stmt = $pdo->query("SELECT id, name, price, image_url, SUBSTRING(description,1,120) AS short_desc FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();
?>
<h1 class="page-title">สินค้าใหม่ล่าสุด</h1>

<section class="grid">
  <?php foreach ($products as $p): ?>
    <article class="card">
      <a href="product.php?id=<?= (int)$p['id'] ?>">
        <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
      </a>
      <div class="card-body">
        <h3 class="card-title">
          <a href="product.php?id=<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['name']) ?></a>
        </h3>
        <p class="muted"><?= htmlspecialchars($p['short_desc']) ?><?= strlen($p['short_desc'])>=120 ? '…' : '' ?></p>
        <div class="price-row">
          <strong class="price">฿<?= number_format($p['price'], 2) ?></strong>
          <form class="add-to-cart" method="post" action="cart.php?action=add">
            <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
            <input type="hidden" name="qty" value="1">
            <button type="submit" class="btn">ใส่ตะกร้า</button>
          </form>
        </div>
      </div>
    </article>
  <?php endforeach; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
