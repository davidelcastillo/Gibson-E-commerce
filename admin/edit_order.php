<?php include('./header.php') ?>
<?php require_once(__DIR__ . '/require_login.php'); ?>
<?php 
$stmt = $conn->prepare("SELECT user_id FROM users");
$stmt->execute();
$result = $stmt->get_result();
$user_ids = [];
while ($row = $result->fetch_assoc()) {
    $user_ids[] = $row['user_id'];
}
if( isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $orders =  $stmt->get_result();
    
}else if( isset($_POST['edit_btn'])) {

    $order_id = $_POST['order_id'];
    $order_status = $_POST['status'];
    $order_cost = $_POST['cost'];
    $order_date = $_POST['date'];
    $user_address = $_POST['address'];
    $user_id = $_POST['user_id']; 

    // Validar el estado
    $validStatuses = ['not paid', 'paid', 'shipped', 'delivered'];
    if (!in_array($order_status, $validStatuses)) {
        header("Location: ./orders.php?error=Invalid status value.");
        exit;
    }
    // Validar el costo
    if (!is_numeric($order_cost) || $order_cost <= 0) {
        header("Location: ./orders.php?error=Invalid cost value.");
        exit;
    }
    // Validar la fecha
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $order_date)) {
        header("Location: ./orders.php?error=Invalid date format.");
        exit;
    }
    //Validar la dirección
    if (empty(trim($user_address))) {
        header("Location: ./orders.php?error=Address is required.");
        exit;
    }
      // Validar que el ID del usuario sea válido
    if (!in_array($user_id, $user_ids)) {
        header("Location: ./orders.php?error=Invalid user ID.");
        exit;
    }
    // Actualizar la orden en la base de datos
    $stmt2 = $conn->prepare('UPDATE orders 
    SET order_status = ?, order_cost = ?, order_date = ?, user_address = ?, user_id = ?
    WHERE order_id = ?'); 
$stmt2->bind_param('sissii', $order_status, $order_cost, $order_date, $user_address, $user_id, $order_id);

if ($stmt2->execute()) {
        header("Location: ./orders.php?success=Order has been updated successfully");
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
    <title>Edit Order</title>
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
                <form class="form" action="edit_order.php" method="POST">
                    <?php foreach($orders as $order) { 
                        $order_date = isset($order['order_date']) ? date('Y-m-d', strtotime($order['order_date'])) : '';
                    ?>
                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>" />
                    
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="formEntry" required>
                            <option value="not paid" <?php if($order['order_status'] == 'not paid') echo 'selected'; ?>>Not Paid</option>
                            <option value="paid" <?php if($order['order_status'] == 'paid') echo 'selected'; ?>>Paid</option>
                            <option value="shipped" <?php if($order['order_status'] == 'shipped') echo 'selected'; ?>>Shipped</option>
                            <option value="delivered" <?php if($order['order_status'] == 'delivered') echo 'selected'; ?>>Delivered</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="cost">Cost</label>
                        <input 
                            class="name formEntry" 
                            value="<?php echo $order['order_cost']; ?>" 
                            type="number" 
                            id="cost" 
                            name="cost" 
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
                            value="<?php echo $order_date; ?>" 
                            type="date" 
                            id="date" 
                            name="date" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <input 
                            class="name formEntry" 
                            value="<?php echo $order['user_address']; ?>" 
                            type="text" 
                            id="address" 
                            name="address" 
                            placeholder="Address" 
                            required
                        >
                    </div>

                    <div class="form-group">
                    <label for="user_id">User ID</label>
                    <select name="user_id" id="user_id" class="formEntry" required>
                        <?php foreach ($user_ids as $id) { ?>
                            <option value="<?php echo $id; ?>" 
                                    <?php if($order['user_id'] == $id) echo 'selected'; ?>>
                                <?php echo $id; ?>
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
    </div>
    <script>
        document.querySelector('form').addEventListener('submit', function(event) {
            const errors = [];
            // Validar el estado
            const status = document.getElementById('status').value;
            const validStatuses = ['not paid', 'paid', 'shipped', 'delivered'];
            if (!validStatuses.includes(status)) {
                errors.push('Invalid status selected.');
            }
            // Validar el costo
            const cost = document.getElementById('cost').value;
            if (!cost || isNaN(cost) || cost <= 0) {
                errors.push('Cost must be a positive number.');
            }
            // Validar la fecha
            const date = document.getElementById('date').value;
            if (!date || !/^\d{4}-\d{2}-\d{2}$/.test(date)) {
                errors.push('Invalid date format.');
            }
            // Validar la dirección
            const address = document.getElementById('address').value;
            if (!address.trim()) {
                errors.push('Address is required.');
            }

            const userId = document.getElementById('user_id').value;
            if (!userId || isNaN(userId) || userId <= 0) {
                errors.push('User ID must be a positive number.');
            }
            // Mostrar errores si los hay
            if (errors.length > 0) {
                event.preventDefault();
                alert(errors.join('\n'));
            }
        });
    </script>
</body>
</html>
