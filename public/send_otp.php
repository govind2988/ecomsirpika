<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'];
    $otp = rand(100000, 999999);

    session_start();
    $_SESSION['checkout_otp'] = $otp;

    $apiKey = 'jiHVs8TLD2fc6MpUwRnPJhQuCoaYxN1ye0gSr4qmbkI37GvZWEbYqW4ULxcwkT5tP1pHyeK8zdB0lOaD';
    $fields = [
        "sender_id" => "FSTSMS",
        "message" => "Your OTP is $otp",
        "language" => "english",
        "route" => "p",
        "numbers" => $phone,
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://www.fast2sms.com/dev/bulkV2",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($fields),
        CURLOPT_HTTPHEADER => [
            "authorization: $apiKey",
            "Content-Type: application/json"
        ],
    ]);

    $response = curl_exec($curl);
    curl_close($curl);
    echo 'OTP sent';
}
?>
