<?php
require './../vendor/autoload.php';
include './../includes/db.php';

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

$secret = 'your_webhook_secret_here'; // Set this when configuring webhook in Razorpay

// Get payload and headers
$payload = file_get_contents('php://input');
$headers = getallheaders();
$signature = $headers['X-Razorpay-Signature'] ?? '';

try {
    \Razorpay\Api\Webhook::verify($payload, $signature, $secret);
    $data = json_decode($payload, true);

    // Check event type
    if ($data['event'] === 'payment.captured') {
        $payment_id = $data['payload']['payment']['entity']['id'];
        $amount = $data['payload']['payment']['entity']['amount'] / 100; // convert from paise
        $email = $data['payload']['payment']['entity']['email'] ?? '';
        $contact = $data['payload']['payment']['entity']['contact'] ?? '';

        // Log or update database as needed
        file_put_contents("razorpay_logs.txt", date("Y-m-d H:i:s") . " - Payment Captured: $payment_id for Rs. $amount\n", FILE_APPEND);
        
        // You can save this payment ID to a table to mark it "paid" if not already processed
    }

    http_response_code(200);
    echo "Webhook processed";
} catch (SignatureVerificationError $e) {
    http_response_code(400);
    echo "Signature mismatch: " . $e->getMessage();
}
