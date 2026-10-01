<?php
require_once __DIR__ . '/../admin/require_login.php';
include('./connection.php'); // Conexión a la base de datos

header('Content-Type: application/json');

$startDate = $_GET['start'];
$endDate = $_GET['end'];

// Consulta para obtener las compras por cliente
$query = "SELECT u.user_name AS client_name, SUM(o.order_cost) AS total_purchases
          FROM orders o
          JOIN users u ON o.user_id = u.user_id
          WHERE order_date BETWEEN ? AND  ?
          GROUP BY u.user_name
          ORDER BY total_purchases DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $startDate, $endDate); // "ss" indica dos strings
$stmt->execute();
$result = $stmt->get_result();

$data = [];
$data[] = ['Client Name', 'Total Purchases']; // Encabezados para Google Charts

// Recorrer los resultados de la consulta
while ($row = $result->fetch_assoc()) {
    $data[] = [$row['client_name'], (float) $row['total_purchases']];
}

// Enviar datos como JSON
echo json_encode($data);
?>

