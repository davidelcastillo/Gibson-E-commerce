<!DOCTYPE html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <meta http-equiv="X-UA-Compatible" content="ie=edge"> -->
    <title>Cart</title>
    <link rel="stylesheet" href="">
    <link rel="icon" type="image/x-icon" href="../asset/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/Cart.css">
    <link rel="stylesheet" href="../css/Header.css">
    <link rel="stylesheet" href="../css/Footer.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<?php  
    include('../layouts/header-php.php');
?>

<?php 

session_start();

if (isset($_POST['add_to_cart'])){

  // if user already added product to cart
  if(isset($_SESSION['cart'])){

    $products_array_ids = array_column($_SESSION['cart'],'product_id');
    //if product has already been added
    if(!in_array($_POST['product_id'], $products_array_ids)){

      $product_id = $_POST['product_id'];

        $product_array = array(
          'product_id'=> $_POST['product_id'], 
          'product_image'=> $_POST['product_image'],
          'product_name'=> $_POST['product_name'],
          'product_price'=> $_POST['product_price'],
        );

        $_SESSION['cart'][$_POST['product_id']] = $product_array;  ?>

        <script> Swal.fire({
                position: "top-end",
                icon: "success",
                title: "Product was add to the cart",
                showConfirmButton: false,
                timer: 2000,
                color: "#6f6d6b",
                background: "#0f0e0b"
              });
        </script>


<?php 
      // product has already been added
    }else { ?>
      <script> Swal.fire({
        position: "top-end",
        icon: "warning",
        title: "Product was already added to the cart",
        showConfirmButton: false,
        timer: 2000,
        color: "#6f6d6b",
        background: "#0f0e0b"
      });
      </script>
    <?php }

    // if is the 1st product
  } else {

      $product_id = $_POST['product_id'];
      $product_image = $_POST['product_image'];
      $product_name = $_POST['product_name'];
      $product_price = $_POST['product_price'];

      $product_array = array(
        'product_id'=> $product_id, 
        'product_image'=> $product_image,
        'product_name'=> $product_name,
        'product_price'=> $product_price,
      );
      
      $_SESSION['cart'][$product_id] = $product_array; ?>
      
      <script> Swal.fire({
                position: "top-end",
                icon: "success",
                title: "Product was add to the cart",
                showConfirmButton: false,
                timer: 2000,
                color: "#6f6d6b",
                background: "#0f0e0b"
              });
        </script>
      <?php

  }

  //calculate TOTAL
  calculateTotalCart() ;
  $_SESSION['total_tax'] = $_SESSION['total'] + 4;

//remove product 4 cart
}else if(isset($_POST['remove_product'])){

  $product_id = $_POST['product_id'];
  unset($_SESSION['cart'][$product_id]);

  //calculate TOTAL
  calculateTotalCart() ;
  $_SESSION['total_tax'] = $_SESSION['total'] + 4;

}else {
  // header('location: index.php');
}


function calculateTotalCart() {
  $total = 0;
  $total_quantity = 0;

  foreach($_SESSION['cart'] as $key => $value) {
    $product = $_SESSION['cart'][$key];
    $price = $product['product_price'];

    $total = $total + $price;
    $total_quantity = $total_quantity + 1;
  }

  $_SESSION['total'] = $total;
  $_SESSION['quantity'] = $total_quantity;
}

?>


    <section class="main_section">
        <div class="title">
            <h1>
                Cart
            </h1>
        </div>
        
        <section class="Shopping_section">
          <div class="table_conteiner">
            <table class="table table-borderless table-dark">
              <thead class="table-dark">
                <tr>
                  <th scope="col" class="table_title">Shopping Bag</th>
                  <th scope="col"class="table_title price-cell">Price</th>
                </tr>
              </thead>

              <tbody class="table-active">
                      
                <?php if(isset($_SESSION['cart'])) { foreach($_SESSION['cart'] as $key => $value) { ?>
                <tr>
                  <th scope="row" style="width: 50vw; align-items: center;">
                    <div class="product_conteiner">
                      <img src="../img/<?php echo $value['product_image'];?>" class=" img-1"  alt="Product">
                      <div class="product_detail">
                        <p class="mb-5"><?php echo $value['product_name'];?></p>
                        <br>
                        <form method="POST" action="Cart.php">
                          <input type="hidden" name="product_id" value="<?php echo $value['product_id']; ?>"/>
                          <input type="submit" name="remove_product" class="remove-btm" value="Remove"/>
                        </form>
                      </div>
                    </div>
                  </th>
                          
                  <td style="align-items: center;">
                    <div class="Subtotal_conteiner" >
                      <p class="mb-0">$ <?php echo $value['product_price'];?></p>
                    </div>
                  </td>
                </tr>

                <?php } } ?>
              </tbody>
            </table>
          </div>
          
          <div class="Total_conteiner";">
            <div class="Sub_conteiner">
              <p class="mb-3">Subtotal</p>
              <p class="mb-4">$ <?php if (isset($_SESSION['total'])) { echo $_SESSION['total']; }?></p>
            </div>
            
            <div class="Sub_conteiner">
              <p class="mb-3">Shipping</p>
              <p class="mb-4">$4</p>
              <hr>
            </div>

            <div class="Total">
              <p class="mb-1">Total (tax included)</p>
              <p class="mb-4">$<?php if (isset($_SESSION['total_tax'])) {echo $_SESSION['total_tax'];} ?></p>
            </div>
            
            <div class="checkout-container">
              <form  method="POST" action="Checkout.php">
                <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) { ?>
                  <input type="submit" class="btn checkout-btn" value="Checkout" name="checkout">
                <?php } ?>
              </form>
            </div>
          </div>     
        </section>
    </section>
<?php  
  include('../layouts/footer-php.php');
?>