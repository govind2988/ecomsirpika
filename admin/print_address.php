<?php
include '../includes/db.php';
$conn = getDbConnection();

$orderId = intval($_GET['order_id']);

$sql = "SELECT o.customer_name as name, o.customer_phone as phone, o.customer_address as address, o.id
        FROM orders o
        WHERE o.id = $orderId";
$result = $conn->query($sql);
if (!$result || $result->num_rows === 0) {
    die("Invalid Order ID.");
}
$row = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Print Shipping Address</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            color: #333;
        }

        .print-box {
            border: 1px solid #000;
            padding: 20px;
            max-width: 600px;
            margin: auto;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        #printBtn {
            display: block;
            margin: 20px auto 0;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        @media print {
            #printBtn {
                display: none;
            }

            @page {
                margin: 0;
            }

            body {
                margin: 1cm;
            }

            .print-box {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>
<body>
    <h2>Shipping Address for Order #<?= $row['id'] ?></h2>

    <div class="print-box">
        <p><strong>Name:</strong> <?= htmlspecialchars($row['name']) ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($row['phone']) ?></p>
        <p><strong>Address:</strong><br><?= nl2br(htmlspecialchars($row['address'])) ?></p>
    </div>

    <button id="printBtn">🖨️ Print & Close</button>

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

        // Auto click after slight delay
        setTimeout(() => {
            btn.click();
        }, 400);
    });
    </script>
</body>
</html>
