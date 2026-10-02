<?php 
    session_start();
    include('../server/connection.php')
?>
<?php 
include('../admin/header.php');
if (!isset($_SESSION['admin_logged_in'])) {
    header('location: ../admin/login.php');
    exit();
}

if(isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];
    $stmt = $conn->prepare("CALL mov_n_delete_product(?);");
    $stmt->bind_param('i', $product_id);
    if($stmt->execute()) {
        header( "Location: products.php?success=Deleted successfully" );
    } else {
        header( "Location: products.php?error=Couldn't deleated product" );
    }
}else {
    header("Location: products.php?error=Unauthorized access");
    exit;
}

?>