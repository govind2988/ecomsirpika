<?php
// info.php
session_start();
include './../includes/db.php';
$conn = getDbConnection();

// Get slug from URL (e.g., ?page=about)
$slug = $_GET['page'] ?? '';

// Prepare and execute query securely
$stmt = $conn->prepare("SELECT title, content FROM cms_pages WHERE slug = ?");
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Show 404 if not found
    http_response_code(404);
    echo "<h1>404 - Page Not Found</h1>";
    exit;
}

$page = $result->fetch_assoc();
include '_header.php';
?>


  <div class="innerBanner">
    <div class="container-fluid p-0">
      <img src="assets/images/InnerBanner.jpg" alt="Sirpika Millets" />
      <h2 class="title"><?= htmlspecialchars($page['title']) ?></h2>
    </div>
  </div>


<main class="container text-gray-800 mt-8">
  <div class="mx-auto px-4">
   <!-- <h1 class="text-4xl font-bold mt-4 mb-2"><?= htmlspecialchars($page['title']) ?></h1> -->


    <div class="text-gray-700">
      <?= $page['content'] ?>
    </div>
  </div>
</main>



<?php include '_footer.php'; ?>
