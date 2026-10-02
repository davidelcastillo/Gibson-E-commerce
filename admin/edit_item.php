<?php include('./header.php') ?>
<?php require_once(__DIR__ . '/require_login.php'); ?>
<?php 
if( isset($_GET['item_id'])) {
    $item_id = filter_var($_GET['item_id'], FILTER_VALIDATE_INT);
    if (!$item_id) {
        header("Location: ./orders.php?error=Invalid item ID");
        exit;
    }
    $stmt = $conn->prepare("SELECT * FROM order_items WHERE item_id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $items = $stmt->get_result();
    if ($items->num_rows === 0) {
        header("Location: ./orders.php?error=Item not found");
        exit;
    }
     // Consultar IDs válidas de productos
     $productQuery = "SELECT product_id FROM products";
     $validProducts = $conn->query($productQuery);
 
}else if( isset($_POST['edit_btn'])) {
    // Validar datos del formulario
    $item_id = filter_var($_POST['item_id'], FILTER_VALIDATE_INT);
    $product_id = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);
    $product_price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
    $item_date = $_POST['date'];

    // Validar formato de fecha
    if (!$item_id || !$product_id || !$product_price ||  !preg_match('/^\d{4}-\d{2}-\d{2}$/', $item_date)) {
        header("Location: ./orders.php?error=Invalid input");
        exit;
    }
     // Validar que los IDs seleccionados existan
     $validProductQuery = "SELECT COUNT(*) FROM products WHERE product_id = ?";
     $stmt = $conn->prepare($validProductQuery);
     $stmt->bind_param("i", $product_id);
     $stmt->execute();
     $stmt->bind_result($productExists);
     $stmt->fetch();
     $stmt->close();
 
     if ($productExists === 0 ) {
         header("Location: ./orders.php?error=Invalid product ID");
         exit;
     }
    // Actualizar la orden en la base de datos
    // Actualizar el registro
    $stmt2 = $conn->prepare("UPDATE order_items
                            SET product_id = ?, product_price = ?, item_date = ?
                            WHERE item_id = ?");
    $stmt2->bind_param('iisi', $product_id, $product_price, $item_date, $item_id);
    if ($stmt2->execute()) {
        header("Location: ./orders.php?success=Order item has been updated successfully");
    } else {
        header("Location: ./orders.php?error=Error, try again");
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
    <title>Edit Order Items</title>
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
                <h1>Edit Order</h1>
            </header>
            <section class = "main_section" >
            <div class="form-container">
                <form class="form" action="edit_item.php" method="POST">
                <?php foreach ($items as $item): ?>
                    <input type="hidden" id="item_id" name="item_id" value="<?php echo htmlspecialchars($item['item_id'], ENT_QUOTES, 'UTF-8'); ?>" />
                    
                    <div class="form-group">
                        <label for="product_id">Product ID</label>
                        <select id="product_id" name="product_id" required>
                            <?php while ($product = $validProducts->fetch_assoc()): ?>
                                <option value="<?php echo $product['product_id']; ?>" <?php echo ($product['product_id'] == $item['product_id']) ? 'selected' : ''; ?>>
                                    <?php echo $product['product_id']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="price">Cost</label>
                        <input 
                            class="name formEntry" 
                            value="<?php echo htmlspecialchars($item['product_price'], ENT_QUOTES, 'UTF-8'); ?>" 
                            type="number" 
                            id="price" 
                            name="price" 
                            placeholder="Cost" 
                            min="0.01" 
                            step="0.01" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="date">Date</label>
                        <input 
                            class="name formEntry" 
                            value="<?php echo htmlspecialchars(date('Y-m-d', strtotime($item['item_date'])), ENT_QUOTES, 'UTF-8'); ?>" 
                            type="date" 
                            id="date" 
                            name="date" 
                            required
                        >
                    </div>
                    <button name="edit_btn" type="submit" class="submit formEntry">Edit</button>
                <?php endforeach; ?>
                </form>
            </div>
            </section>
        </div>
    </div>
</body>
</html>
