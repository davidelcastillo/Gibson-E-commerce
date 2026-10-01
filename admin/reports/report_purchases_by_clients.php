<?php
require_once(__DIR__ . '/../require_login.php');
require('./fpdf.php');
include('../../server/connection.php');

class PDF extends FPDF {
    // Header
    function Header() {
        $this->Image('../../img/Gibson_logo_black.png', 10, 6, 30);
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(40);
        $this->Cell(100, 10, 'Gibson - Report: Purchases by Clients', 0, 1, 'C');
        $this->SetFont('Arial', 'I', 12);
        $this->Cell(190, 10, 'List of Purchases by Clients', 0, 1, 'C');
        $this->Ln(10);
    }

    // Footer
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page '.$this->PageNo().'/{nb}', 0, 0, 'C');
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

// Create a new PDF instance
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// **Imprimir rango de fechas en el PDF**
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, 'Date Range: ' . $start_date . ' to ' . $end_date, 0, 1, 'C'); // Agregar fechas al encabezado del PDF
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(60, 10, 'Client ID', 1, 0, 'C', true);
$pdf->Cell(60, 10, 'Client Name', 1, 0, 'C', true);
$pdf->Cell(60, 10, 'Total Purchases', 1, 1, 'C', true);
$pdf->SetFont('Arial', '', 10);
// Fetch data from the database
$query = "SELECT u.user_id, u.user_name AS client_name, SUM(o.order_cost) AS total_purchases
          FROM orders o
          JOIN users u ON o.user_id = u.user_id
          WHERE o.order_date BETWEEN ? AND ? -- Filtro por rango de fechas
          GROUP BY u.user_id
          ORDER BY total_purchases DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param('ss', $start_date, $end_date); // Vincular las fechas
$stmt->execute();
$result = $stmt->get_result();

// // Output data to PDF
// $pdf->SetFont('Arial', 'B', 10);
// $pdf->Cell(30, 10, 'Client ID', 1);
// $pdf->Cell(70, 10, 'Client Name', 1);
// $pdf->Cell(40, 10, 'Total Purchases', 1);
// $pdf->Ln();

// $pdf->SetFont('Arial', '', 10);
while ($row = $result->fetch_assoc()) {
    $pdf->Cell(60, 10, $row['user_id'], 1);
    $pdf->Cell(60, 10, $row['client_name'], 1);
    $pdf->Cell(60, 10, '$' . number_format($row['total_purchases'], 2), 1);
    $pdf->Ln();
}

$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 10, 'Report generated for the period: ' . $start_date . ' to ' . $end_date, 0, 1, 'C');


// Output the PDF
$pdf->Output('D', 'Report_Purchases_by_Clients_' . $start_date . '_to_' . $end_date . '.pdf');
?>
