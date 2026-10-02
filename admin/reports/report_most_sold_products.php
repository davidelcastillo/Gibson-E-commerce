<?php
require_once(__DIR__ . '/../require_login.php');
require('./fpdf.php');
include('../../server/connection.php');

class PDF extends FPDF {
    function Header() {
        $this->Image('../../img/Gibson_logo_black.png', 10, 6, 30);
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(40);
        $this->Cell(100, 10, 'Gibson - Most Sold Products Report', 0, 1, 'C');
        $this->SetFont('Arial', 'I', 12);
        $this->Cell(190, 10, 'List of Most Frequently Sold Products', 0, 1, 'C');
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
// **Imprimir rango de fechas en el PDF**
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, 'Date Range: ' . $start_date . ' to ' . $end_date, 0, 1, 'C'); // Agregar fechas al encabezado del PDF
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(120, 10, 'Product Name', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Total Sold', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 10);
$query = " SELECT p.product_name, SUM(oi.product_price) AS total_sold
    FROM products p
    JOIN order_items oi ON p.product_id = oi.product_id
    WHERE oi.item_date BETWEEN ? AND ?
    GROUP BY p.product_id
    ORDER BY total_sold DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param('ss', $start_date, $end_date); // Vincular las fechas
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $pdf->Cell(120, 10, $row['product_name'], 1);
    $pdf->Cell(40, 10, $row['total_sold'], 1, 1, 'C');
}
$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 10, 'Report generated for the period: ' . $start_date . ' to ' . $end_date, 0, 1, 'C');

$pdf->Output('D', 'most_sold_products_report_' . $start_date . '_to_' . $end_date . '.pdf');
?>
