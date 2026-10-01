<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . '/require_login.php');
include('../server/connection.php');

if (isset($_POST['create_btn'])) {
    $error = 0;
    if (empty($_POST['name']) || !preg_match("/^[A-Za-z\s]+$/", $_POST['name'])) {
        $error = 1;
    }
    if (empty($_POST['surname']) || !preg_match("/^[A-Za-z\s]+$/", $_POST['surname'])) {
        $error = 1;
    }
    // Validar el correo
    if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 1;
    }
    // Validar el teléfono
    if (empty($_POST['phone']) || !preg_match("/^[0-9]{10}$/", $_POST['phone'])) {
        $error = 1; 
    }
    // Validar la contraseña
    if (empty($_POST['password']) || strlen($_POST['password']) < 6) {
        $error = 1;
    }
    if ($error == 1) {
        header('location: users.php?error=Invalid data' );
    } else {
        $user_name = $_POST['name'];
        $user_surname = $_POST['surname'];
        $user_email = strtolower($_POST['email']);
        $user_phone = $_POST['phone'];
        $user_password = $_POST['password'];

        // Verify if the email already exists
        $checkQuery = "SELECT COUNT(*) FROM users WHERE user_email = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param('s', $user_email);
        $stmt->execute();
        $stmt->bind_result($emailExists);
        $stmt->fetch();
        $stmt->close();

        if ($emailExists > 0) {
            header('location: users.php?error=This email already exists' );
        } else {
            //create a new user
            $stmt = $conn->prepare("INSERT INTO users (user_name,user_surname, user_email, user_phone, user_password)
                                        VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('ssis', $user_name,$user_surname, $user_email,$user_phone,md5($user_password));
            if ($stmt->execute()) {
            header('location: users.php?success=User has been created successfully');
            }else{
            header('location: users.php?error=Error occured, try again');
            }
        }
    }
} else {
    header("Location: products.php?error=Unauthorized access");
    exit;
}
?>