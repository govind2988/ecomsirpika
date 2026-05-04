<?php
include '../includes/db.php';
$conn = getDbConnection();

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if ($orderId <= 0) {
    die("Invalid Order ID.");
}

/*
|--------------------------------------------------------------------------
| Fetch settings row
| Assumption:
| - settings table has a single row OR you want the latest row
| - column names may be like:
|   company_name, company_address, company_phone, company_email, company_logo
|--------------------------------------------------------------------------
*/
$settings = [
    'company_name'    => 'Your Company Name',
    'company_address' => '123 Business Street, City, State - ZIP',
    'company_phone'   => '+91-9876543210',
    'company_email'   => 'support@yourcompany.com',
    'company_logo'    => '../assets/images/logo.png'
];

$settingsSql = "SELECT * FROM settings ORDER BY id DESC LIMIT 1";
$settingsResult = $conn->query($settingsSql);

if ($settingsResult && $settingsResult->num_rows > 0) {
    $settingsRow = $settingsResult->fetch_assoc();

    $settings['company_name']    = !empty($settingsRow['company_name']) ? $settingsRow['company_name'] : $settings['company_name'];
    $settings['company_address'] = !empty($settingsRow['address']) ? $settingsRow['address'] : $settings['company_address'];
    $settings['company_phone']   = !empty($settingsRow['contact_no']) ? $settingsRow['contact_no'] : $settings['company_phone'];
    $settings['company_email']   = !empty($settingsRow['contact_email']) ? $settingsRow['contact_email'] : $settings['company_email'];

    // Adjust this if your DB column is named logo instead of company_logo
    if (!empty($settingsRow['logo'])) {
        $settings['company_logo'] = '../uploads/' . ltrim($settingsRow['logo'], '/');
    } 
}

/*
|--------------------------------------------------------------------------
| Fetch order using prepared statement
|--------------------------------------------------------------------------
*/
$orderStmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$orderStmt->bind_param("i", $orderId);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();

if (!$orderResult || $orderResult->num_rows === 0) {
    die("Invalid Order ID.");
}
$order = $orderResult->fetch_assoc();

/*
|--------------------------------------------------------------------------
| Fetch order items using prepared statement
|--------------------------------------------------------------------------
*/
$itemsStmt = $conn->prepare("
    SELECT oi.quantity, oi.price, p.name AS product_name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$itemsStmt->bind_param("i", $orderId);
$itemsStmt->execute();
$itemsResult = $itemsStmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice - Order #<?= (int)$order['id'] ?></title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            padding: 40px;
            color: #333;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 10px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0,0,0,.15);
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .logo {
            max-height: 60px;
            max-width: 180px;
            object-fit: contain;
        }

        .company-info {
            text-align: right;
            font-size: 14px;
            line-height: 1.6;
        }

        h1 {
            text-align: center;
            font-size: 26px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #000;
        }

        .section-title {
            font-size: 16px;
            margin-bottom: 10px;
            padding-bottom: 4px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .addessInfo {
            display: flex;
            justify-content: space-between;
            align-items: start;
            gap: 1rem;
            margin-top: 1rem;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .addessInfo .info {
            flex-basis: 45%;
            box-sizing: border-box;
        }

        .info p {
            margin: 4px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table thead {
            background: #f5f5f5;
        }

        table th, table td {
            border: 1px solid #ccc;
            padding: 10px;
            font-size: 14px;
            text-align: center;
        }

        table tfoot td {
            font-weight: bold;
            background: #f9f9f9;
        }

        #printBtn {
            display: block;
            margin: 30px auto 0;
            padding: 10px 20px;
            background-color: #ff5859;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
            border-radius: 6px;
        }

        @media print {
            #printBtn {
                display: none;
            }

            @page {
                margin: 0;
            }

            body {
                margin: 0;
                padding: 20px;
            }

            .invoice-box {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>
<body>
<div class="invoice-box">
    <div class="header">
        <div>
            <img src="<?= htmlspecialchars($settings['company_logo']) ?>" alt="Company Logo" class="logo">
        </div>
        <div class="company-info">
            <strong><?= htmlspecialchars($settings['company_name']) ?></strong><br>
            <?= nl2br(htmlspecialchars($settings['company_address'])) ?><br>
            Phone: <?= htmlspecialchars($settings['company_phone']) ?><br>
            Email:
            <a href="mailto:<?= htmlspecialchars($settings['company_email']) ?>">
                <?= htmlspecialchars($settings['company_email']) ?>
            </a>
        </div>
    </div>

    <h1>Invoice - Order #<?= (int)$order['id'] ?></h1>

    <div class="addessInfo">
        <div class="info">
            <div class="section-title">Customer & Order Details</div>
            <p><strong>Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($order['customer_phone']) ?></p>
            <p><strong>Order Date:</strong> <?= htmlspecialchars($order['order_date']) ?></p>
        </div>

        <div class="info">
            <div class="section-title">Delivery Address</div>
            <p><strong><?= htmlspecialchars($order['customer_name']) ?></strong></p>
            <p><strong><?= htmlspecialchars($order['customer_phone']) ?></strong></p>
            <p><strong><?= nl2br(htmlspecialchars($order['customer_address'])) ?></strong></p>
        </div>
    </div>

    <div class="info">
        <div class="section-title">Order Summary</div>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Unit Price (₹)</th>
                    <th>Quantity</th>
                    <th>Subtotal (₹)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $grandTotal = 0;
                while ($item = $itemsResult->fetch_assoc()):
                    $subtotal = $item['quantity'] * $item['price'];
                    $grandTotal += $subtotal;
                ?>
                <tr>
                    <td style="text-align: left;"><?= htmlspecialchars($item['product_name']) ?></td>
                    <td><?= number_format((float)$item['price'], 2) ?></td>
                    <td><?= (int)$item['quantity'] ?></td>
                    <td><?= number_format($subtotal, 2) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align:right">Grand Total:</td>
                    <td>₹<?= number_format($grandTotal, 2) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Print & Close Button -->
<button id="printBtn">Print & Close</button>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const btn = document.getElementById("printBtn");

    btn.addEventListener("click", function () {
        window.print();

        setTimeout(function () {
            if (window.opener) {
                window.opener.location.href = 'orders.php';
            }
            window.close();
        }, 300);
    });

    setTimeout(() => {
        btn.click();
    }, 400);
});
</script>
</body>
</html>