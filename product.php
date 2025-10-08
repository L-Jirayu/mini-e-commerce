<?php
$page_title = "สินค้า";
require_once __DIR__ . '/includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
  echo '<h1 class="page-title">ไม่พบสินค้า</h1>';
  require_once __DIR__ . '/includes/footer.php';
  exit;
}
?>
<article class="product-detail">
  <div class="detail-media">
    <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
  </div>
  <div class="detail-info">
    <h1><?= htmlspecialchars($product['name']) ?></h1>
    <p class="muted"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
    <div class="detail-buy">
      <strong class="price">฿<?= number_format($product['price'], 2) ?></strong>
      <form class="add-to-cart" method="post" action="cart.php?action=add">
        <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
        <label>จำนวน:
          <input type="number" name="qty" min="1" value="1">
        </label>
        <button type="submit" class="btn">ใส่ตะกร้า</button>
      </form>
    </div>
  </div>
</article>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
