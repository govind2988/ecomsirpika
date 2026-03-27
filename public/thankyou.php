<?php
include './../includes/db.php';
$conn = getDbConnection();

$order_id = $_GET['order_id'] ?? 0;
$order = $conn->query("SELECT * FROM orders WHERE id = $order_id")->fetch_assoc();
$items = $conn->query("
    SELECT oi.quantity, oi.price, p.name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = $order_id
");
$shipping_charges = 50;

include '_header.php';

?>

<main class="container text-gray-800 mt-8">
  <div class="mx-auto px-4">


  <div class="max-w-2xl text-center mx-auto py-12 px-4">
    <i class="fa-regular fa-circle-check text-green-600 text-4xl"></i>
    <h1 class="text-3xl font-bold text-green-600 mt-4 mb-4">Thank you for your Order!</h1>
    <p>Our team will reach you shortly</p>
    

    <?php if ($order): ?>
	   <div class="bg-white rounded-lg shadow-lg p-6 mb-4">
        <h2 class="text-xl font-semibold mb-4">Order Summary</h2>
        <?php
  //        $itemCount = array_sum(array_column($cartItems, 'quantity'));
  //        $shipping = $total > 0 ? 50 : 0;
  //        $orderTotal = $total + $shipping;
        ?>
		
        <div class="flex justify-between py-2 border-b">
          <span class="font-medium">Order ID:</span>
          <span id="order-id"><?= $order_id ?></span>
        </div>
        <div class="flex justify-between py-2 border-b">
          <span class="font-medium">Name:</span>
          <span>₹<span id="order-name"><?= htmlspecialchars($order['customer_name']) ?></span></span>
        </div>
        <div class="flex justify-between py-2 border-b">
          <span class="font-medium">Phone:</span>
          <span>₹<span id="order-phone"><?= htmlspecialchars($order['customer_phone']) ?></span></span>
        </div>
		<div class="flex justify-between py-2 border-b">
          <span class="font-medium">Address:</span>
          <span>₹<span id="order-address"><?= nl2br(htmlspecialchars($order['customer_address'])) ?></span></span>
        </div>
      </div>
  <!--    <p class="mb-2"><strong>Order ID:</strong> #<?= $order_id ?></p>
      <p class="mb-2"><strong>Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
      <p class="mb-2"><strong>Phone:</strong> <?= htmlspecialchars($order['customer_phone']) ?></p>
      <p class="mb-4"><strong>Address:</strong> <?= nl2br(htmlspecialchars($order['customer_address'])) ?></p>
   -->
      <h2 class="text-xl font-semibold mb-2">Order Summary</h2>
      <ul class="mb-4 space-y-1">
        <?php while ($item = $items->fetch_assoc()): ?>
          <li>- <?= htmlspecialchars($item['name']) ?> × <?= $item['quantity'] ?> = ₹<?= $item['price'] * $item['quantity'] ?></li>
        <?php endwhile; ?>
      </ul>
	   <p class="text-lg font-semibold">Shipping: ₹<?= $shipping_charges ?></p>
      <p class="text-lg font-semibold">Order Total: ₹<?= $order['total'] + $shipping_charges ?></p>
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