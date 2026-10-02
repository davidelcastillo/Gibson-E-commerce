<?php 

session_start();
include("./connection.php");

if(!isset($_SESSION["logged_in"]) ) {
    header('Location: ../php/Login.php?error=Please Login/Register to Place Order');
    exit();
}

else{
    if(isset($_POST['Place_order']) ) {

        // get user info n' store user info in dbb
        $name = $_POST['firstName'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $city = $_POST['city'];
        $order_cost = $_SESSION["total"];
        $order_status = "not paid";
        $user_id = $_SESSION['user_id'];
        $order_date = date("Y-m-d H:i:s");
    
        $_SESSION['user_email'] = $email;
    
        $stmt = $conn->prepare("INSERT INTO orders (order_cost, order_status,user_id,user_phone,user_city,user_address,order_date)
                        VALUES (?,?,?,?,?,?,?);");
    
        $stmt->bind_param('ssiisss', $order_cost, $order_status, $user_id, $phone, $city, $address, $order_date);
    
        $stmt_status = $stmt->execute();
    
        if(!$stmt_status) {
            header('Location: ../php/Login.php');
            exit();
        }
    
        $order_id = $stmt->insert_id;
    
        //issue new order n' store order information
        echo $order_id;
    
    
        //get product 4 cart (session)
        foreach($_SESSION['cart'] as $_key => $value) {
    
            $product = $_SESSION['cart'][$_key]; //[]
            $product_id = $product['product_id'] ;  
            $product_name = $product['product_name'] ;  
            $product_price = $product['product_price'] ;  
            $product_image = $product['product_image'] ;  
    
            // store each single item in dbb
            $stmp1 = $conn-> prepare('INSERT INTO order_items(order_id, product_id, product_name, product_image, product_price, user_id, item_date)
            VALUES (?,?,?,?,?,?,?)');
    
            $stmp1->bind_param('iissiis', $order_id, $product_id, $product_name, $product_image, $product_price, $user_id, $order_date);
    
            $stmp1->execute();
        }
    
    
        //clean cart --> until payment done
    
        // unset( $_SESSION['cart'] );
    
        $_SESSION['order_id'] = $order_id;
    
        // inform user whether is fine or problem
    
        header('location: ../php/Payment.php?order_status=order place successfully');
    
    }
}

?>