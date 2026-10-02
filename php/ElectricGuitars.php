<?php 
session_start();
include('../server/connection.php');

if(isset($_GET['page_no']) && $_GET['page_no'] != '') {
    $page_no = $_GET['page_no'];
} else {
    // if user just entered the page 
    $page_no = 1;
}

// return number of products 
$stmt1 = $conn->prepare("SELECT COUNT(*) AS total_records FROM products WHERE product_category='electric'");
$stmt1->execute();
$stmt1->bind_result($total_records);
$stmt1->store_result();
$stmt1->fetch();

// products per page
$total_records_per_page = 8;
$offset = ($page_no-1) * $total_records_per_page;
$previous_page = $page_no-1;
$next_page = $page_no+ 1;
$adjacents = '2';
$total_no_of_pages = ceil( $total_records / $total_records_per_page );

// get all products
$stmt = $conn->prepare("SELECT * FROM products WHERE product_category='electric' LIMIT $offset,$total_records_per_page");
$stmt->execute();
$featured_products =  $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electric Guitars</title>
    <link rel="stylesheet" href="">
    <link rel="icon" type="image/x-icon" href="../asset/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/ElectricGuitars.css">
    <link rel="stylesheet" href="../css/Header.css">
    <link rel="stylesheet" href="../css/Footer.css">
</head>

<?php  
    include('../layouts/header-php.php');
?>
    <section class="main_section">
        <div class="titleContainer">
            <h1>Electric</h1>
        </div>

        <div class="cardsContainer">
            <?php while($row = $featured_products->fetch_assoc()) { 
                // Calcular precio con descuento si el producto tiene promoción
                $precio_original = $row['product_price'];
                $precio_descuento = $precio_original;
                $descuento = 0;

                // 1. Verificar si el producto tiene una promoción
                // Asegurarse de cerrar la consulta anterior antes de reutilizar el stmt
                if (isset($stmt3)) {
                    $stmt3->close();
                }
                $stmt3 = $conn->prepare("SELECT p.discount_value 
                                             FROM promotion_targets pt
                                             JOIN promotions p ON pt.promo_id = p.promo_id
                                             WHERE pt.target_id = ? AND pt.target_type = 'category'");
                    $stmt3->bind_param('i', $row['body']);
                    $stmt3->execute();
                    $promo_category_result = $stmt3->get_result();
                    if ($promo_category = $promo_category_result->fetch_assoc()) {
                        $descuento = $promo_category['discount_value'];
                    }
                

                // 2. Si el producto no tiene promoción, verificar si la categoría tiene promoción
                if ($descuento == 0) {
                    if (isset($stmt2)) {
                        $stmt2->close();
                    }
                    $stmt2 = $conn->prepare("SELECT p.discount_value FROM promotion_targets pt
                        JOIN promotions p ON pt.promo_id = p.promo_id
                        WHERE pt.target_id = ? AND pt.target_type = 'product'");
                    $stmt2->bind_param('i', $row['product_id']);
                    $stmt2->execute();
                    $promo_product_result = $stmt2->get_result();
                    if ($promo_product = $promo_product_result->fetch_assoc()) {
                        $descuento = $promo_product['discount_value'];
                    }
                }

                // Aplicar descuento si existe
                if ($descuento > 0) {
                    $precio_descuento = $precio_original * (1 - ($descuento / 100));
                }
            ?>
                <div class="card"> 
                    <div class="logoGibsonCard">
                        <img class="logoGibsonImgCard" src="../img/logoGibson.png" alt="Logo de Gibson">
                    </div>
                    <img class="guitarImgContainer" src="../img/<?php echo $row['product_image_stand']?>" alt="Guitar EXPL Black">
                    <p class="productName"><?php echo $row['product_name_shrt']?> </p>
                    
                    <!-- Mostrar precio con descuento -->
                    <p class="productPrice">
                        <?php if ($descuento > 0) { ?>
                            <span style="text-decoration: line-through; color: red;">US$<?php echo number_format($precio_original, 2); ?></span>
                            <span style="color: green;">US$<?php echo number_format($precio_descuento, 2); ?></span>
                        <?php } else { ?>
                            US$<?php echo number_format($precio_original, 2); ?>
                        <?php } ?>
                    </p>
                    <a href="<?php echo "ProductDestail.php?product_id=". $row['product_id'];?>">
                        <button class="buy-btn">Buy now</button>
                    </a>
                </div>
            <?php } ?>
        </div>

        <div class="pagination-container">
            <nav aria-label="Page navigation example">
                <ul class="pagination">
                    <li class="page-item <?php if($page_no<=1) {echo 'disable';} ?>">
                        <a class="page-link" href="<?php if($page_no<=1){echo '#';} else { echo '?page_no='.($page_no-1); } ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    <li class="page-item"><a class="page-link" href="?page_no=1">1</a></li>
                    <?php if($total_no_of_pages>1) { ?>
                    <li class="page-item"><a class="page-link" href="?page_no=2">2</a></li>
                    <?php } ?>
                    <?php if($page_no>= 3) { ?>
                        <li class="page-item"><a class="page-link" href="#">...</a></li>
                    <?php } ?>
                    <li class="page-item <?php if($page_no>=$total_no_of_pages) {echo 'disable';} ?>">
                        <a class="page-link" href="<?php if($page_no>=$total_no_of_pages){echo '#';} else { echo '?page_no='.($page_no+1); } ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </section>

<?php  
    include('../layouts/footer-php.php');
?>
