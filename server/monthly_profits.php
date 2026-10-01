<?php
require_once __DIR__ . '/../admin/require_login.php';
include('./connection.php'); // Conexión a la base de datos

header('Content-Type: application/json');

$startDate = $_GET['start'];
$endDate = $_GET['end'];

// Consulta para obtener las ganancias mensuales
$query = "SELECT MONTH(order_date) AS month, SUM(order_cost) AS total_profit
          FROM orders
          WHERE order_date BETWEEN ? AND  ?
          GROUP BY MONTH(order_date)
          ORDER BY MONTH(order_date)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $startDate, $endDate); // "ss" indica dos strings
$stmt->execute();
$result = $stmt->get_result();

$data = [];
$data[] = ['Month', 'Profit']; // Encabezados para Google Charts

while ($row = $result->fetch_assoc()) {
    $monthName = date('F', mktime(0, 0, 0, $row['month'], 10)); // Convertir número de mes a nombre
    $data[] = [$monthName, (float) $row['total_profit']];
}

echo json_encode($data);
?>
