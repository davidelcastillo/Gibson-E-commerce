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
$stmt1 = $conn->prepare("SELECT COUNT(*) AS total_records FROM orders");
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

$stmt = $conn->prepare("SELECT * FROM orders LIMIT $offset,$total_records_per_page");

$stmt->execute();

$featured_orders =  $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../asset/favicon.ico">
    <link rel="stylesheet" href="../css/Siderbar.css">
    <link rel="stylesheet" href="./css/orders.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Orders</title>
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
                                <th scope="col" class="table_title price-cell">Order Id</th>
                                <th scope="col"class="table_title">Order Status</th>
                                <th scope="col" class="table_title price-cell">Order Cost</th>
                                <th scope="col"class="table_title price-cell">Order Date</th>
                                <th scope="col"class="table_title price-cell">User Id</th>
                                <th scope="col"class="table_title price-cell">User Address</th>
                                <th scope="col" class="table_title">Details</th>
                                <th scope="col" class="table_title">Edit</th>
                                <th scope="col" class="table_title">Delete</th>
                            </tr>
                        </thead>
                        <tbody class="table-active"> 
                            <?php foreach($featured_orders as $order ) { ?>
                            <tr>
                                <td style="align-items: center;">
                                    <div class="Details_conteiner" >
                                    <p class="mb-0"> <?php echo $order['order_id'] ?> </p>
                                    </div>
                                </td>

                                <td style="align-items: center;">
                                    <div class="Details_conteiner" >
                                    <p class="mb-0"><?php echo $order['order_status'] ?> </p>
                                    </div>
                                </td>
                                
                                <td style="align-items: center;">
                                    <div class="Details_conteiner" >
                                    <p class="mb-0">  $ <?php echo $order['order_cost'] ?></p>
                                    </div>
                                </td>

                                <td style="align-items: center;">
                                    <div class="Details_conteiner" >
                                    <p class="mb-0"><?php echo $order['order_date'] ?></p>
                                    </div>
                                </td>

                                <td style="align-items: center;">
                                    <div class="Details_conteiner" >
                                    <p class="mb-0"><?php echo $order['user_id'] ?></p>
                                    </div>
                                </td>

                                <td style="align-items: center;">
                                    <div class="Details_conteiner" >
                                    <p class="mb-0"><?php echo $order['user_address'] ?></p>
                                    </div>
                                </td>

                                <td style="align-items: center;">
                                    <div class="Details_conteiner" >
                                    <p class="mb-0"> <a class="btn img-btn"  href="order_details.php?order_id=<?php echo $order['order_id'] ?>">Details</a> </p>
                                    </div>
                                </td>

                                <td style="align-items: center;">
                                    <div class="Details_conteiner" >
                                    <p class="mb-0"> <a class="btn edit-btn" href="edit_order.php?order_id=<?php echo $order['order_id'] ?>">Edit</a> </p>
                                    </div>
                                </td>

                                <td style="align-items: center;">
                                    <div class="Details_conteiner" >
                                    <p class="mb-0"> <a class="btn delete-btn" href="delete_order.php?order_id=<?php echo $order['order_id'] ?>" >Delete</a> </p>
                                    </div>
                                </td>
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