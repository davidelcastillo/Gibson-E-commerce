<?php
session_start();
if(isset($_GET["logout"])) {
    if (isset($_SESSION["admin_logged_in"])) {
        unset( $_SESSION["admin_logged_in"] );
        unset( $_SESSION["admin_email"] );
        unset( $_SESSION["admin_name"] );
        header('location: ../admin/login.php');
        exit;
    }
}
?>