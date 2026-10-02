<?php include('./header.php') ?>
<?php require_once(__DIR__ . '/require_login.php'); ?>
<?php 

if( isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    $stmt = $conn->prepare("CALL GetOrderDetails(?);");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $details =  $stmt->get_result();

} else {
    header("Location: orders.php?error=Unauthorized access");
    exit;
}

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
    <title>Order Details</title>

</head>
<body>
<?php  
    include('../layouts/siderbar.php');
?>
        <div class="main-content">
            <header>
                <h1>Order N° <?php echo $order_id ?></h1>
            </header>
            <section class="main-tble">
                <div class="table_conteiner">
                    <table class="table table-borderless table-dark">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col" class="table_title price-cell">Order Id</th>
                                <th scope="col"class="table_title price-cell">Product Id</th>
                                <th scope="col" class="table_title ">Product Name</th>
                                <th scope="col"class="table_title ">Product Image</th>
                                <th scope="col"class="table_title price-cell">Product Price</th>
                                <th scope="col" class="table_title">Edit</th>
                                <th scope="col" class="table_title">Delete</th>
                            </tr>
                        </thead>
                        <tbody class="table-active"> 
                            <?php foreach($details as $item ) { ?>
                            <tr>
                                <td style="align-items: center;">
                                    <div class="Details_conteiner" >
                                    <p class="mb-0"> <?php echo $item['order_id'] ?> </p>
                                    </div>
                                </td>

                                <td style="align-items: center;">
                                    <div class="Details_conteiner" >
                                    <p class="mb-0"> <?php echo $item['product_id'] ?> </p>
                                    </div>
                                </td>

                                <td style="align-items: center;">
                                    <div class="Details_conteiner" >
                                    <p class="mb-0"> <?php echo $item['product_name'] ?> </p>
                                    </div>
                                </td>

                                <td style="align-items: center;">
                                    <div class="Image_conteiner" >
                                    <img src="../img/<?php echo $item['product_photo'] ?>" alt="">
                                    </div>
                                </td>

                                <td style="align-items: center;">
                                    <div class="Details_conteiner" >
                                    <p class="mb-0">$ <?php echo $item['price'] ?></p>
                                    </div>
                                </td>

                                <td style="align-items: center;">
                                    <div class="Details_conteiner" >
                                    <p class="mb-0"> <a class="btn edit-btn" href="edit_item.php?item_id=<?php echo $item['item_id'] ?>">Edit</a> </p>
                                    </div>
                                </td>

                                <td style="align-items: center;">
                                    <div class="Details_conteiner" >
                                    <p class="mb-0"> <a class="btn delete-btn" href="delete_item.php?item_id=<?php echo $item['item_id']  ?>" >Delete</a> </p>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</body>
</html>