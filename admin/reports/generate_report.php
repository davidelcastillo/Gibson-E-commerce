<?php
require_once(__DIR__ . '/../require_login.php');
if (isset($_GET['report'], $_GET['start_date'], $_GET['end_date'])) {
    $report = $_GET['report'];
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];

    // Validar formato de las fechas
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
        die("Invalid date format. Please use YYYY-MM-DD.");
    }

    // Verificar que la fecha de inicio no sea mayor que la fecha de fin
    if ($start_date > $end_date) {
        header("Location: ../reports.php?error=Start date cannot be later than end date");
    }

    // Procesar el reporte solicitado
    switch ($report) {
        case 'purchases_by_clients':
            include('report_purchases_by_clients.php');
            break;
        case 'most_sold_products':
            include('report_most_sold_products.php');
            break;
        case 'revenue_by_product':
            include('report_revenue_by_product.php');
            break;
        case 'report_inactive_customers':
            include('report_inactive_customers.php');
            break;
        case 'sales_by_month':
            include('report_sales_by_month.php');
            break;
        default:
            echo "Invalid report type.";
            break;
    }
} else {
    echo "Missing required parameters: report, start_date, or end_date.";
}

