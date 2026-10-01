<?php include('./header.php') ?>
<?php require_once(__DIR__ . '/require_login.php'); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
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
                <h1>Add User</h1>
            </header>
            <section class = "main_section" >
            <div class="form-container">
                <form class="form" action="create_user.php" method="POST" enctype="multipart/form-data" > 
                <div class="form-group">
                    <label for="name">Name</label>
                        <input 
                            class="name formEntry" 
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
                                type="text" 
                                id="surname" 
                                name="surname" 
                                placeholder="Surname" 
                                required 
                                pattern="[A-Za-z\s]+" 
                                title="El apellido solo puede contener letras y espacios.">
                        </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input 
                            class="name formEntry" 
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
                            type="tel" 
                            id="phone" 
                            name="phone" 
                            placeholder="Phone" 
                            required 
                            pattern="[0-9]{10}" 
                            title="El número de teléfono debe contener exactamente 10 dígitos.">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input 
                            class="name formEntry" 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Password" 
                            required 
                            minlength="6" 
                            title="La contraseña debe tener al menos 6 caracteres.">
                    </div>
                    <button name="create_btn" type="submit" class="submit formEntry">Add</button>
                </form>
            </div>
            </section>
        </div>
    </div>
</body>
</html>
