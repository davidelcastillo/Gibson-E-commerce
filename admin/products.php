<?php include('./header.php') ?>
<?php require_once(__DIR__ . '/require_login.php'); ?>
<?php 

if(isset($_GET['page_no']) && $_GET['page_no'] != '') {
    $page_no = $_GET['page_no'];

} else {
    // if user just entered the page 
    $page_no = 1;
}
// return nro of products 
$stmt1 = $conn->prepare("SELECT COUNT(*) AS total_records FROM products");
$stmt1->execute();
$stmt1->bind_result($total_records);
$stmt1->store_result();
$stmt1->fetch();

// products per page

$total_records_per_page = 10;

$offset = ($page_no-1) * $total_records_per_page;

$previous_page = $page_no-1;
$next_page = $page_no+ 1;

$adjacents = '2';

$total_no_of_pages = ceil( $total_records / $total_records_per_page );

// get all products

$query = "
SELECT p.product_id, p.product_name_shrt, p.product_price, p.product_stock, p.product_image1, 
       pr.discount_type, pr.discount_value
FROM products p
LEFT JOIN promotion_targets pt ON (pt.target_type = 'product' AND pt.target_id = p.product_id)
LEFT JOIN promotions pr ON pr.promo_id = pt.promo_id 
    AND pr.active = 1 
    AND NOW() BETWEEN pr.start_date AND pr.end_date
LEFT JOIN promotion_targets pt_cat ON (pt_cat.target_type = 'category' AND pt_cat.target_id = p.category_id)
LEFT JOIN promotions pr_cat ON pr_cat.promo_id = pt_cat.promo_id
    AND pr_cat.active = 1 
    AND NOW() BETWEEN pr_cat.start_date AND pr_cat.end_date
LIMIT $offset, $total_records_per_page";
$stmt = $conn->prepare($query);
$stmt->execute();
$featured_products = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../asset/favicon.ico">
    <link rel="stylesheet" href="../css/Siderbar.css">
    <link rel="stylesheet" href="./css/products.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Products</title>
</head>
<body>
<?php  
    include('../layouts/siderbar.php');
?>
        <div class="main-content">
            <header>
                <h1>Orders</h1>
            </header>
            <section class="main-tble">
            <?php 
                if(isset($_GET['success'])) { 
            ?>
                    <script>
                        Swal.fire({
                            icon: "success",
                            title: 'Success',
                            text: '<?php echo htmlspecialchars($_GET['success'], ENT_QUOTES, 'UTF-8'); ?>',
                            color: "#6f6d6b",
                            background: "#0f0e0b"
                        });
                    </script>    

            <?php } else if (isset($_GET['error'])) { ?>
                    <script>
                        Swal.fire({
                            icon: "error",
                            title: 'Error',
                            text: '<?php echo htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8'); ?>',
                            color: "#6f6d6b",
                            background: "#0f0e0b"
                        });
                    </script>   
            <?php }  ?>
                <div class="table_conteiner">
                    <table class="table table-borderless table-dark">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col" class="table_title price-cell">Product Id</th>
                                <th scope="col"class="table_title">Product Image</th>
                                <th scope="col"class="table_title">Product Name</th>
                                <th scope="col" class="table_title price-cell">Product Price</th>
                                <th scope="col"class="table_title price-cell">Product Stock</th>
                                <th scope="col" class="table_title">Edit Img</th>
                                <th scope="col" class="table_title">Edit</th>
                                <th scope="col" class="table_title">Delete</th>
                            </tr>
                        </thead>
                        <tbody class="table-active"> 
                        <?php foreach($featured_products as $product) { 
                            $price = $product['product_price'];

                            // Obtener descuentos individuales y por categoría
                            $discounts = [];
                            if (!empty($product['discount_type'])) {
                                $discounts[] = [
                                    'type'  => $product['discount_type'],
                                    'value' => $product['discount_value']
                                ];
                            }
                            if (!empty($product['discount_type_cat'])) {
                                $discounts[] = [
                                    'type'  => $product['discount_type_cat'],
                                    'value' => $product['discount_value_cat']
                                ];
                            }

                            // Aplicar el mayor descuento encontrado
                            $discounted_price = $price;
                            foreach ($discounts as $discount) {
                                if ($discount['type'] === 'percentage') {
                                    $temp_price = $price - ($price * ($discount['value'] / 100));
                                } elseif ($discount['type'] === 'fixed') {
                                    $temp_price = max(0, $price - $discount['value']);
                                }
                                $discounted_price = min($discounted_price, $temp_price);
                            }
                        ?>
                        <tr>
                            <td><?php echo $product['product_id']; ?></td>
                            <td>
                                <div class="Image_conteiner">
                                    <img src="../img/<?php echo $product['product_image1']; ?>" alt="">
                                </div>
                            </td>
                            <td><?php echo $product['product_name_shrt']; ?></td>
                            <td>
                                <div class="Details_conteiner">
                                    <?php if ($discounted_price < $price) { ?>
                                        <p class="mb-0">
                                            <span style="text-decoration: line-through; color: gray;">$<?php echo number_format($price, 2); ?></span>
                                            <span style="color: red; font-weight: bold;">$<?php echo number_format($discounted_price, 2); ?></span>
                                        </p>
                                    <?php } else { ?>
                                        <p class="mb-0">$<?php echo number_format($price, 2); ?></p>
                                    <?php } ?>
                                </div>
                            </td>
                            <td><?php echo $product['product_stock']; ?></td>
                        </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </section>
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
        </div>
    </div>
</body>
</html>