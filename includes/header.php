<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER["PHP_SELF"]);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Authentic Perfume</title>

    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=15">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>

<body>


    <!-- =========================
     HEADER
========================= -->

    <header class="main-header">

        <div class="header-container">


            <!-- BRAND LOGO -->

            <a href="index.php" class="brand-logo">

                <span class="brand-mark" aria-hidden="true">

                    <svg class="flower-logo" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">

                        <g fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"
                            stroke-linejoin="round">

                            <path d="M32 31
                     C21 26 18 16 22 8
                     C31 11 36 20 32 31Z" />

                            <path d="M33 31
                     C38 20 47 16 55 20
                     C52 29 43 34 33 31Z" />

                            <path d="M32 33
                     C43 38 46 47 42 56
                     C33 52 28 43 32 33Z" />

                            <path d="M30 32
                     C20 37 10 34 6 26
                     C14 20 24 22 30 32Z" />

                            <circle cx="32" cy="32" r="3.2" />

                            <path d="M32 35
                     C32 43 30 50 26 57" />

                        </g>

                    </svg>

                </span>

                <div class="logo-text">

                    <span class="brand-name">
                        Authentic Perfume
                    </span>

                    <span class="brand-tagline">
                        ORIGINAL SCENTS • A MORE BEAUTIFUL YOU
                    </span>

                </div>

            </a>



            <!-- NAVIGATION -->

            <nav class="main-nav">

                <a href="index.php" class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
                    Home
                </a>

                <a href="shop.php" class="<?php echo $current_page === 'shop.php' ? 'active' : ''; ?>">
                    Shop
                </a>

                <a href="verify.php" class="<?php echo $current_page === 'verify.php' ? 'active' : ''; ?>">
                    Verify Authenticity
                </a>

                <a href="orders.php" class="<?php echo $current_page === 'orders.php' ? 'active' : ''; ?>">
                    Orders
                </a>

                <a href="cart.php" class="nav-cart">

                    Cart

                    <i class="fa-solid fa-cart-shopping"></i>

                </a>

                <?php if (isset($_SESSION["user_id"])): ?>

                    <span class="logged-user">

                        <i class="fa-regular fa-user"></i>

                        <?php
                        echo htmlspecialchars(
                            $_SESSION["full_name"]
                        );
                        ?>

                    </span>


                    <a href="logout.php" class="register-button">

                        Logout

                    </a>

                <?php else: ?>

                    <a href="login.php">
                        Login
                    </a>


                    <a href="register.php" class="register-button">

                        Register

                    </a>

                <?php endif; ?>

            </nav>

        </div>

    </header>