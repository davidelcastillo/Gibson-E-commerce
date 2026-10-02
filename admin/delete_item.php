<?php 
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    include('../server/connection.php')
?>
<?php 
include('../admin/header.php');
if (!isset($_SESSION['admin_logged_in'])) {
    header('location: ../admin/login.php');
    exit();
}

if(isset($_GET['item_id'])) {
    $item_id = $_GET['item_id'];
    
    // Llamar al procedimiento almacenado para mover y borrar el pedido
    $stmt = $conn->prepare("DELETE FROM order_items WHERE item_id = ?");
    $stmt->bind_param('i', $item_id);
    if($stmt->execute()) {
        header("Location: orders.php?success=Deleted successfully");
    } else {
        header("Location: orders.php?error=Couldn't delete order");
    }
}


?>