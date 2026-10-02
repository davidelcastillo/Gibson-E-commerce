<?php include('./header.php') ?>
<?php require_once(__DIR__ . '/require_login.php'); ?>
<?php 
if( isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ? ");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $products =  $stmt->get_result();
}else if( isset($_POST['edit_btn'])) {
    $error = 0;
    // Validar el título (aceptar paréntesis también)
    if (empty($_POST['title']) || !preg_match("/^[A-Za-z0-9\s,\'\-\(\)‘]+$/", $_POST['title'])) {
        $error = 1;
    }
    // Validar la descripción (no vacío)
    if (empty($_POST['description']) || strlen($_POST['description']) < 5) {
        $error = 1;
    }
    // Validar el precio (número positivo con hasta 2 decimales)
    if (empty($_POST['price']) || !preg_match("/^\d+(\.\d{1,2})?$/", $_POST['price'])) {
        $error = 1;
    }
    // Validar el stock (entero positivo)
    if (empty($_POST['stock']) || !preg_match("/^[0-9]+$/", $_POST['stock'])) {
        $error = 1;
    }
    // Validar la categoría (debe coincidir con una de las opciones permitidas)
    $allowedCategories = ['Electric', 'Acoustic'];
    if (empty($_POST['category']) || !in_array($_POST['category'], $allowedCategories)) {
        $error = 1;
    }
    if ($error == 1) {
        header('location: products.php?error=Invalid input detected');
        exit;
    } else {
        $product_id = $_POST['product_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $category = $_POST['category'];
        $stmt2 = $conn->prepare('UPDATE products 
                                        SET product_name = ?, product_description = ?, product_price = ?, product_category = ?, product_stock = ?
                                        WHERE product_id = ?'); 
        $stmt2->bind_param('ssdsii', $title,$description,$price,$category,$stock,$product_id);    

        if ($stmt2->execute()) {
            header( "Location: ./products.php?success=Product has been updated successfully");
        }else {
            header( "Location: ./products.php?error=Error updating product, try again");
        }
    }
}else {
    header("Location: products.php?error=Unauthorized access");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="icon" type="image/x-icon" href="../asset/favicon.ico">
    <link rel="stylesheet" href="../css/Siderbar.css">
    <link rel="stylesheet" href="./css/edit_product.css">
</head>
<body>
<?php  
    include('../layouts/siderbar.php');
?>
        <div class="main-content">
            <header>
                <h1>Edit Product</h1>
            </header>
            <section class = "main_section" >
            <div class="form-container">
                <form class="form" action="edit_product.php" method="POST" >
                    <?php foreach($products as $product) { ?>

                    <input type="hidden" name="product_id" value="<?php echo $product['product_id'] ?>" />
                    
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input class="name formEntry" type="text" id="title" name="title" placeholder="Title" required pattern="^[A-Za-z0-9\s,\'\-]+$"  title="Only letters, numbers, spaces, commas, apostrophes, and hyphens are allowed"
                        value="<?php echo $product['product_name'] ?>" />
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="message formEntry" id="description" name="description" placeholder="Description" required minlength="5">
                        <?php echo $product['product_description'] ?> </textarea>
                    </div>

                    <div class="form-group">
                        <label for="price">Price</label>
                        <input class="name formEntry" type="number" id="price" name="price" placeholder="Price" step="0.01" required min="0.01"
                        value="<?php echo $product['product_price'] ?>" />
                    </div>

                    <div class="form-group">
                        <label for="stock">Stock</label>
                        <input class="name formEntry" type="number" id="stock" name="stock" placeholder="Stock" required min="0"
                        value="<?php echo $product['product_stock'] ?>" />
                    </div>

                    <div class="form-group">
                        <label for="category">Category</label>
                        <select class="formEntry" name="category" id="category" required>
                            <option class="option" value="">Select a category</option>
                            <option class="option" value="Electric">Electric</option>
                            <option class="option" value="Acoustic">Acoustic</option>
                        </select>
                    </div>

                    <button name="edit_btn" type="submit" class="submit formEntry">Edit</button>
                    <?php } ?>
                </form>
            </div>
            </section>
        </div>
    </div>
</body>
</html>
