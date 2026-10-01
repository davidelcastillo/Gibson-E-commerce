<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . '/require_login.php');
include('../server/connection.php');
if (isset($_POST['create_btn'])) {
    $error = 0; // Error flag
    // Validar el nombre del producto
    if (empty($_POST['name']) || !preg_match("/^[A-Za-z0-9\s,\'\-]+$/", $_POST['name'])) {
        $error = 1;
        header("Location: products.php?error=Invalid product name");
        exit;
    }
    // Validar el nombre corto
    if (empty($_POST['name_shrt']) || strlen($_POST['name_shrt']) > 50) {
        $error = 1;
        header("Location: products.php?error=Invalid short name (max 50 characters)");
        exit;
    }
    // Validar el título de descripción
    if (empty($_POST['description_title']) || !preg_match("/^[A-Za-z0-9\s,\'\-]+$/", $_POST['description_title'])) {
        $error = 1;
        header("Location: products.php?error=Invalid description title");
        exit;
    }
    // Validar la descripción
    if (empty($_POST['description']) || strlen($_POST['description']) < 5) {
        $error = 1;
        header("Location: products.php?error=Description must be at least 5 characters long");
        exit;
    }
    // Validar el precio
    if (empty($_POST['price']) || !preg_match("/^\d+(\.\d{1,2})?$/", $_POST['price'])) {
        $error = 1;
        header("Location: products.php?error=Invalid price format");
        exit;
    }
    // Validar el stock
    if (empty($_POST['stock']) || !preg_match("/^[0-9]+$/", $_POST['stock'])) {
        $error = 1;
        header("Location: products.php?error=Stock must be a positive integer");
        exit;
    }
    // Validar la categoría
    $allowedCategories = ['Electric', 'Acoustic'];
    if (empty($_POST['category']) || !in_array($_POST['category'], $allowedCategories)) {
        $error = 1;
        header("Location: products.php?error=Invalid category");
        exit;
    }
    // Validar imágenes
    $allowedExtensions = ['jpeg', 'jpg', 'png'];
    $images = ['img1', 'img2', 'img3', 'img4', 'img5', 'img6'];
    $imageNames = [];
    foreach ($images as $index => $imageKey) {
        if (isset($_FILES[$imageKey]) && $_FILES[$imageKey]['error'] == 0) {
            $fileTmp = $_FILES[$imageKey]['tmp_name'];
            $fileName = $_FILES[$imageKey]['name'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if (!in_array($fileExt, $allowedExtensions)) {
                header("Location: products.php?error=Invalid format for image {$imageKey} (allowed: jpeg, jpg, png)");
                exit;
            }
            $newFileName = $_POST['name'] . ($index + 1) . '.' . $fileExt;
            $imageNames[$imageKey] = $newFileName;
            $uploadPath = "../img/" . $newFileName;
            if (!move_uploaded_file($fileTmp, $uploadPath)) {
                header("Location: products.php?error=Failed to upload image {$imageKey}");
                exit;
            }
        } else {
            header("Location: products.php?error=Image {$imageKey} not uploaded");
            exit;
        }
    }
    // Insertar datos en la base de datos
    $stmt = $conn->prepare(
        "INSERT INTO products (product_name, product_name_shrt, product_description_title, 
        product_description, product_price, product_stock, product_category, product_image_stand, 
        product_image2, product_image3, product_image4, product_image5, product_image1) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        'ssssdsissssss',
        $_POST['name'], $_POST['name_shrt'], $_POST['description_title'],
        $_POST['description'], $_POST['price'], $_POST['stock'], $_POST['category'],
        $imageNames['img1'], $imageNames['img2'], $imageNames['img3'], $imageNames['img4'], 
        $imageNames['img5'], $imageNames['img6']
    );

    if ($stmt->execute()) {
        header("Location: products.php?success=Product added successfully");
    } else {
        header("Location: products.php?error=Failed to add product");
    }
    exit;
} else {
    header("Location: products.php?error=Unauthorized access");
    exit;
}
?>