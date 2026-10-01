<?php include('./header.php') ?>
<?php require_once(__DIR__ . '/require_login.php'); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
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
                <h1>Add Product</h1>
            </header>
            <section class = "main_section" >
                <div class="form-container">
                    <form class="form" action="create_product.php" method="POST" enctype="multipart/form-data">
                        <!-- Columna 1 -->
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input class="formEntry" type="text" id="name" name="name" placeholder="Name" required>
                        </div>

                        <div class="form-group">
                            <label for="name_shrt">Name Short</label>
                            <input class="formEntry" type="text" id="name_shrt" name="name_shrt" placeholder="Short Name" required>
                        </div>

                        <div class="form-group">
                            <label for="description_title">Description Title</label>
                            <input class="formEntry" type="text" id="description_title" name="description_title" placeholder="Description Title" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="formEntry" id="description" name="description" placeholder="Product Description" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="price">Price</label>
                            <input class="formEntry" type="number" id="price" name="price" placeholder="Price" step="0.01" required>
                        </div>

                        <div class="form-group">
                            <label for="stock">Stock</label>
                            <input class="formEntry" type="text" id="stock" name="stock" placeholder="Stock Quantity" required>
                        </div>

                        <div class="form-group">
                            <label for="category">Category</label>
                            <select class="formEntry" name="category" id="category" required>
                                <option class="option" value="Electric">Electric</option>
                                <option class="option" value="Acoustic">Acoustic</option>
                            </select>
                        </div>

                        <!-- Columna 2 (Imágenes) -->
                        <div class="form-group">
                            <label for="img1" class="upload-btn">Upload Image 1</label>
                            <input class="formEntry" type="file" id="img1" name="img1" required onchange="updateFileName(this, 'file-name-img1')">
                            <span id="file-name-img1" class="file-name">No file selected</span>
                        </div>

                        <div class="form-group">
                            <label for="img2" class="upload-btn">Upload Image 2</label>
                            <input class="formEntry" type="file" id="img2" name="img2" required onchange="updateFileName(this, 'file-name-img2')">
                            <span id="file-name-img2" class="file-name">No file selected</span>
                        </div>

                        <div class="form-group">
                            <label for="img3" class="upload-btn">Upload Image 3</label>
                            <input class="formEntry" type="file" id="img3" name="img3" required onchange="updateFileName(this, 'file-name-img3')">
                            <span id="file-name-img3" class="file-name">No file selected</span>
                        </div>

                        <div class="form-group">
                            <label for="img4" class="upload-btn">Upload Image 4</label>
                            <input class="formEntry" type="file" id="img4" name="img4" required onchange="updateFileName(this, 'file-name-img4')">
                            <span id="file-name-img4" class="file-name">No file selected</span>
                        </div>

                        <div class="form-group">
                            <label for="img5" class="upload-btn">Upload Image 5</label>
                            <input class="formEntry" type="file" id="img5" name="img5" required onchange="updateFileName(this, 'file-name-img5')">
                            <span id="file-name-img5" class="file-name">No file selected</span>
                        </div>

                        <div class="form-group">
                            <label for="img6" class="upload-btn">Upload Landscape Image</label>
                            <input class="formEntry" type="file" id="img6" name="img6" required onchange="updateFileName(this, 'file-name-img6')">
                            <span id="file-name-img6" class="file-name">No file selected</span>
                        </div>
                        <!-- Botón de envío -->
                        <button name="create_btn" type="submit" class="submit">Add Product</button>
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
