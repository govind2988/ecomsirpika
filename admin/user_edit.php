<?php
session_start();
include './../includes/db.php';
$conn = getDbConnection();

if (!isset($_GET['id'])) {
    die("Missing user ID.");
}

$id = (int)$_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
  //  $phone = $conn->real_escape_string($_POST['phone']);

    $conn->query("UPDATE users SET name='$name', email='$email' WHERE id=$id");

    header("Location: users.php");
    exit;
}

$res = $conn->query("SELECT * FROM users WHERE id = $id LIMIT 1");
if ($res->num_rows === 0) {
    die("User not found.");
}

$user = $res->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit User</title>
  <link href="https://cdn.tailwindcss.com" rel="stylesheet" />
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-xl mx-auto bg-white shadow-md rounded p-6">
    <h2 class="text-2xl font-bold mb-4">Edit User</h2>
    <form method="POST">
      <div class="mb-4">
        <label class="block font-semibold">Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required class="w-full border px-3 py-2 rounded">
      </div>
      <div class="mb-4">
        <label class="block font-semibold">Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required class="w-full border px-3 py-2 rounded">
      </div>
	  <!--
      <div class="mb-4">
        <label class="block font-semibold">Phone:</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" class="w-full border px-3 py-2 rounded">
      </div>
	  -->
      <div class="flex justify-between">
        <a href="users.php" class="text-blue-500 hover:underline">← Back</a>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save Changes</button>
      </div>
    </form>
  </div>
</body>
</html>
