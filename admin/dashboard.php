<?php 
include('../admin/header.php');
if (!isset($_SESSION['admin_logged_in'])) {
    header('location: ../admin/login.php');
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../asset/favicon.ico">
    <link rel="stylesheet" href="./css/dashboard.css">
    <link rel="stylesheet" href="../css/Siderbar.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <title>Dashboard</title>
    <script type="text/javascript" src="../admin/js/charts.js">
    </script>
</head>
<body>
<?php  
    include('../layouts/siderbar.php');
?>
        <div class="main-content">
            <header>
                <h1>Welcome back, <?php if(isset($_SESSION['admin_name'])) {echo $_SESSION['admin_name'];} ?></h1>
            </header>

            <?php 
                if(isset($_GET['admin_log_success'])) { 
            ?>
                <script>
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: " <?php echo $_GET['admin_log_success']; ?> ",
                        showConfirmButton: false,
                        timer: 1500,
                        color: "#6f6d6b",
                        background: "#0f0e0b",

                    });
                </script>
            <?php 
                }
            ?>

            <!-- Sección del gráfico dinámico -->
            <section class="charts">
                <div class="filters">
                    <label for="startDate">Start Date:</label>
                    <input type="date" class="formEntry"  id="startDate">
                    <label for="endDate">End Date:</label>
                    <input type="date" class="formEntry"  id="endDate">
                    <button class="submit" onclick="validateCharts()">Aplicar</button>
                </div>

                <div class="chart-container">
                    <div id="clientsPurchasesChart" class="chart"></div>
                </div>

                <div class="chart-container">
                    <div id="monthlyProfitsChart" class="chart"></div>
                </div>

                <div class="chart-container">
                    <div id="dailySalesChart" class="chart"></div>
                </div>
            </section>
        </div>
    </div>
</body>
</html>