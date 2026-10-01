<?php
include('./header.php');
require_once(__DIR__ . '/require_login.php');

if (isset($_POST['create_btn'])) {
    require_once('../server/connection.php'); // Asegúrate de incluir la conexión

    $name = $_POST['name'];
    $description = $_POST['description'] ?? null;
    $type = "percentage";
    $value = $_POST['value'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = $_POST['status'];
    $product_ids = $_POST['products'] ?? [];
    $bodys_ids = $_POST['bodys'] ?? [];

    // Validar formato de fecha
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
        header("Location: ./add_discount.php?error=Invalid date format");
        exit;
    }

    // Insertar la promoción en la tabla promotions
    $stmt = $conn->prepare("INSERT INTO promotions (name, description, discount_type, discount_value, start_date, end_date, active) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Error en la preparación de la consulta: " . $conn->error);
    }

    $stmt->bind_param("sssdssi", $name, $description, $type, $value, $start_date, $end_date, $status);
    
    if ($stmt->execute()) {
        $promo_id = $stmt->insert_id;
        $stmt->close(); // Cerrar statement después de la ejecución

        // Si no hay productos ni categorías, cancelar
        if (empty($product_ids) && empty($bodys_ids)) {
            $conn->query("DELETE FROM promotions WHERE promo_id = $promo_id"); // Eliminar promo huérfana
            header("Location: ./add_discount.php?error=No products or categories selected");
            exit;
        }

        // Insertar asociaciones en promotion_targets
        $stmt2 = $conn->prepare("INSERT INTO promotion_targets (promo_id, target_type, target_id) VALUES (?, ?, ?)");
        if (!$stmt2) {
            die("Error en la preparación de la consulta de asociación: " . $conn->error);
        }

        // Insertar productos
        foreach ($product_ids as $product_id) {
            $target_type = 'product';
            $stmt2->bind_param('isi', $promo_id, $target_type, $product_id);
            $stmt2->execute();
        }

        // Insertar categorías
        foreach ($bodys_ids as $body_id) {
            $target_type = 'category';
            $stmt2->bind_param('isi', $promo_id, $target_type, $body_id);
            $stmt2->execute();
        }

        $stmt2->close(); // Cerrar statement

        header("Location: ./discount.php?success=Discount added successfully");
        exit;
    } else {
        header("Location: ./add_discount.php?error=Error adding discount");
        exit;
    }
} else {
    header("Location: ./add_discount.php?error=Unauthorized access");
    exit;
}
?>
