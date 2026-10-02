<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Gibson</title>
    <link rel="stylesheet" href="">
    <link rel="icon" type="image/x-icon" href="./asset/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="./css/paginaPrincipal.css">
    <link rel="stylesheet" href="../css/Header.css">
    <link rel="stylesheet" href="../css/Footer.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary navbarContenedorAbajo">
        <div class="container-fluid navbarContenedorArriba">
            <div class="contenedorDivImagenNavbar">
                <a href="./index.php">
                    <img src="../img/logoGibson.png" alt="logo de Gibson" class="logoNavbar">
                </a>  
            </div>
            <div class="collapse navbar-collapse contenedorInfoNavbar" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 contenedorLinksNavbar">
                    <li class="nav-item">
                        <a class="nav-link" href="../php/ElectricGuitars.php">electric</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../php/AcousticGuitars.php">Acustic</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../html/AboutUs.html">about us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../html/Guitarist.html">guitarist</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../html/ContactUs.html">contact us</a>
                    </li>
                </ul>
            </div>
            <div class="d-flex formNavbar">
                <form class="d-flex" role="search" method="POST" action="../php/Search.php" >
                    <input class="form-control me-2 inputSearchNavbar" type="search" aria-label="Search" name="search" id="searchInput">
                    <button class="search-btn" type="submit" id="searchIcon" ><i class="bi bi-search buttonNavbarRight"></i></button>
                </form>
                <a href="../php/Login.php" class="btn" type="submit"><i class="bi bi-person buttonNavbarRight"></i> </a>
                <div class="cart-container">
                    <a class="btn cart-btn" type="submit" href="../php/Cart.php" ><i class="bi bi-cart buttonNavbarRight"> </i>
                        <?php if(isset($_SESSION['quantity']) && $_SESSION['quantity'] != 0) { ?> 
                            <span class="cart-quantity" ><?php echo $_SESSION['quantity']; ?></span> 
                        <?php }?>
                    </a>
                </div>   
            </div>
            <button class="navbar-toggler menuHamburguesaButton" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>

    <?php  
        if(isset($_GET['email_snt'])) { 
    ?>
        <script>
            Swal.fire({
                position: "top-end",
                icon: "success",
                title: 'Sent Successfully' ,
                showConfirmButton: false,
                timer: 1500,
                color: "#6f6d6b",
                background: "#0f0e0b",
            });
        </script>

    <?php  
           unset($_SESSION['email_snt']);
        } 
    ?>

    <section class="main_section">
        <img src="./img/guitar_hover.png" class="main_img">
        <div class="title">
            <h1>Welcome</h1>
        </div>
        <div class="guitarras">
            <div class="slider">
                <img src="./img/guitarra_expl/explorer_negra.png" alt="" />
                <img src="./img/guitarra_expl/explorer_white.png"  alt="" />
                <img src="./img/guitarra_sg/sg_angus.png" alt="" />
                <img src="./img/guitarra_sg/sg_negra.png" alt="" />
                <img src="./img/guitarra_v/v_negra.png" alt="" />
                <img src="./img/guitarras_lp/lp_pink.png" alt="" />
                <img src="./img/guitarras_lp/lp_purple.png"  alt="" />
                <img src="./img/guitarras_lp/lp_slash1.png"  alt="" />
                <img src="./img/guitarras_lp/lp_slash3.png"  alt="" />
                <img src="./img/guitarras_lp/lp_slash7.png"  alt="" />
            </div>
        </div>

        <div class="cont_p">
            <p>
                Gibson Inc., the world’s most iconic guitar brand, has shaped the sounds of generations of musicians and music lovers across genres for more than 100 years. Founded in 1894 and headquartered in Nashville, TN, Gibson has a legacy of world-class craftsmanship, legendary music partnerships, and progressive product evolution that is unrivaled among musical instrument companies.
                <br>
                The Gibson portfolio includes Gibson, the number one guitar brand, as well as many of the most beloved and recognizable music brands, including Epiphone, Kramer, Steinberger, MESA/Boogie, and the Gibson Pro Audio division, KRK Systems. Gibson is dedicated to quality, innovation, and sound excellence so that music lovers for generations to come will continue to experience music shaped by Gibson. Learn more at Gibson.com and follow us on Twitter, Facebook and Instagram.
            </p>
        </div>
    </section>
    <footer class="footerFatherContainer">
        <div class="grupo">
            <section class="footerImageContainer box">
                <div class="Gibsonfooter">
                    <img src="./img/logoGibson.png" alt="Logo Gibson">
                </div>
            </section>
            <section class="footerInfoContainer box">
                <div class="containerLogos">
                    <a href="https://www.facebook.com/GibsonES/?brand_redir=98534165717" target="_blank">
                        <i  class="bi bi-facebook"></i>
                    </a>
                    <a href="https://twitter.com/Gibson" target="_blank">
                        <i class="bi bi-twitter"></i>
                    </a>
                    <a href="https://www.instagram.com/gibsonguitar" target="_blank">
                        <i class="bi bi-instagram"></i>
                    </a>
                </div>
                <div class="footerEtiquetasA">
                    <a href="./html/ContactUs.html">Contact</a>
                    <a href="./php/Login.php">Sign in</a>
                    <a href="./html/AboutUs.html">About us</a>
                    <a href="./index.php">Menu</a>
                </div>
                <div class="footerMail">
                    <i class="bi bi-envelope"></i>
                </div>
            </section>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous">
    </script>
</body>

</html>