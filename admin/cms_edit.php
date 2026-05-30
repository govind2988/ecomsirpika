<?php
include './../includes/auth.php';
include './../includes/db.php';

$conn = getDbConnection();

$success = '';
$error = '';
$pageTitle = 'New';

$pageData = [
    'id' => 0,
    'slug' => '',
    'title' => '',
    'content' => '',
    'image' => ''
];

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id > 0) {
    $pageTitle = 'Edit';

    $stmt = $conn->prepare("SELECT id, slug, title, content, image FROM cms_pages WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $pageData = $result->fetch_assoc();
    } else {
        $error = 'Page not found.';
        $id = 0;
    }

    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedId = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $page = trim($_POST['page'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    $imagePath = $pageData['image'] ?? '';

    if ($page === '' || $title === '' || $content === '') {
        $error = 'Please fill in all fields.';
    }

    if ($error === '' && !empty($_FILES['image']['name'])) {
        $uploadDir = dirname(__DIR__) . '/uploads/cms/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $originalName = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $allowed, true)) {
            $error = 'Invalid image type. Allowed: jpg, jpeg, png, gif, webp.';
        } elseif (!is_uploaded_file($_FILES['image']['tmp_name'])) {
            $error = 'Invalid image upload.';
        } else {
            $imageName = uniqid('cms_', true) . '.' . $ext;
            $targetFile = $uploadDir . $imageName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $imagePath = 'uploads/cms/' . $imageName;
            } else {
                $error = 'Image upload failed.';
            }
        }
    }

    if ($error === '') {
        $checkSql = "SELECT id FROM cms_pages WHERE slug = ? AND id != ?";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param("si", $page, $postedId);
        $stmt->execute();
        $result = $stmt->get_result();
        $slugExists = $result->num_rows > 0;
        $stmt->close();

        if ($slugExists) {
            $error = 'Page slug already exists.';
        } else {
            if ($postedId > 0) {
                $sql = "UPDATE cms_pages SET slug = ?, title = ?, content = ?, image = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssi", $page, $title, $content, $imagePath, $postedId);
                $stmt->execute();

                if ($stmt->affected_rows >= 0) {
                    $success = 'Page updated successfully.';
                    $id = $postedId;
                    $pageTitle = 'Edit';
                } else {
                    $error = 'Failed to update page.';
                }
                $stmt->close();
            } else {
                $sql = "INSERT INTO cms_pages (slug, title, content, image) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssss", $page, $title, $content, $imagePath);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    $id = $stmt->insert_id;
                    $success = 'Page created successfully.';
                    $pageTitle = 'Edit';
                } else {
                    $error = 'Failed to create page.';
                }
                $stmt->close();
            }

            $pageData = [
                'id' => $id,
                'slug' => $page,
                'title' => $title,
                'content' => $content,
                'image' => $imagePath
            ];
        }
    } else {
        $pageData['slug'] = $page;
        $pageData['title'] = $title;
        $pageData['content'] = $content;
        $pageData['image'] = $imagePath;
    }
}

include '_header.php';
?>

<main class="p-6 mt-16 space-y-4">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold"><?= htmlspecialchars($pageTitle) ?> CMS Page</h2>
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
            <input type="hidden" name="id" value="<?= (int)($pageData['id'] ?? 0) ?>">

            <label class="block mb-2 font-semibold">Page Slug (e.g., about, contact)</label>
            <input
                type="text"
                name="page"
                class="w-full p-2 border rounded mb-4"
                value="<?= htmlspecialchars($pageData['slug'] ?? '') ?>"
                required
            >

            <label class="block mb-2 font-semibold">Title</label>
            <input
                type="text"
                name="title"
                class="w-full p-2 border rounded mb-4"
                value="<?= htmlspecialchars($pageData['title'] ?? '') ?>"
                required
            >

            <label class="block mb-2 font-semibold">Content</label>
            <textarea name="content" id="editor"><?= htmlspecialchars($pageData['content'] ?? '') ?></textarea>

            <label class="block mb-2 font-semibold mt-4">Image</label>
            <?php if (!empty($pageData['image'])): ?>
                <div class="mb-2">
                    <img src="../<?= htmlspecialchars($pageData['image']) ?>" alt="Page Image" class="w-32 h-32 object-cover rounded border mb-2">
                </div>
            <?php endif; ?>
            <input type="file" name="image" class="mb-4" accept=".jpg,.jpeg,.png,.gif,.webp">

            <button type="submit" class="bg-primary text-white px-4 py-2 rounded">
                <?= ($id > 0) ? 'Update' : 'Save' ?> Page
            </button>
        </form>
    </section>
</main>

<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
CKEDITOR.on('dialogDefinition', function (ev) {
    if (ev.data.name === 'image') {
        var dialogDefinition = ev.data.definition;
        var oldOnShow = dialogDefinition.onShow;

        dialogDefinition.onShow = function () {
            if (oldOnShow) {
                oldOnShow.apply(this, arguments);
            }

            var previewField = this.getContentElement('info', 'htmlPreview');
            if (previewField) {
                var el = previewField.getElement();
                if (el) {
                    el.hide();
                }
            }
        };
    }
});

CKEDITOR.replace('editor', {
    allowedContent: true,
    extraAllowedContent: 'div(*)[*]{*}; header(*)[*]{*}; a(*)[*]{*}; img(*)[*]{*}; span(*)[*]{*}',
    pasteFilter: null
});
</script>

<?php include '_footer.php'; ?>