<?php
    include '../includes/db.php';
    $conn = getDbConnection();

    $orderId = intval($_GET['order_id']);

    $orderSql = "SELECT * FROM orders WHERE id = $orderId";
    $orderResult = $conn->query($orderSql);
    if (!$orderResult || $orderResult->num_rows === 0) {
        die("Invalid Order ID.");
    }
    $order = $orderResult->fetch_assoc();

    $itemsSql = "SELECT oi.quantity, oi.price, p.name AS product_name
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = $orderId";
    $itemsResult = $conn->query($itemsSql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Invoice - Order #<?= $order['id'] ?></title>
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
        }

        .logo {
            max-height: 50px;
        }

        .company-info {
            text-align: right;
            font-size: 14px;
            line-height: 1.5;
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
        .addessInfo{
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
            <img src="../assets/images/logo.png" alt="Company Logo" class="logo">
        </div>
        <div class="company-info">
            <strong>Your Company Name</strong><br>
            123 Business Street, City, State - ZIP<br>
            Phone: +91-9876543210
            Email: support@yourcompany.com
        </div>
    </div>

    <h1>Invoice - Order #<?= $order['id'] ?></h1>


    <div class="addessInfo">
       
    <div class="info">
        <div class="section-title">Customer & Order Details</div>
        <p><strong>Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($order['customer_phone']) ?></p>
        <p><strong>Order Date:</strong> <?= $order['order_date'] ?></p>
    </div>

    <div class="info">
        <div class="section-title">Delivery Address</div>
        <p><strong> <?= htmlspecialchars($order['customer_name']) ?></strong></p>
        <p><strong> <?= htmlspecialchars($order['customer_phone']) ?></strong></p>
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
                    <td><?= number_format($item['price'], 2) ?></td>
                    <td><?= $item['quantity'] ?></td>
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

    // Auto-click the button to simulate user trigger after slight delay
    setTimeout(() => {
        btn.click();
    }, 400);
});
</script>
</body>
</html>
