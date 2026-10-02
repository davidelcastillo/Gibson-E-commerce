
<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary navbarContenedorAbajo">
        <div class="container-fluid navbarContenedorArriba">
            <div class="contenedorDivImagenNavbar">
                <a href="../index.php">
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
                        <a class="nav-link" href="../html/Records.html">records</a>
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
