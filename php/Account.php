<?php
session_start();
include("../server/connection.php");
if(!isset($_SESSION["logged_in"])) {
    header('location: ./Login.php');
    exit;
}
if(isset($_GET["logout"])) {
    if (isset($_SESSION["logged_in"])) {
        unset( $_SESSION["logged_in"] );
        unset( $_SESSION["user_email"] );
        unset( $_SESSION["user_name"] );
        unset( $_SESSION["user_phone"] );
        header('location: ../index.php');
        exit;
    }
}
if(isset($_POST['change_password'])) { 
    $password = $_POST['password'];
    $new_password = $_POST['new_password'];
    if (strlen($new_password) < 6) {
        header('location: Account.php?error2=password must be at least 6 characters');
    }else {
        $stmt1 = $conn->prepare('SELECT user_password FROM users WHERE user_email = ?');
        $stmt1->bind_param('s', $_SESSION['user_email']);
        $stmt1->execute();
        $stmt1->bind_result($pss);
        $stmt1->store_result();
        $stmt1->fetch();
        if ($pss != md5($password)) {
            header('location: Account.php?error=Wrong Password');   
        }else {
            $stmt2 = $conn->prepare('UPDATE users SET user_password = ?
                                    WHERE user_email = ?'); 
            $stmt2->bind_param('ss', md5($new_password), $_SESSION['user_email']);         
            if($stmt2->execute()){
                header('location: Account.php?message=Password has been updated succesfully');
            }else {
                header('location: Account.php?error2=Could´t update password');
            }
        }
    }
}
//Get Orders
if(isset($_SESSION['logged_in'])) {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id=?");
    $stmt->bind_param("i", $_SESSION["user_id"]);
    $stmt->execute();
    $orders =  $stmt->get_result();
}
?>
<!DOCTYPE html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <meta http-equiv="X-UA-Compatible" content="ie=edge"> -->
    <title>Account</title>
    <link rel="stylesheet" href="">
    <link rel="icon" type="image/x-icon" href="../asset/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/Account.css">
    <link rel="stylesheet" href="../css/Header.css">
    <link rel="stylesheet" href="../css/Footer.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<?php  
    include('../layouts/header-php.php');
?>
    <section class="main_section">
        <div class="title">
            <h1>
                My Account
            </h1>
        </div>
        <section class="info_section">

        <?php 
            if(isset($_GET['payment_message'])) { 
        ?>
                <script>
                    Swal.fire({
                        icon: "success",
                        title: "<?php echo $_GET['payment_message'];  ?>",
                        color: "#6f6d6b",
                        background: "#0f0e0b"
                    });
                </script>    

        <?php } ?>
            <div class="info_conteiner">
                <div class="account_details">
                    <h4>Account Info</h4>
                    <?php 
                        if(isset($_GET['log_success'])) { 
                    ?>
                            <script>
                                Swal.fire({
                                    position: "top-end",
                                    icon: "success",
                                    title: " <?php echo $_GET['log_success']; ?> ",
                                    showConfirmButton: false,
                                    timer: 1500,
                                    color: "#6f6d6b",
                                    background: "#0f0e0b",

                                });
                            </script>
                    <?php 
                        }
                    ?>


                    <form>
                        <div class="mb-3">
                        <label for="name">Name : <?php if(isset($_SESSION['user_name'])) {echo $_SESSION['user_name'];} ?> </label>
                        </div>

                        <div class="mb-3">
                        <label for="email">Email : <?php if(isset($_SESSION['user_email'])) {echo $_SESSION['user_email'];} ?> </label>
                        </div>

                        <div class="mb-3">
                        <label for="phone">Phone: <?php if(isset($_SESSION['user_phone'])) {echo $_SESSION['user_phone'];} ?>  </label>
                        </div>

                        <hr>
                        <a href="#orders" id="order-btn">
                        <p>Your orders</p>
                        </a>
                        <a href="./Account.php?logout=1" id="logout-btn" >
                        <p>Logout</p>
                        </a>
                    </form>
                </div>
                <div class="passw_details">
                    <form method="POST" action="Account.php">
                        <h4>
                            Change Password
                        </h4>

                        <div class="mb-3">
                            <label for="password">Password</label>
                            <input type="text" class="form-control main-inpt" name="password" placeholder="Actual Password">
                            <p style="color:red"><?php if(isset($_GET['error'])){echo $_GET['error'];} ?></p>
                        </div>

                        <div class="mb-3">
                            <label for="password">New Password</label>
                            <input type="text" class="form-control main-inpt" name="new_password" placeholder="New Password">
                            <p style="color:red"><?php if(isset($_GET['error2'])){echo $_GET['error2'];} ?></p>
                        </div> 
                    <?php 
                        if(isset($_GET['message'])){
                    ?>
                    <script>
                        Swal.fire({
                            icon: "success",
                            title: "<?php echo $_GET['message'] ; ?>",
                            color: "#6f6d6b",
                            background: "#0f0e0b"
                        });
                    </script>

                    <?php } ?>

                    <input type="submit" class="btn checkout-btn" value="Change Password" name="change_password">
                    </form>
                </div>
            </div>
            <div id="orders" class="info_conteiner">
                <div class="order_conteiner" > 
                    <h4>
                        Your Orders
                    </h4>
                    <table class="table table-dark table-borderless" style="background-color: rgba(0, 0, 0, 0); align-items: center;">
                        <thead>
                            <tr>
                            <th scope="col"><h5>Order Id</h5></th>
                            <th scope="col"><h5>Order Cost</h5></th>
                            <th scope="col"><h5>Order Status</h5></th>
                            <th scope="col"><h5>Order Date</h5></th>
                            <th scope="col"><h5>Order Details</h5></th>
                            </tr>
                        </thead>
                        <tbody class="table-active" >
                            <?php while($row = $orders->fetch_assoc() ){ ?>
                            <tr>
                                <td><h6><?php echo $row['order_id']; ?></h6></td>
                                <td><h6><?php echo $row['order_cost']; ?></h6></td>
                                <td><h6><?php echo $row['order_status']; ?></h6></td>
                                <td><h6><?php echo $row['order_date']; ?></h6></td>
                                <td style="display: flex; text-align: center; align-items: center; justify-content: center; flex-direction: column;" >
                                    <form method="POST" action="Order_details.php" class="detail-form">
                                        <input type="hidden" value="<?php echo $row['order_status']; ?>" name="order_status">
                                        <input type="hidden" value="<?php echo $row['order_id']; ?>" name="order_id">
                                        <input class="btn detail-btn" type="submit" value="Details" name="order_detail">
                                    </form>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>

                </div>
            </div>
        </section>
    </section>
    <?php  
        include('../layouts/footer-php.php');
    ?>