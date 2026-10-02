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

if(isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $stmt2 = $conn->prepare("CALL mov_n_delete_user(?)");
    $stmt2->bind_param('i', $user_id);
    if($stmt2->execute()) {
        header("Location: users.php?success=Deleted successfully");
    } else {
        header("Location: users.php?error=Couldn't delete order");
    }
} else {
    header("Location: products.php?error=Unauthorized access");
    exit;
}


?>