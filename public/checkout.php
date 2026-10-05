<?php
session_start();
include './../includes/db.php';

$conn = getDbConnection();
$mode = $_GET['mode'] ?? 'traditional';
$message = '';

// Load cart from session
$cart = $_SESSION['cart'] ?? [];

if (isset($_POST['place_order'])) {
    $captcha_input = strtoupper(trim($_POST['captcha_input'] ?? ''));
    $captcha_code = $_SESSION['captcha'] ?? '';

    if ($captcha_input !== $captcha_code) {
        $message = "❌ Invalid CAPTCHA. Please try again.";
    } elseif (empty($cart)) {
        $message = "🛒 Cart is empty!";
    } else {
        $name = $conn->real_escape_string($_POST['name']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $email = $conn->real_escape_string($_POST['email']);
        $address = $conn->real_escape_string($_POST['address']);

        // Fetch product details for all cart items
        $product_ids = array_keys($cart);
        $placeholders = implode(',', array_map('intval', $product_ids));
        $query = "SELECT id, name, rrp_price, sale_price FROM products WHERE id IN ($placeholders)";
        $result = $conn->query($query);

        $total = 0;
        $order_items = [];
        $hasAnyBulk = false;

        while ($row = $result->fetch_assoc()) {
            $pid = (int)$row['id'];
            $qty = (int)($cart[$pid] ?? 0);
            if ($qty <= 0) continue;

            $price = ($row['sale_price'] ?? 0) > 0 ? $row['sale_price'] : $row['rrp_price'];
            $isBulk = !empty($_SESSION['bulk_items'][$pid]);
            if ($isBulk) {
                $hasAnyBulk = true;
                $order_items[] = [
                    'product_id' => $pid,
                    'product_name' => $conn->real_escape_string($row['name']),
                    'price' => 0.00,
                    'quantity' => $qty,
                    'is_bulk' => 1
                ];
            } else {
                $subtotal = $price * $qty;
                $total += $subtotal;
                $order_items[] = [
                    'product_id' => $pid,
                    'product_name' => $conn->real_escape_string($row['name']),
                    'price' => $price,
                    'quantity' => $qty,
                    'is_bulk' => 0
                ];
            }
        }

        // Insert into orders table
        $conn->query("INSERT INTO orders (status, customer_name, customer_phone, customer_email, customer_address, total)
                      VALUES ('New Order', '$name', '$phone', '$email', '$address', $total)");
        $order_id = $conn->insert_id;

        // Insert order items
        foreach ($order_items as $item) {
            $pid = $item['product_id'];
            $qty = $item['quantity'];
            $price = $item['price'];
            $is_bulk = $item['is_bulk'];

            $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price, is_bulk)
                          VALUES ($order_id, $pid, $qty, $price, $is_bulk)");
        }

        // Clear cart, bulk and CAPTCHA
        unset($_SESSION['cart'], $_SESSION['bulk_items'], $_SESSION['captcha']);
        $_SESSION['cart'] = [];
        $_SESSION['bulk_items'] = [];

        if ($mode === 'whatsapp') {
            $whatsapp_message = "*Order Items:*\n";
            $allBulk = true;
            $calculatedTotal = 0;
            foreach ($order_items as $item) {
                if (!empty($item['is_bulk'])) {
                    $whatsapp_message .= "• {$item['product_name']} (x{$item['quantity']}) - Bulk Order (Price on Request)\n";
                } else {
                    $allBulk = false;
                    $lineTotal = $item['price'] * $item['quantity'];
                    $calculatedTotal += $lineTotal;
                    $whatsapp_message .= "• {$item['product_name']} (x{$item['quantity']}) - ₹{$lineTotal}\n";
                }
            }

            if ($allBulk) {
                $whatsapp_message .= "\nTotal: Price on Request (Bulk Inquiry)";
            } else {
                $whatsapp_message .= "\nTotal: Rs {$calculatedTotal}" . ($hasAnyBulk ? " (+ Bulk items on Request)" : "");
            }

            $encoded = urlencode("👋 Hi, I have placed an order.\n\n$name\n$phone\n$address\n\n$whatsapp_message");
            $whatsapp_number = WHATSAPP_NO;
            header("Location: https://wa.me/$whatsapp_number?text=$encoded");
            exit;
        } else {

            $message = "✅ Order placed successfully!";
        }
    }
}

include '_header.php';
?>
<!DOCTYPE html>
<html>
<head>
  <title>Checkout</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <base href="<?= htmlspecialchars(BASE_URL) ?>">
  <script src="./assets/js/captcha.js"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <div class="max-w-xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-4 text-center">🧾 Checkout</h1>

    <?php if ($message): ?>
      <p class="bg-yellow-100 border border-yellow-300 text-yellow-800 p-3 rounded mb-4">
        <?= $message ?>
      </p>
    <?php endif; ?>

    <form method="post" class="bg-white p-6 rounded shadow">
      <input type="hidden" name="mode" value="<?= htmlspecialchars($mode) ?>">

      <div class="mb-4">
        <label>Name:</label>
        <input type="text" name="name" required class="w-full border p-2 rounded" />
      </div>
      <div class="mb-4">
        <label>Phone:</label>
        <input type="text" name="phone" required class="w-full border p-2 rounded" />
      </div>
      <div class="mb-4">
        <label>Email:</label>
        <input type="email" name="email" required class="w-full border p-2 rounded" />
      </div>
      <div class="mb-4">
        <label>Address:</label>
        <textarea name="address" required class="w-full border p-2 rounded"></textarea>
      </div>

      <!-- CAPTCHA -->
      <div class="mb-4">
        <label>CAPTCHA:</label>
        <div class="flex items-center space-x-4">
          <div id="captchaText" class="font-mono text-lg bg-gray-200 px-4 py-2 rounded select-none"></div>
          <button type="button" onclick="generateCaptcha()" class="text-sm text-blue-600 hover:underline">↻ Refresh</button>
        </div>
        <input type="text" name="captcha_input" id="captcha_input" placeholder="Enter above text" required class="mt-2 w-full border p-2 rounded uppercase tracking-widest" />
      </div>

      <button name="place_order" class="bg-green-600 text-white px-4 py-2 rounded w-full mt-2">
        ✅ Confirm & Place Order
      </button>
    </form>

    <div class="mt-6 text-center">
      <a href="cart.php" class="text-blue-600 hover:underline">⬅ Back to Cart</a>
    </div>
  </div>
</body>
</html>
<?php include '_footer.php'; ?>
