<?php 

session_start();
include('../server/get_featured_products.php');

if(isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    // Obtener información del producto
    $stmt = $conn->prepare('SELECT * FROM products WHERE product_id = ?');
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $product =  $stmt->get_result();

    // Obtener detalles del producto
    $stmt1 = $conn->prepare('SELECT * FROM products_details WHERE product_id = ?');
    $stmt1->bind_param('i', $product_id);
    $stmt1->execute();
    $product_details =  $stmt1->get_result();
} else {
    header('location: ../index.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy</title>
    <link rel="stylesheet" href="">
    <link rel="icon" type="image/x-icon" href="../asset/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/ProductDetail.css">
    <link rel="stylesheet" href="../css/Header.css">
    <link rel="stylesheet" href="../css/Footer.css">
</head> 

<?php  
    include('../layouts/header-php.php');
?>

<section class="main_section">
    <div class="title">
        <h1>Buy</h1>
    </div>
    <?php while($row = $product->fetch_assoc()){ ?>
        <?php $row_detail = $product_details->fetch_assoc(); ?>

        <!-- Calcular precio con descuento si el producto o su categoría tienen promoción -->
        <?php
            $precio_original = $row['product_price'];
            $precio_descuento = $precio_original;
            $descuento = 0;

            // 1. Verificar si el producto tiene una promoción en `promotion_targets`
            $stmt2 = $conn->prepare("
                SELECT p.discount_value 
                FROM promotion_targets pt
                JOIN promotions p ON pt.promo_id = p.promo_id
                WHERE pt.target_id = ? AND pt.target_type = 'product'
            ");
            $stmt2->bind_param('i', $product_id);
            $stmt2->execute();
            $promo_product_result = $stmt2->get_result();
            if ($promo_product = $promo_product_result->fetch_assoc()) {
                $descuento = $promo_product['discount_value'];
            }

            // 2. Si el producto no tiene promoción, verificar si la categoría tiene promoción
            if ($descuento == 0) {
                $stmt3 = $conn->prepare("
                    SELECT p.discount_value
                    FROM promotion_targets pt
                    JOIN promotions p ON pt.promo_id = p.promo_id
                    WHERE pt.target_id = ? AND pt.target_type = 'category'
                ");
                $stmt3->bind_param('i', $row['body']);
                $stmt3->execute();
                $promo_category_result = $stmt3->get_result();
                if ($promo_category = $promo_category_result->fetch_assoc()) {
                    $descuento = $promo_category['discount_value'];
                }
            }

            // Aplicar descuento si existe
            if ($descuento > 0) {
                $precio_descuento = $precio_original * (1 - ($descuento / 100));
            }
        ?>

        <div class="product-page">
            <div class="product-image-section">
                <div class="product-images">
                    <div class="thumbnail-images">
                        <img class="fst_guit" src="../img/<?php echo $row['product_image1']; ?>" alt="Thumbnail 1" onclick="showImage('../img/<?php echo $row['product_image1']; ?>')">
                        <img src="../img/<?php echo $row['product_image2']; ?>" alt="Thumbnail 2" onclick="showImage('../img/<?php echo $row['product_image2']; ?>')">
                        <img src="../img/<?php echo $row['product_image3']; ?>" alt="Thumbnail 3" onclick="showImage('../img/<?php echo $row['product_image3']; ?>')">
                        <img src="../img/<?php echo $row['product_image4']; ?>" alt="Thumbnail 4" onclick="showImage('../img/<?php echo $row['product_image4']; ?>')">
                        <img src="../img/<?php echo $row['product_image5']; ?>" alt="Thumbnail 5" onclick="showImage('../img/<?php echo $row['product_image5']; ?>')">
                    </div>
                    <div class="main_img">
                        <img id="main-image" src="../img/<?php echo $row['product_image1']; ?>" alt="Guitar Image" class="main-image">
                    </div>
                </div>              
                <div class="product-description">
                    <h4><?php echo $row['product_description_title']; ?></h4>
                    <p><?php echo $row['product_description']; ?></p>
                </div>
            </div>

            <div class="product-details-section">
                <h2><?php echo $row['product_name']; ?></h2>
                <hr>
                <div class="product-options">
                    <div class="color">
                        <label>Color</label>
                        <img src="<?php echo $row_detail['product_color']; ?>" alt="">
                    </div>
                    <div class="option">
                        <label>Handedness</label>
                        <select>
                            <option>Right</option>
                            <option>Left</option>
                        </select>
                    </div>
                </div> 
                <hr>
                <div class="product-price">
                    <?php if ($descuento > 0) { ?>
                        <h3 style="text-decoration: line-through; color: red;">$<?php echo number_format($precio_original, 2); ?></h3>
                        <h3 style="color: green;">$<?php echo number_format($precio_descuento, 2); ?></h3>
                        <p style="color: green;">(Discount: <?php echo $descuento; ?>%)</p>
                    <?php } else { ?>
                        <h3>$<?php echo number_format($precio_original, 2); ?></h3>
                    <?php } ?>
                </div>
                <div class="product-btn">
                    <form method="POST" action="Cart.php">
                        <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>" />
                        <input type="hidden" name="product_price" value="<?php echo $precio_descuento; ?>" />
                        <?php if(isset($row['product_stock']) && $row['product_stock'] >= 1 ) { ?>
                            <button type="submit" name="add_to_cart">
                                Buy
                            </button>
                        <?php } else { ?>
                            <h3 style="color:rgb(99, 6, 6)">Out Of Stock</h3>
                        <?php } ?>
                    </form>
                </div>
                <img class="gibson_logo" src="../img/logoGibson.png" alt="">
            </div>    
        </div>
                            
        <div class="details_section">
            <hr>
            <div class="tabs">
                <button class="tablinks active" onclick="openTab(event, 'Body')">BODY</button>
                <button class="tablinks" onclick="openTab(event, 'Neck')">NECK</button>
                <button class="tablinks" onclick="openTab(event, 'Hardware')">HARDWARE</button>
                <button class="tablinks" onclick="openTab(event, 'Electronics')">ELECTRONICS</button>
                <button class="tablinks" onclick="openTab(event, 'Miscellaneous')">MISCELLANEOUS</button>
            </div>

            <div id="Body" class="tabcontent active">
                <div class="specs">
                    <div>
                        <p><strong>Body Style</strong><br><?php echo $row_detail['product_bodystyle']; ?></p>
                        <p><strong>Top</strong><br><?php echo $row_detail['product_top']; ?></p>
                        <p><strong>Body Shape</strong><br><?php echo $row_detail['product_bodyshape']; ?></p>
                    </div>
                    <div>
                        <p><strong>Body Material</strong><br><?php echo $row_detail['product_bodymaterial']; ?></p>
                        <p><strong>Body Finish</strong><br><?php echo $row_detail['product_finish']; ?></p>
                    </div>
                </div>
            </div>

            <div id="Neck" class="tabcontent">
                <div class="specs">
                    <div>
                        <p><strong>Profile</strong><br><?php echo $row_detail['product_profile']; ?></p>
                        <p><strong>Scale Length</strong><br><?php echo $row_detail['product_lenght']; ?></p>
                        <p><strong>Neck Material</strong><br><?php echo $row_detail['product_neckmaterial']; ?></p>
                    </div>
                    <div>
                        <p><strong>Fingerboard Radius</strong><br><?php echo $row_detail['product_fingerboard']; ?></p>
                        <p><strong>Nut Material</strong><br><?php echo $row_detail['product_nutmaterial']; ?></p>
                    </div>
                </div>
            </div>

            <div id="Hardware" class="tabcontent">
                <div class="specs">
                    <div>
                        <p><strong>Finish</strong><br><?php echo $row_detail['product_finish']; ?></p>
                        <p><strong>Strap Buttons</strong><br><?php echo $row_detail['product_strapbuttons']; ?></p>
                        <p><strong>Switch Tip</strong><br><?php echo $row_detail['product_switchtip']; ?></p>
                    </div>
                    <div>
                        <p><strong>Bridge</strong><br><?php echo $row_detail['product_brige']; ?></p>
                        <p><strong>Switch washer</strong><br><?php echo $row_detail['product_switchwasher']; ?></p>
                    </div>
                </div>
            </div>

            <div id="Electronics" class="tabcontent">
                <div class="specs">
                    <div>
                        <p><strong>Neck Pickup</strong><br><?php echo $row_detail['product_neckpickup']; ?></p>
                        <p><strong>Pickup Selector</strong><br><?php echo $row_detail['product_pickupselector']; ?></p>
                        <p><strong>Output Jack</strong><br><?php echo $row_detail['product_outputJack']; ?></p>
                    </div>
                    <div>
                        <p><strong>Bridge Pickup</strong><br><?php echo $row_detail['product_bridgepickup']; ?></p>
                        <p><strong>Controls</strong><br><?php echo $row_detail['product_controls']; ?></p>
                    </div>
                </div>
            </div>

            <div id="Miscellaneous" class="tabcontent">
                <div class="specs">
                    <div>
                        <p><strong>Strings Gauge</strong><br><?php echo $row_detail['product_stringgauge']; ?></p>
                        <p><strong>Case</strong><br><?php echo $row_detail['product_case']; ?></p>
                        <p><strong>Accessories</strong><br><?php echo $row_detail['product_accessories']; ?></p>
                    </div>
                </div>
            </div>


    <?php } ?>

    <div class="video_section">
            <hr>
            <iframe  src="https://www.youtube.com/embed/IFun5e2RwY0?si=WwsvBOL9Rkb1Ifw3" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
    </div>
</section>

<?php  
    include('../layouts/footer-php.php');
?>
