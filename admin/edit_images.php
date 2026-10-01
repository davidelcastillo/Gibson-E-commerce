<?php include('./header.php') ?>
<?php require_once(__DIR__ . '/require_login.php'); ?>
<?php 
if(isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];
    $product_name = $_GET['product_name'];
}else {
    header("Location: products.php?error=Unauthorized access");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Image</title>
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
                <h1>Edit Image</h1>
            </header>
            <section class = "main_section" >
            <div class="form-container">
                <form class="form" action="update_images.php" method="POST" enctype="multipart/form-data" >
                    
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id'] ?>" />
                    <input type="hidden" name="product_name" value="<?php echo $product['product_name'] ?>" />

                    <div class="form-group">
                        <label for="img1" class="upload-btn">Upload Image 1</label>
                        <input 
                            class="image formEntry" 
                            type="file" 
                            id="img1" 
                            name="img1" 
                            placeholder="Image 1" 
                            accept="image/png, image/jpeg, image/jpg" 
                            required
                            onchange="updateFileName(this, 'file-name-img1')"
                        >
                        <span id="file-name-img1" class="file-name">No file selected</span>
                    </div>

                    <div class="form-group">
                        <label for="img2" class="upload-btn">Upload Image 2</label>
                        <input 
                            class="image formEntry" 
                            type="file" 
                            id="img2" 
                            name="img2" 
                            placeholder="Image 2" 
                            accept="image/png, image/jpeg, image/jpg" 
                            required
                            onchange="updateFileName(this, 'file-name-img2')"
                        >
                        <span id="file-name-img2" class="file-name">No file selected</span>
                    </div>

                    <div class="form-group">
                        <label for="img3" class="upload-btn">Upload Image 3</label>
                        <input 
                            class="image formEntry" 
                            type="file" 
                            id="img3" 
                            name="img3" 
                            placeholder="Image 3" 
                            accept="image/png, image/jpeg, image/jpg" 
                            required
                            onchange="updateFileName(this, 'file-name-img3')"
                        >
                        <span id="file-name-img3" class="file-name">No file selected</span>
                    </div>

                    <div class="form-group">
                        <label for="img4" class="upload-btn">Upload Image 4</label>
                        <input 
                            class="image formEntry" 
                            type="file" 
                            id="img4" 
                            name="img4" 
                            placeholder="Image 4" 
                            accept="image/png, image/jpeg, image/jpg" 
                            required
                            onchange="updateFileName(this, 'file-name-img4')"
                        >
                        <span id="file-name-img4" class="file-name">No file selected</span>
                    </div>

                    <div class="form-group">
                        <label for="img5" class="upload-btn">Upload Image 5</label>
                        <input 
                            class="image formEntry" 
                            type="file" 
                            id="img5" 
                            name="img5" 
                            placeholder="Image 5" 
                            accept="image/png, image/jpeg, image/jpg" 
                            required
                            onchange="updateFileName(this, 'file-name-img5')"
                        >
                        <span id="file-name-img5" class="file-name">No file selected</span>
                    </div>

                    <div class="form-group">
                        <label for="img6" class="upload-btn">Upload Landscape Image</label>
                        <input 
                            class="image formEntry" 
                            type="file" 
                            id="img6" 
                            name="img6" 
                            placeholder="Image 6" 
                            accept="image/png, image/jpeg, image/jpg" 
                            required
                            onchange="updateFileName(this, 'file-name-img6')"
                        >
                        <span id="file-name-img6" class="file-name">No file selected</span>
                    </div>

                    <button name="update_images" type="submit" class="submit formEntry">Edit</button>
                </form>
            </div>
            </section>
        </div>
    </div>
    <script>
        function updateFileName(input, fileNameId) {
            const fileNameSpan = document.getElementById(fileNameId);
            if (input.files && input.files[0]) {
                fileNameSpan.textContent = input.files[0].name;
            } else {
                fileNameSpan.textContent = "No file selected";
            }
        }
    </script>
</body>
</html>
