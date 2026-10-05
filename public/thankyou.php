<?php
include './../includes/db.php';
$conn = getDbConnection();

$order_id = (int)($_GET['order_id'] ?? 0);
$order = $conn->query("SELECT * FROM orders WHERE id = $order_id")->fetch_assoc();
$items = $conn->query("
    SELECT oi.quantity, oi.price, oi.is_bulk, p.name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = $order_id
");

$itemList = [];
$retailTotal = 0;
$hasBulk = false;
$hasRetail = false;

if ($items && $items->num_rows > 0) {
    while ($item = $items->fetch_assoc()) {
        $isBulk = !empty($item['is_bulk']);
        if ($isBulk) {
            $hasBulk = true;
        } else {
            $hasRetail = true;
            $retailTotal += ($item['price'] * $item['quantity']);
        }
        $itemList[] = $item;
    }
}

include '_header.php';
?>

<main class="container text-gray-800 mt-8">
  <div class="mx-auto px-4">

  <div class="max-w-2xl text-center mx-auto py-12 px-4">
    <i class="fa-regular fa-circle-check text-green-600 text-4xl"></i>
    <h1 class="text-3xl font-bold text-green-600 mt-4 mb-4">Thank you for your Order!</h1>
    <p>Our team will reach you shortly</p>
    
    <?php if ($order): ?>
	   <div class="bg-white rounded-lg shadow-lg p-6 mb-4 text-left">
        <h2 class="text-xl font-semibold mb-4 text-center">Order Summary</h2>
		
        <div class="flex justify-between py-2 border-b">
          <span class="font-medium">Order ID:</span>
          <span id="order-id">#<?= $order_id ?></span>
        </div>
        <div class="flex justify-between py-2 border-b">
          <span class="font-medium">Name:</span>
          <span id="order-name"><?= htmlspecialchars($order['customer_name']) ?></span>
        </div>
        <div class="flex justify-between py-2 border-b">
          <span class="font-medium">Phone:</span>
          <span id="order-phone"><?= htmlspecialchars($order['customer_phone']) ?></span>
        </div>
		<div class="flex justify-between py-2 border-b">
          <span class="font-medium">Address:</span>
          <span id="order-address"><?= nl2br(htmlspecialchars($order['customer_address'])) ?></span>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-lg p-6 mb-4 text-left">
        <h2 class="text-xl font-semibold mb-3">Order Items</h2>
        <ul class="mb-4 space-y-2">
          <?php foreach ($itemList as $item): ?>
            <?php $isBulk = !empty($item['is_bulk']); ?>
            <li class="flex items-center justify-between border-b pb-2">
              <div>
                <span class="font-medium"><?= htmlspecialchars($item['name']) ?></span> × <?= $item['quantity'] ?>
                <?php if ($isBulk): ?>
                  <span class="text-xs bg-green-100 text-green-800 px-2 py-0.5 rounded-full font-semibold ml-2">Bulk Order</span>
                <?php endif; ?>
              </div>
              <div>
                <?php if ($isBulk): ?>
                  <span class="text-gray-500 italic text-sm">Price on Request</span>
                <?php else: ?>
                  <span class="font-semibold">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                <?php endif; ?>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>

        <div class="text-right text-lg font-bold">
          <?php if ($hasRetail && $hasBulk): ?>
            Order Total: ₹<?= number_format($retailTotal, 2) ?> <span class="text-xs font-normal text-gray-500">(+ Bulk items on Request)</span>
          <?php elseif ($hasBulk && !$hasRetail): ?>
            Order Total: <span class="text-green-700">Price on Request (Bulk Inquiry)</span>
          <?php else: ?>
            Order Total: ₹<?= number_format($retailTotal, 2) ?>
          <?php endif; ?>
        </div>
      </div>
    <?php else: ?>
      <p class="text-red-600 font-semibold">Invalid Order.</p>
    <?php endif; ?>

    <div class="mt-6">
      <a href="index.php" class="bg-primary text-white px-5 py-2 rounded">Continue Shopping</a>
    </div>
  </div>

  </div>
</main>


<?php include '_footer.php'; ?>