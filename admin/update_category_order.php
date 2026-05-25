<?php

include './../includes/auth.php';
include './../includes/db.php';

$conn = getDbConnection();

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !is_array($data)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid data'
    ]);
    exit;
}

foreach ($data as $item) {

    $id = intval($item['id']);
    $position = intval($item['position']);

    $stmt = $conn->prepare("
        UPDATE categories 
        SET sort_order = ? 
        WHERE id = ?
    ");

    $stmt->bind_param("ii", $position, $id);
    $stmt->execute();
}

echo json_encode([
    'success' => true
]);