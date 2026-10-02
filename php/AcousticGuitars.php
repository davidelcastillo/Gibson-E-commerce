<?php 
session_start();
include('../server/connection.php');
if(isset($_GET['page_no']) && $_GET['page_no'] != '') {
    $page_no = $_GET['page_no'];
} else {
    // if user just entered the page 
    $page_no = 1;
}
// return nro of products 
$stmt1 = $conn->prepare("SELECT COUNT(*) AS total_records FROM products WHERE product_category='acustic'");
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
$stmt = $conn->prepare("SELECT * FROM products WHERE product_category='acustic' LIMIT $offset,$total_records_per_page");
$stmt->execute();
$featured_products =  $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acoustic Guitars</title>
    <link rel="stylesheet" href="">
    <link rel="icon" type="image/x-icon" href="../asset/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/AcousticGuitars.css">
    <link rel="stylesheet" href="../css/Header.css">
    <link rel="stylesheet" href="../css/Footer.css">
<?php  
    include('../layouts/header-php.php');
?>
    <section class="main_section">
        <div class="titleContainer">
            <h1>Acoustic</h1>
        </div>

        <div class="cardsContainer">
            <?php while($row= $featured_products->fetch_assoc()) {?>
                <div class="card"> 
                    <div class="logoGibsonCard">
                        <img class="logoGibsonImgCard" src="../img/logoGibson.png" alt="Logo de Gibson">
                    </div>
                    <img class="guitarImgContainer" src="../img/<?php echo $row['product_image_stand']?>" alt="Guitar EXPL Black">
                    <p class="productName"><?php echo $row['product_name_shrt']?> </p>
                    <p class="productPrice">US$<?php echo $row['product_price']?></p>
                    <a href="<?php echo "ProductDestail.php?product_id=". $row['product_id'];?>">
                        <button class="buy-btn"> Buy now </button>
                    </a>
                </div>
            <?php } ?>
        </div>

        <div class="pagination:container">
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
                        <li class="page-item"><a class="page-link" href="<?php echo "?page_no=".$page_no; ?>"> <?php echo $page_no;?></a></li>
                    <?php } ?>

                    <li class="page-item <?php if($page_no>= $total_no_of_pages) {echo 'disable';} ?>">
                        <a class="page-link" href="<?php if($page_no>= $total_no_of_pages){echo '#';} else { echo '?page_no='.($page_no+1); } ?>" aria-label="Next">
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