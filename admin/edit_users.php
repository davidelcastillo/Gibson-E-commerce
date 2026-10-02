<?php include('./header.php') ?>
<?php require_once(__DIR__ . '/require_login.php'); ?>
<?php 
if( isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $users =  $stmt->get_result();
}else if( isset($_POST['edit_btn'])) {
    $user_id = $_POST['user_id'];
    $user_name = $_POST['name'];
    $user_surname = $_POST['surname'];
    $user_email = strtolower($_POST['email']);
    $user_phone = $_POST['phone'];
    // Verify if the email already exists
    $checkQuery = "SELECT COUNT(*) FROM users WHERE user_email = ? AND user_id != ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param('si', $user_email, $user_id);
    $stmt->execute();
    $stmt->bind_result($emailExists);
    $stmt->fetch();
    $stmt->close();
    if ($emailExists > 0) {
        header('location: users.php?error=This email already exists' );
    } else {
        $stmt2 = $conn->prepare('UPDATE users
                                        SET user_name = ?, user_surname = ?, user_email = ?, user_phone = ?
                                        WHERE user_id = ?'); 
        $stmt2->bind_param('sssii', $user_name,$user_surname, $user_email,$user_phone,$user_id);
        if ($stmt2->execute()) {
            header("Location: ./users.php?success=User has been updated successfully");
        }else {
            header("Location: ./users.php?error=Error, try again");
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
    <title>Edit User</title>
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
                <h1>Edit User</h1>
            </header>
            <section class = "main_section" >
            <div class="form-container">
                <form class="form" action="edit_users.php" method="POST" >
                    <?php foreach($users as $user) { ?>

                    <input type="hidden" name="user_id" value="<?php echo $user['user_id'] ?>" />
                    
                    <div class="form-group">
                    <label for="name">Name</label>
                        <input 
                            class="name formEntry" 
                            value="<?php echo htmlspecialchars($user['user_name']); ?>" 
                            type="text" 
                            id="name" 
                            name="name" 
                            placeholder="Name" 
                            required 
                            pattern="[A-Za-z\s]+" 
                            title="El nombre solo puede contener letras y espacios.">
                    </div>

                    <div class="form-group">
                        <label for="name">Surname</label>
                            <input 
                                class="name formEntry" 
                                value="<?php echo htmlspecialchars($user['user_surname']); ?>" 
                                type="text" 
                                id="surname" 
                                name="surname" 
                                placeholder="surname" 
                                required 
                                pattern="[A-Za-z\s]+" 
                                title="El apellido solo puede contener letras y espacios.">
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input 
                            class="name formEntry" 
                            value="<?php echo htmlspecialchars($user['user_email']); ?>" 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="Email" 
                            required 
                            title="Por favor, ingrese un correo válido.">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input 
                            class="name formEntry" 
                            value="<?php echo htmlspecialchars($user['user_phone']); ?>" 
                            type="tel" 
                            id="phone" 
                            name="phone" 
                            placeholder="Phone" 
                            required 
                            pattern="[0-9]{10}" 
                            title="El número de teléfono debe contener 10 dígitos.">
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
