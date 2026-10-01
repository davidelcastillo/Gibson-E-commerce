<?php
require_once(__DIR__ . '/../require_login.php');
require('./fpdf.php');
include('../../server/connection.php');

class PDF extends FPDF {
    function Header() {
        $this->Image('../../img/Gibson_logo_black.png', 10, 6, 30);
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(40);
        $this->Cell(100, 10, 'Gibson - Sales by Month Report', 0, 1, 'C');
        $this->SetFont('Arial', 'I', 12);
        $this->Cell(190, 10, 'Total Sales by Month', 0, 1, 'C');
        $this->Ln(10);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}
// Validate date
if (isset($_GET['start_date'], $_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];

    // Validar formato de fechas
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
        die("Invalid date format. Please use YYYY-MM-DD.");
    }

    // Verificar que la fecha de inicio no sea mayor que la fecha de fin
    if ($start_date > $end_date) {
        die("Start date cannot be later than end date.");
    }
} else {
    die("Missing date parameters.");
}
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, 'Date Range: ' . $start_date . ' to ' . $end_date, 0, 1, 'C'); // Agregar fechas al encabezado del PDF
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(60, 10, 'Month', 1, 0, 'C', true);
$pdf->Cell(60, 10, 'Year', 1, 0, 'C', true);
$pdf->Cell(60, 10, 'Total Sales', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 10);
$query = " SELECT MONTH(order_date) AS month, YEAR(order_date) AS year, SUM(order_cost) AS total_sales
    FROM orders o
    WHERE o.order_date BETWEEN ? AND ? 
    GROUP BY YEAR(order_date), MONTH(order_date)
    ORDER BY year DESC, month DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param('ss', $start_date, $end_date); // Vincular las fechas
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $pdf->Cell(60, 10, date("F", mktime(0, 0, 0, $row['month'], 10)), 1);
    $pdf->Cell(60, 10, $row['year'], 1);
    $pdf->Cell(60, 10, '$' . number_format($row['total_sales'], 2), 1, 1, 'C');
}
$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 10, 'Report generated for the period: ' . $start_date . ' to ' . $end_date, 0, 1, 'C');



$pdf->Output('D', 'sales_by_month_report_' . $start_date . '_to_' . $end_date . '.pdf');
?>
