<?php
session_start();
include './../includes/db.php';
$conn = getDbConnection();

if ($_POST['otp'] != $_SESSION['checkout_otp']) {
    die("Invalid OTP");
}

$name = $_POST['name'];
$phone = $_POST['phone'];
$address = $_POST['address'];
$user_id = $_SESSION['user_id'] ?? 1;

// Fetch cart
$cart = $conn->query("SELECT c.product_id, c.quantity, p.price, p.name
                      FROM cart c JOIN products p ON c.product_id = p.id 
                      WHERE c.user_id = $user_id");

$total = 0;
$message = "Order placed:\n";

while ($item = $cart->fetch_assoc()) {
    $pid = $item['product_id'];
    $qty = $item['quantity'];
    $price = $item['price'];
    $lineTotal = $price * $qty;
    $total += $lineTotal;

    $conn->query("INSERT INTO orders (user_id, product_id, quantity, status, customer_name, customer_phone, customer_address, total)
                  VALUES ($user_id, $pid, $qty, 'Processing', '$name', '$phone', '$address', $lineTotal)");

    $message .= "- {$item['name']} × {$qty} = ₹{$lineTotal}\n";
}

$conn->query("DELETE FROM cart WHERE user_id = $user_id");
$message .= "Total = ₹$total\nName: $name\nPhone: $phone\nAddress: $address";

// WhatsApp redirect
$waUrl = "https://wa.me/91XXXXXXXXXX?text=" . urlencode($message);
header("Location: $waUrl");
exit;
?>
