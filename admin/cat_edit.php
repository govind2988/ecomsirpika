<?php
include './../includes/db.php';

$conn = getDbConnection();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$pageTitle = $id > 0 ? "Edit Category" : "Add Category";
$success = $error = "";

$catData = [
    'name' => '',
    'description' => ''
];

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows) {
        $catData = $result->fetch_assoc();
    } else {
        $error = "Category not found.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name) {
        $exists = $conn->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
        $exists->bind_param("si", $name, $id);
        $exists->execute();
        $exists->store_result();

        if ($exists->num_rows > 0) {
            $error = "Category name already exists. Please choose a different name.";
        } else {
            if ($id > 0) {
                $stmt = $conn->prepare("UPDATE categories SET name=?, description=? WHERE id=?");
                $stmt->bind_param("ssi", $name, $description, $id);

                if ($stmt->execute()) {
                    $success = "Category updated successfully.";

                    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows) {
                        $catData = $result->fetch_assoc();
                    }
                } else {
                    $error = "Failed to update category.";
                }
            } else {
                $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
                $stmt->bind_param("ss", $name, $description);

                if ($stmt->execute()) {
                    $id = $conn->insert_id;
                    $success = "Category added successfully.";
                    $catData['name'] = $name;
                    $catData['description'] = $description;
                } else {
                    $error = "Failed to add category.";
                }
            }
        }
    } else {
        $error = "Please enter a category name.";
    }
}

include '_header.php';
?>

<main class="p-6 mt-16 space-y-4">
  <div class="wrapper max-w-4xl mx-auto">
    <div class="flex justify-between items-center">
      <h2 class="text-2xl font-bold"></h2>
      <div class="space-x-3">
        <a href="cat_list.php" class="bg-gray-500 text-white font-medium px-4 py-2 rounded">
          <i class="fa-solid fa-arrow-left"></i> Back
        </a>
      </div>
    </div>

    <section class="overflow-x-auto bg-white shadow rounded-lg p-4">
      <?php if ($success): ?>
        <p class="bg-green-100 text-green-700 p-3 mb-4 rounded"><?= htmlspecialchars($success ?? '') ?></p>
        <script>
          setTimeout(() => {
            window.location.href = "cat_list.php?newId=<?= $id ?>";
          }, 1500);
        </script>
      <?php elseif ($error): ?>
        <p class="bg-red-100 text-red-700 p-3 mb-4 rounded"><?= htmlspecialchars($error ?? '') ?></p>
      <?php endif; ?>

      <form method="POST">
        <label class="block font-semibold mb-1">Category Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($catData['name'] ?? '') ?>" class="w-full border px-3 py-2 rounded mb-4" required>

        <label class="block font-semibold mb-1">Description</label>
        <input type="text" name="description" value="<?= htmlspecialchars($catData['description'] ?? '') ?>" class="w-full border px-3 py-2 rounded mb-4">

        <div class="mt-6 row">
          <button type="submit" class="bg-primary font-semibold uppercase text-white px-4 py-2 rounded w-auto mr-4">
            <?= ($id > 0) ? 'Update Category' : 'Save Category' ?>
          </button>
          <button type="button" class="bg-gray-500 font-semibold uppercase text-white px-4 py-2 rounded w-auto" onclick="window.location.href='cat_list.php'">
            Cancel
          </button>
        </div>
      </form>
    </section>
  </div>
</main>

<?php include '_footer.php'; ?>