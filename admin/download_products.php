<?php
require '../vendor/autoload.php'; // Adjust path to autoload

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

include '../includes/db.php';
$conn = getDbConnection();

// 1. Fetch product data with category name
$sql = "
    SELECT 
        p.id, 
        p.name, 
        p.description, 
        c.name AS category,
        p.rrp_price AS MRP,
        p.sale_price AS Offer_Price,
        p.stock
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
";
$result = $conn->query($sql);

// 2. Create Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// 3. Set header row
$headers = ['ID', 'Name', 'Description', 'Category', 'MRP', 'Offer_Price', 'Stock'];
$sheet->fromArray($headers, null, 'A1');

// 4. Add data rows
$rowNum = 2;
while ($row = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $rowNum, $row['id']);
    $sheet->setCellValue('B' . $rowNum, $row['name']);
    $sheet->setCellValue('C' . $rowNum, $row['description']);
    $sheet->setCellValue('D' . $rowNum, $row['category']);
    $sheet->setCellValue('E' . $rowNum, $row['MRP']);
    $sheet->setCellValue('F' . $rowNum, $row['Offer_Price']);
    $sheet->setCellValue('G' . $rowNum, $row['stock']);
    $rowNum++;
}

// 5. Output file to browser
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="product_list.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
