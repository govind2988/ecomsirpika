<?php
function getDbConnection() {
    $host = 'localhost';        // Replace with your actual DB host
    $user = 'root';     // Replace with your DB username
    $pass = ''; // Replace with your DB password
    $dbname = 'ecom_sirpika';   // Replace with your DB name

    $conn = new mysqli($host, $user, $pass, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}
?>
