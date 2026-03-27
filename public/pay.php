<?php
session_start();
include './../includes/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['checkout_data'])) {
    header("Location: checkout.php");
    exit;
}

$conn = getDbConnection();
$user_id = $_SESSION['user_id'];

// Fetch cart total
$result = $conn->query("SELECT c.quantity, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = $user_id");

$total = 0;
while ($row = $result->fetch_assoc()) {
    $total += $row['price'] * $row['quantity'];
}
$amount_in_paise = $total * 100; // Razorpay requires amount in paise

$name = $_SESSION['checkout_data']['name'];
$phone = $_SESSION['checkout_data']['phone'];
$address = $_SESSION['checkout_data']['address'];

$key_id = "rzp_test_XXXXXXX"; // Replace with your Razorpay test key
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>
  <h2>Redirecting to Razorpay...</h2>

  <script>
    var options = {
        "key": "<?= $key_id ?>",
        "amount": "<?= $amount_in_paise ?>",
        "currency": "INR",
        "name": "Your Shop Name",
        "description": "Order Payment",
        "handler": function (response){
            // On success, go to payment_success.php
            window.location.href = "payment_success.php?payment_id=" + response.razorpay_payment_id;
        },
        "prefill": {
            "name": "<?= $name ?>",
            "contact": "<?= $phone ?>"
        },
        "theme": {
            "color": "#0f172a"
        }
    };
    var rzp1 = new Razorpay(options);
    rzp1.open();
  </script>
</body>
</html>
