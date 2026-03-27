<?php
session_start();
include '../includes/db.php';
$conn = getDbConnection();

// 1. Sales per day (last 30 days) — use order_date, not created_at
$salesLabels = $salesData = [];
for ($i = 29; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $res = $conn->query("
        SELECT IFNULL(SUM(total), 0) AS sum
        FROM orders
        WHERE DATE(order_date) = '$date'
    ")->fetch_assoc();
    $salesLabels[] = $date;
    $salesData[]   = (float)$res['sum'];
}

// 2. New users per month (last 6 months) — uses users.created_at
$userLabels = $userData = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $res = $conn->query("SELECT COUNT(*) AS cnt
        FROM users
        WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'")->fetch_assoc();
    $userLabels[] = date('M Y', strtotime("$month-01"));
    $userData[]   = (int)$res['cnt'];
}

// 3. Order status breakdown
$statusLabels = $statusData = [];
$statusRes = $conn->query("
    SELECT status, COUNT(*) AS cnt
    FROM orders
    GROUP BY status
");
while ($row = $statusRes->fetch_assoc()) {
    $statusLabels[] = $row['status'];
    $statusData[]   = (int)$row['cnt'];
}
include '_header.php';
?>







<main class="p-6 mt-16 space-y-4">
   

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Reports & Analytics</h2>
       
    </div>

  

     <section class="grid md:grid-cols-2 gap-6">
       
            
             <!-- Sales Chart -->
    <div class="bg-white p-6 rounded shadow">
      <h2 class="text-xl font-semibold mb-4">Sales (Last 30 Days)</h2>
      <canvas id="salesChart"></canvas>
    </div>

    <!-- New Users Chart -->
    <div class="bg-white p-6 rounded shadow">
      <h2 class="text-xl font-semibold mb-4">New Users (Last 6 Months)</h2>
      <canvas id="userChart"></canvas>
    </div>

    <!-- Order Status Pie -->
    <div class="bg-white p-6 rounded shadow">
      <h2 class="text-xl font-semibold mb-4">Order Status Breakdown</h2>
      <canvas id="statusChart"></canvas>
    </div>

           
    </section>

</main>




  <script>
    new Chart(document.getElementById('salesChart'), {
      type: 'line',
      data: {
        labels: <?= json_encode($salesLabels) ?>,
        datasets: [{ label: 'Sales (₹)', data: <?= json_encode($salesData) ?>, fill: false, tension: 0.1 }]
      }
    });
    new Chart(document.getElementById('userChart'), {
      type: 'bar',
      data: {
        labels: <?= json_encode($userLabels) ?>,
        datasets: [{ label: 'New Users', data: <?= json_encode($userData) ?>, barPercentage: 0.6 }]
      }
    });
    new Chart(document.getElementById('statusChart'), {
      type: 'pie',
      data: {
        labels: <?= json_encode($statusLabels) ?>,
        datasets: [{ data: <?= json_encode($statusData) ?> }]
      }
    });
  </script>

<?php include '_footer.php'; ?>