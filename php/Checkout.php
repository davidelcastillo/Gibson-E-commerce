<?php 

session_start();

if (!empty( $_SESSION["cart"] ) ) {


// send user to home
} else {

    header('location: ../index.php');

}

?>


<!DOCTYPE html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <meta http-equiv="X-UA-Compatible" content="ie=edge"> -->
    <title>Checkout</title>
    <link rel="stylesheet" href="">
    <link rel="icon" type="image/x-icon" href="../asset/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/Checkout.css">
    <link rel="stylesheet" href="../css/Header.css">
    <link rel="stylesheet" href="../css/Footer.css">

</head>

<body>
<?php  
    include('../layouts/header-php.php');
?>
    <section class="main_section">
        <div class="title">
            <h1>
                Checkout
            </h1>
        </div>
        <section class="nno_title" >
            <div class="info_conteiner">
                <div class="info_details">
                    <h4>Billing address</h4>
                    <form class="needs-validation bill-form" method="POST" action="../server/place_order.php">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstName">First name</label>
                                <input type="text" class="form-control" name="firstName" placeholder="" value="" required="">
                                <div class="invalid-feedback">
                                Valid first name is required.
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lastName">Last name</label>
                                <input type="text" class="form-control" name="lastName" placeholder="" value="" required="">
                                <div class="invalid-feedback">
                                Valid last name is required.
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" name="email" placeholder="you@example.com">
                        <div class="invalid-feedback">
                            Please enter a valid email address for shipping updates.
                        </div>
                        </div>

                        <div class="mb-3">
                        <label for="address">Address</label>
                        <input type="text" class="form-control" name="address" placeholder="1234 Main St" required="">
                        <div class="invalid-feedback">
                            Please enter your shipping address.
                        </div>
                        </div>

                        <div class="mb-3">
                        <label for="phone">Phone</label>
                        <input type="text" class="form-control" name="phone" placeholder="333 111 2222">
                        </div>

                        <div class="mb-3">
                        <label for="city">City</label>
                        <input type="text" class="form-control" name="city" placeholder="Texas">
                        </div>

                        <hr>
                        <div class="place_order-container">
                            <input type="submit" class="place_order-btn" name="Place_order" value="Continue to checkout" >
                        </div>
                    </form>
                </div>
                <div class="cart_details">
                    <h4>
                        Your cart
                    </h4>

                    <ul>

                        <?php foreach($_SESSION['cart'] as $key => $value) { ?>

                        <li >
                            <div>
                                <h6><?php echo $value['product_name'];?></h6>
                            </div>
                            <span class="price">$<?php echo $value['product_price'];?></span>
                        </li>

                        <?php } ?>

                        <li class="list-group-item d-flex justify-content-between total-li">
                            <span>Total (USD)</span>
                            <h4>$<?php echo $_SESSION['total'];?>.00</h4>
                        </li>
                    </ul>
                </div>
            </div>
        </section>
        
    </section>
<?php  
    include('../layouts/footer-php.php');
?>