<?php include('./header.php'); ?>
<?php require_once(__DIR__ . '/require_login.php'); ?>
<?php 
if( isset($_GET['promo_id'])) {
    $promo_id = $_GET['promo_id'];
    $stmt = $conn->prepare("SELECT * FROM promotions WHERE promo_id = ?");
    $stmt->bind_param("i", $promo_id);
    $stmt->execute();
    $promotions = $stmt->get_result();

    // Obtener asociaciones existentes
    $associatedTargets = $conn->prepare("SELECT target_type, target_id FROM promotion_targets WHERE promo_id = ?");
    $associatedTargets->bind_param("i", $promo_id);
    $associatedTargets->execute();
    $result = $associatedTargets->get_result();
    $associated = ['product' => [], 'body_style' => []];
    while ($row = $result->fetch_assoc()) {
        $associated[$row['target_type']][] = $row['target_id'];
    }

    // Obtener todos los productos y categorías
    $allProducts = $conn->query("SELECT * FROM products");
    $allBodys = $conn->query("SELECT * FROM body_style");

} else if( isset($_POST['edit_btn'])) {
    $promo_id = $_POST['promo_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $type = "percentage";
    $value = $_POST['value'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = $_POST['status'];
    $product_ids = $_POST['products'] ?? [];
    $bodys_ids = $_POST['bodys'] ?? [];

    // Validar que las fechas sean correctas
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
        header("Location: ./discount.php?error=Invalid date format");
        exit;
    }

    // Actualizar la promoción
    $stmt2 = $conn->prepare('UPDATE promotions 
                             SET name = ?, description = ?, discount_type = ?, discount_value = ?, start_date = ?, end_date = ?, active = ?
                             WHERE promo_id = ?'); 
    $stmt2->bind_param('sssissii', $name, $description, $type, $value, $start_date, $end_date, $status, $promo_id);
    if ($stmt2->execute()) {
        // Actualizar asociaciones
        $conn->query("DELETE FROM promotion_targets WHERE promo_id = $promo_id");
        $stmt3 = $conn->prepare("INSERT INTO promotion_targets (promo_id, target_type, target_id) VALUES (?, ?, ?)");
        foreach ($product_ids as $product_id) {
            $target_type = 'product';
            $stmt3->bind_param('isi', $promo_id, $target_type, $product_id);
            $stmt3->execute();
        }
        foreach ($bodys_ids as $body_id) {
            $target_type = 'body';
            $stmt3->bind_param('isi', $promo_id, $target_type, $body_id);
            $stmt3->execute();
        }
        header("Location: ./discount.php?success=Promotion updated successfully");
    } else {
        header("Location: ./discount.php?error=Error updating promotion");
    }
} else {
    header("Location: discount.php?error=Unauthorized access");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Promotion</title>
    <link rel="icon" type="image/x-icon" href="../asset/favicon.ico">
    <link rel="stylesheet" href="../css/Siderbar.css">
    <link rel="stylesheet" href="./css/edit_product.css">
</head>
<body>
<?php include('../layouts/siderbar.php'); ?>
<div class="main-content">
    <header>
        <h1>Edit Promotion</h1>
    </header>
    <section class="main_section">
        <div class="form-container">
            <form class="form" action="edit_discount.php" method="POST">
                <?php foreach ($promotions as $promo) { ?>

                <input type="hidden" name="promo_id" value="<?php echo $promo['promo_id']; ?>" />

                <div class="form-group">
                    <label for="name">Name</label>
                    <input 
                        class="name formEntry" 
                        value="<?php echo htmlspecialchars($promo['name']); ?>" 
                        type="text" 
                        id="name" 
                        name="name" 
                        placeholder="Name" 
                        required>
                </div>

                <div class="form-group">
                    <label for="description">Description (Optional)</label>
                    <input 
                        class="name formEntry" 
                        value="<?php echo htmlspecialchars($promo['description']); ?>" 
                        type="text" 
                        id="description" 
                        name="description" 
                        placeholder="Description">
                </div>

                <div class="form-group">
                    <label for="value">Value</label>
                    <input 
                        class="formEntry" 
                        type="number" 
                        id="value" 
                        name="value" 
                        value="<?php echo htmlspecialchars($promo['discount_value']); ?>" 
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
                        value="<?php echo htmlspecialchars($promo['start_date']); ?>" 
                        required>
                </div>

                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input 
                        class="name formEntry" 
                        type="date" 
                        id="end_date" 
                        name="end_date" 
                        value="<?php echo htmlspecialchars($promo['end_date']); ?>" 
                        required>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select class="formEntry" name="status" id="status" required>
                        <option value="1" <?php if ($promo['active'] == 1) echo 'selected'; ?>>Active</option>
                        <option value="0" <?php if ($promo['active'] == 0) echo 'selected'; ?>>Inactive</option>
                    </select>
                </div>

                <!-- Nueva sección para productos -->
                <div class="form-group">
                    <label for="products">Associate Products</label>
                    <select class="formEntry" name="products[]" id="products" multiple>
                        <?php while ($product = $allProducts->fetch_assoc()) { 
                            $selected = in_array($product['product_id'], $associated['product']) ? 'selected' : ''; ?>
                            <option value="<?php echo $product['product_id']; ?>" <?php echo $selected; ?>>
                                <?php echo htmlspecialchars($product['product_name']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <!-- Nueva sección para categorías -->
                <div class="form-group">
                    <label for="categories">Associate Categories</label>
                    <select class="formEntry" name="bodys[]" id="bodys" multiple>
                        <?php while ($body = $allBodys->fetch_assoc()) { 
                            $selected = in_array($body['body_id'], $associated['body_style']) ? 'selected' : ''; ?>
                            <option value="<?php echo $body['body_id']; ?>" <?php echo $selected; ?>>
                                <?php echo htmlspecialchars($body['body_name']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <button name="edit_btn" type="submit" class="submit formEntry">Edit</button>
                <?php } ?>
            </form>
        </div>
    </section>
</div>
</body>
</html>
