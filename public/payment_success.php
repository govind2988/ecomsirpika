<?php
session_start();
include './../includes/db.php';

$conn = getDbConnection();
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Razorpay sends these POST parameters
    $razorpay_payment_id = $_POST['razorpay_payment_id'] ?? '';
    $razorpay_order_id = $_POST['razorpay_order_id'] ?? '';
    $razorpay_signature = $_POST['razorpay_signature'] ?? '';

    // Save payment record (optional)
    $stmt = $conn->prepare("INSERT INTO payments (user_id, payment_id, order_id, signature, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("isss", $user_id, $razorpay_payment_id, $razorpay_order_id, $razorpay_signature);
    $stmt->execute();

    // Get cart items and place order
    $result = $conn->query("SELECT * FROM cart WHERE user_id = $user_id");

    while ($row = $result->fetch_assoc()) {
        $pid = $row['product_id'];
        $qty = $row['quantity'];

        $conn->query("INSERT INTO orders (user_id, product_id, quantity, status, customer_name, customer_phone, customer_address, total)
                      SELECT $user_id, $pid, $qty, 'Paid', '', '', '', (price * $qty)
                      FROM products WHERE id = $pid");
    }

    // Clear cart
    $conn->query("DELETE FROM cart WHERE user_id = $user_id");

    $message = "✅ Payment successful and order placed!";
} else {
    $message = "⚠️ Invalid request.";
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Payment Success</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-green-50 text-gray-800">
  <div class="max-w-xl mx-auto px-4 py-12 text-center">
    <h1 class="text-3xl font-bold mb-4">🎉 Payment Success</h1>
    <p class="text-lg text-green-700 mb-6"><?= $message ?></p>

    <a href="index.php" class="inline-block bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">🏠 Back to Home</a>
  </div>
</body>
</html>
