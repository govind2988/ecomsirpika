<?php
include './../includes/auth.php';
include './../includes/db.php';
$conn = getDbConnection();

$success = "";
$error = "";
$pageData = [
    'page' => '',
    'title' => '',
    'content' => '',
    'image' => ''
];

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$title = $slug = $content = '';
$success = $error = '';
$pageTitle = "New";

if ($id > 0) {
  $pageTitle = "Edit";
  $stmt = $conn->prepare("SELECT * FROM cms_pages WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows) {
        $pageData = $result->fetch_assoc();
  }
  else {
    $error = "Page not found.";
  }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $page = trim($_POST['page']);
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
	
    // Handle image upload
    $imagePath = $pageData['image'] ?? null;
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = './../uploads/cms/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $imageName = basename($_FILES['image']['name']);
        $targetFile = $uploadDir . $imageName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $imagePath = 'uploads/cms/' . $imageName;
        } else {
            $error = "Image upload failed.";
        }
    }

    if ($page && $title && $content) {
        $stmt = $conn->prepare("SELECT id FROM cms_pages WHERE slug = ?");
        $stmt->bind_param("s", $page);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update existing
            if ($imagePath) {
                $stmt = $conn->prepare("UPDATE cms_pages SET title = ?, content = ?, image = ? WHERE slug = ?");
                $stmt->bind_param("ssss", $title, $content, $imagePath, $page);
            } else {
                $stmt = $conn->prepare("UPDATE cms_pages SET title = ?, content = ? WHERE slug = ?");
                $stmt->bind_param("sss", $title, $content, $page);
            }
            $stmt->execute();
            $success = "Page updated successfully.";
        } else {
            // Insert new
            $stmt = $conn->prepare("INSERT INTO cms_pages (slug, title, content, image) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $page, $title, $content, $imagePath);
            $stmt->execute();
            $success = "Page created successfully.";
        }

        // Refresh page data
        $pageData = [
            'slug' => $page,
            'title' => $title,
            'content' => $content,
            'image' => $imagePath
        ];
    } else {
        $error = "Please fill in all fields.";
    }
}


include '_header.php';


?>



<main class="p-6 mt-16 space-y-4">
    

      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold"><?=$pageTitle?> CMS Pages</h2>
        <div class="space-x-3">
            <a href="cms_list.php" class="bg-gray-600 text-white px-3 py-1 rounded view-btn">Back</a>
        </div>
    </div>


    <section class="overflow-x-auto bg-white shadow rounded-lg p-4">
    
     <?php if ($success): ?>
            <p class="bg-green-100 text-green-700 p-3 mb-4 rounded"><?= htmlspecialchars($success) ?></p>
        <?php elseif ($error): ?>
            <p class="bg-red-100 text-red-700 p-3 mb-4 rounded"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label class="block mb-2 font-semibold">Page Slug (e.g., about, contact)</label>
			<input type="text" name="page" class="w-full p-2 border rounded mb-4" value="<?= htmlspecialchars($pageData['slug'] ?? '') ?>"required <?= isset($_GET['id']) ? 'readonly="readonly"' : '' ?>>


            <label class="block mb-2 font-semibold">Title</label>
            <input type="text" name="title" class="w-full p-2 border rounded mb-4" value="<?= htmlspecialchars($pageData['title']) ?>" required>

            <label class="block mb-2 font-semibold">Content</label>
			
          	<textarea name="content" id="editor"><?php echo htmlspecialchars_decode($pageData['content'], ENT_QUOTES); ?></textarea>
			
			<!-- Correct script loading -->
			<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script> 
			<script>
			  CKEDITOR.replace('editor');
			</script>

            <label class="block mb-2 font-semibold">Image</label>
            <?php if (!empty($pageData['image'])): ?>
                <div class="mb-2">
                    <img src="../<?= htmlspecialchars($pageData['image']) ?>" alt="Page Image" class="w-32 h-32 object-cover rounded border mb-2">
                </div>
            <?php endif; ?>
            <input type="file" name="image" class="mb-4">

            <button type="submit" class="bg-primary text-white px-4 py-2 rounded"><?= ($id > 0) ? 'Update' : 'Save' ?> Page</button>
        </form>

    </section>
</main>


<?php include '_footer.php'; ?>

