<?php
require_once __DIR__ . '/../admin/require_login.php';
include('./connection.php');

header('Content-Type: application/json');

$startDate = $_GET['start'];
$endDate = $_GET['end'];

// Consulta para obtener las ventas diarias
$query = "SELECT DATE(order_date) AS day, COUNT(*) AS total_sales
          FROM orders
          WHERE order_date BETWEEN ? AND  ?
          GROUP BY DATE(order_date)
          ORDER BY DATE(order_date)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $startDate, $endDate); // "ss" indica dos strings
$stmt->execute();
$result = $stmt->get_result();

$data = [];
$data[] = ['Day', 'Sales'];

while ($row = $result->fetch_assoc()) {
    $data[] = [$row['day'], (int) $row['total_sales']];
}

echo json_encode($data);
?>
