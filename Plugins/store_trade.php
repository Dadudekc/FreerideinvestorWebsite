<?php
// Load database connection
require 'db_connect.php';

// Get raw POST data
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id'])) {
    echo json_encode(["error" => "Invalid data received"]);
    exit;
}

// Prepare MySQL Insert
$sql = "INSERT INTO trades (id, symbol, qty, filled_qty, filled_avg_price, order_type, side, time_in_force, status, created_at, filled_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        filled_qty = VALUES(filled_qty), filled_avg_price = VALUES(filled_avg_price), status = VALUES(status), filled_at = VALUES(filled_at)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssiiissssss",
    $data['id'],
    $data['symbol'],
    $data['qty'],
    $data['filled_qty'],
    $data['filled_avg_price'],
    $data['order_type'],
    $data['side'],
    $data['time_in_force'],
    $data['status'],
    $data['created_at'],
    $data['filled_at']
);

if ($stmt->execute()) {
    echo json_encode(["success" => "Trade saved successfully!"]);
} else {
    echo json_encode(["error" => "Database error: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
