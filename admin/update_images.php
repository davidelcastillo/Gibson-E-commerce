<?php
require_once(__DIR__ . '/require_login.php');
include('../server/connection.php');
if (isset($_POST['update_images'])){
    $product_name = $_POST['product_name'];
    $product_id = $_POST['product_id'];

    // Allowed file extensions
    $allowedExtensions = ['jpeg', 'jpg', 'png'];

    // Verify each image
    $images = ['img1', 'img2', 'img3', 'img4', 'img5', 'img6'];
    $imageNames = []; // Array to store the names of the images to be moved

    foreach ($images as $index => $imageKey) {
        if (isset($_FILES[$imageKey]) && $_FILES[$imageKey]['error'] == 0) {
            $fileTmp = $_FILES[$imageKey]['tmp_name'];
            $fileName = $_FILES[$imageKey]['name'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            // Validate file extension
            if (!in_array($fileExt, $allowedExtensions)) {
                header("Location: products.php?error=The file for {$imageKey} is not a valid format (allowed: jpeg, jpg, png)");
                exit;
            }

            // Generate the new filename
            $newFileName = $product_name . ($index + 1) . '.' . $fileExt;
            $imageNames[$imageKey] = $newFileName;

            // Move the file to the upload directory
            $uploadPath = "../img/" . $newFileName;
            if (!move_uploaded_file($fileTmp, $uploadPath)) {
                header("Location: products.php?error=Failed to move the file for {$imageKey}");
                exit;
            }
        } else {
            header("Location: products.php?error=File for {$imageKey} not received or upload error occurred");
            exit;
        }
    }
    $stmt = $conn->prepare("UPDATE products SET product_image_stand=?, product_image2=?, product_image3=?,
                                                    product_image4=?, product_image5=?, product_image1=? 
                                    WHERE product_id=?");
    $stmt->bind_param(
        'ssssssi', 
        $image_names['img1'], $image_names['img2'], $image_names['img3'], 
        $image_names['img4'], $image_names['img5'], $image_names['img6'], 
        $product_id
    );
    if ($stmt->execute()) {
    header('location: products.php?images_updated=Images have been updated successfully');
    }else{
    header('location: products.php?images_failed-Error occured, try again' );
    }
} else {
    header("Location: products.php?error=Unauthorized access");
}
