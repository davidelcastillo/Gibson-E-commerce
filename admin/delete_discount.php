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

if(isset($_GET['promo_id'])) {
    $promo_id = $_GET['promo_id'];
    
    // Llamar al procedimiento almacenado para mover y borrar el pedido
    $stmt = $conn->prepare("DELETE FROM promotions WHERE promo_id = ?");
    $stmt->bind_param('i', $promo_id);
    if($stmt->execute()) {
        header("Location: discount.php?success=Deleted successfully");
    } else {
        header("Location: discount.php?error=Couldn't delete order");
    }
}


?>