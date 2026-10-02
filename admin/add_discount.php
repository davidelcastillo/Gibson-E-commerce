<?php include('./header.php') ?>
<?php require_once(__DIR__ . '/require_login.php'); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Discount</title>
    <link rel="icon" type="image/x-icon" href="../asset/favicon.ico">
    <link rel="stylesheet" href="../css/Siderbar.css">
    <link rel="stylesheet" href="./css/edit_product.css">
</head>
<body>
<?php  
    include('../layouts/siderbar.php');

    // Obtener todos los productos y estilos de carrocería
    $allProducts = $conn->query("SELECT * FROM products");
    $allBodys = $conn->query("SELECT * FROM body_style");
?>
<div class="main-content">
    <header>
        <h1>Add Discount</h1>
    </header>
    <section class="main_section">
        <div class="form-container">
            <form class="form" action="create_discount.php" method="POST" enctype="multipart/form-data"> 
                <div class="form-group">
                    <label for="name">Name</label>
                    <input 
                        class="name formEntry" 
                        type="text" 
                        id="name" 
                        name="name" 
                        placeholder="Name" 
                        required 
                        pattern="[A-Za-z\s]+" 
                        title="Only letters and spaces are allowed.">
                </div>
                <div class="form-group">
                    <label for="description">Description (Optional)</label>
                    <input 
                        class="name formEntry" 
                        type="text" 
                        id="description" 
                        name="description" 
                        placeholder="Description" 
                        pattern="[A-Za-z\s]+" 
                        title="Optional">
                </div>
                <div class="form-group">
                    <label for="value">Value</label>
                    <input 
                        class="formEntry" 
                        type="number" 
                        id="value" 
                        name="value" 
                        placeholder="Value" 
                        step="0.01" 
                        required>
                </div>
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input 
                        class="name formEntry" 
                        type="date" 
                        id="start_date" 
                        name="start_date" 
                        required>
                </div>
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input 
                        class="name formEntry" 
                        type="date" 
                        id="end_date" 
                        name="end_date" 
                        required>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select class="formEntry" name="status" id="status" required>
                        <option class="option" value="1">Active</option>
                        <option class="option" value="0">Inactive</option>
                    </select>
                </div>

                <!-- Sección para asociar productos -->
                <div class="form-group">
                    <label for="products">Associate Products</label>
                    <select class="formEntry" name="products[]" id="products" multiple>
                        <?php while ($product = $allProducts->fetch_assoc()) { ?>
                            <option value="<?php echo $product['product_id']; ?>">
                                <?php echo htmlspecialchars($product['product_name']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <!-- Sección para asociar estilos de carrocería -->
                <div class="form-group">
                    <label for="bodys">Associate Categories</label>
                    <select class="formEntry" name="bodys[]" id="bodys" multiple>
                        <?php while ($body = $allBodys->fetch_assoc()) { ?>
                            <option value="<?php echo $body['body_id']; ?>">
                                <?php echo htmlspecialchars($body['body_name']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <button name="create_btn" type="submit" class="submit formEntry">Add</button>
            </form>
        </div>
    </section>
</div>
</body>
</html>
