<?php
require 'db_connect.php';

$sql = "SELECT * FROM trades ORDER BY created_at DESC";
$result = $conn->query($sql);

$trades = [];

while ($row = $result->fetch_assoc()) {
    $trades[] = $row;
}

echo json_encode($trades);

$conn->close();
?>
