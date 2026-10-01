<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

include("../server/connection.php");

 // if user has already register -> account page
if (isset ($_SESSION['logged_in'])) {
    header('location: Account.php');
    exit;
}

if (isset($_POST["register"])) {

    $name = $_POST['name'];
    $email= $_POST['email'];
    $password = $_POST['password'];
    $phone = $_POST['phone'];


    if (strlen($password) < 6) {
        header('location: Register.php?error=password must be at least 6 characters');
    }

    // if there is no error
    else {
        //check whether there is a user wht this email o phone or not
        $stmp1 = $conn->prepare('SELECT count(*) FROM users WHERE user_email=?');
        $stmp1->bind_param('s', $email);
        $stmp1->execute();
        $stmp1->bind_result($num_rows);
        $stmp1->store_result();
        $stmp1->fetch();
    
        // if there is a user wht already register with the email or phone
        if ($num_rows != 0) {
    
            header('location: Register.php?error=user with this email or phone already exists');
    
        }else {
            //create a new user
            $stmp = $conn->prepare('INSERT INTO users (user_name,user_email,user_phone,user_password)
                                    VALUES (?,?,?,?)');
            
            $stmp->bind_param('ssss', $name,$email,$phone, md5($password));
            
            
            if($stmp->execute()){
                $user_id = $stmp->insert_id;
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_phone'] = $phone;
                $_SESSION['logged_in'] = true;

                // send confirmation email
                $user_email = $_SESSION['user_email'];
                $message = 'Your Account has been successfully created. Lets Rock!!' ;

                require '../phpmailer/src/Exception.php';
                require '../phpmailer/src/PHPMailer.php';
                require '../phpmailer/src/SMTP.php';

                require_once __DIR__ . '/../config/env.php';

                // SMTP settings come from the environment (see .env.example); no
                // credential is hardcoded here. Port and secure mode keep their
                // historical defaults (465 / ssl) when not provided.
                $smtpHost   = getenv('SMTP_HOST');
                $smtpPort   = getenv('SMTP_PORT');
                $smtpSecure = getenv('SMTP_SECURE');
                $smtpUser   = getenv('SMTP_USER');
                $smtpPass   = getenv('SMTP_PASS');
                // MAIL_TO is intentionally not read here: these notifications go
                // to the registered user, not to the contact-form recipient.
                $mailFrom   = getenv('MAIL_FROM');

                if ($smtpPort === false || $smtpPort === '') { $smtpPort = 465; }
                if ($smtpSecure === false || $smtpSecure === '') { $smtpSecure = 'ssl'; }

                if ($smtpHost === false || $smtpHost === ''
                    || $smtpUser === false || $smtpUser === ''
                    || $smtpPass === false || $smtpPass === ''
                    || $mailFrom === false || $mailFrom === '') {
                    die('Mail configuration is incomplete: set SMTP_HOST, SMTP_USER, SMTP_PASS and MAIL_FROM.');
                }

                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = $smtpHost;
                $mail->SMTPAuth = true;
                $mail->Username = $smtpUser;
                $mail->Password = $smtpPass;
                $mail->SMTPSecure = $smtpSecure;
                $mail->Port = (int) $smtpPort;

                $mail->Sender = $mailFrom;
                $mail->setFrom($mailFrom);

                $mail->addAddress($user_email);

                $mail->isHTML(true);

                $mail->Subject = 'Welcome to Gibson';

                $mail->Body = $message;

                $mail->send();

                header('location: Account.php?log_success=You register succesfully');
            }else{
    
                header('location: Register.php?error=Cold´t create account');
    
            }

        } 
    } 
}

?>


<!doctype html>
<html lang="en">

<head>
    <title>Sign Up</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" type="image/x-icon" href="../asset/favicon.ico">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v2.1.9/css/unicons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/Register.css">
    <link rel="stylesheet" href="../css/Header.css">
    <link rel="stylesheet" href="../css/Footer.css">

</head>
<?php  
    include('../layouts/header-php.php');
?>

    <section class="main_section">
        <div class="title">
            <h1>
                Sign Up
            </h1>
        </div>
        <div class="wrapper">
            <form method="POST" action="Register.php" class="form">
                
                <div class="form-group">
                    <p>Name</p>
                    <input class="inp-main" type="text" class="form-style" name="name">
                    <i class="input-icon uil uil-user"></i>
                </div>
                <div class="form-group mt-2">
                    <p>Phone</p>
                    <input class="inp-main" type="tel" class="form-style" name="phone">
                    <i class="input-icon uil uil-phone"></i>
                </div>
                <div class="form-group mt-2">
                    <p>Email</p>
                    <input class="inp-main" type="email" class="form-style" name="email">
                    <i class="input-icon uil uil-at"></i>
                </div>
                <div class="form-group mt-2">
                    <p>Password</p>
                    <input class="inp-main" type="password" class="form-style" name="password">
                    <i class="input-icon uil uil-lock-alt"></i>
                </div>
                <p style="color:red;" ><?php  if(isset($_GET['error'])) {$_GET['error']; }?> </p>
                <input type="checkbox" class="termsConditions" value="Term">
                <label style="color: rgb(255, 254, 254)" for="terms"> I Accept the <span style="color: #a1a0a0">Terms of Use</span> & <span style="color: #a1a0a0">Privacy Policy</span>.</label><br>
                <input class="btn submit" type="submit" name="register" value="Sing Up" />
                <p style="color:red"><?php if(isset($_GET['error'])){echo $_GET['error'];} ?></p>
                <a href="Login.php">
                    <p class="lst-p" >Already have an account? Login</p>
                </a>
            </form> 
        </div>
    </section>
<?php  
    include('../layouts/footer-php.php');
?>