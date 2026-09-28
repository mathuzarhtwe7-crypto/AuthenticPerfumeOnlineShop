<?php
require_once 'includes/header.php';
?>


<!-- =================================
     HERO SECTION
================================= -->

<section class="hero">

    <div class="hero-content">

        <p class="hero-small-title">
            AUTHENTIC LUXURY PERFUMES
        </p>


        <h1>
            Discover Timeless
            <br>
            Fragrance
        </h1>


        <p class="hero-description">

            100% authentic luxury perfumes from carefully selected
            brands. Because every scent tells a story.

        </p>


        <a href="shop.php" class="primary-button">

            Explore Collection

            <span>→</span>

        </a>



        <!-- HERO FEATURES -->

        <div class="hero-features">


            <!-- AUTHENTIC -->

            <div class="hero-feature">

                <div class="hero-feature-icon">

                    <i class="fa-regular fa-gem"></i>

                </div>

                <div>

                    <strong>
                        100% Authentic
                    </strong>

                    <small>
                        Products
                    </small>

                </div>

            </div>



            <!-- DELIVERY -->

            <div class="hero-feature">

                <div class="hero-feature-icon">

                    <i class="fa-solid fa-truck-fast"></i>

                </div>

                <div>

                    <strong>
                        Secure & Fast
                    </strong>

                    <small>
                        Delivery
                    </small>

                </div>

            </div>



            <!-- PACKAGING -->

            <div class="hero-feature">

                <div class="hero-feature-icon">

                    <i class="fa-solid fa-gift"></i>

                </div>

                <div>

                    <strong>
                        Premium
                    </strong>

                    <small>
                        Packaging
                    </small>

                </div>

            </div>


        </div>

    </div>

</section>



<!-- =================================
     FEATURED PRODUCTS
================================= -->

<section class="featured-section">


    <div class="section-heading">

        <h2>
            Featured Products
        </h2>

        <div class="heading-line"></div>

        <a href="shop.php">
            View All →
        </a>

    </div>



    <div class="featured-layout">


        <!-- PRODUCTS -->

        <div class="product-grid">


            <!-- PRODUCT 1 -->
            <div class="product-card home-featured-clickable" role="link" tabindex="0"
                onclick="window.location.href='product.php?id=3'"
                onkeydown="if(event.key === 'Enter'){ window.location.href='product.php?id=3'; }">

                <div class="product-image product-one">

                    <span class="product-category">
                        Women
                    </span>

                </div>


                <div class="product-info">

                    <p class="featured-brand">
                        Maison Luxe
                    </p>

                    <h3>
                        Rose Lumière
                    </h3>


                    <div class="product-bottom">

                        <span class="price">
                            $135.00
                        </span>

                        <button class="cart-button" type="button" aria-label="View Rose Lumiere"
                            onclick="event.stopPropagation(); window.location.href='product.php?id=3';">

                            <i class="fa-solid fa-cart-shopping"></i>

                        </button>

                    </div>

                </div>

            </div>




            <!-- PRODUCT 2 -->

            <div class="product-card home-featured-clickable" role="link" tabindex="0"
                onclick="window.location.href='product.php?id=2'"
                onkeydown="if(event.key === 'Enter'){ window.location.href='product.php?id=2'; }">

                <div class="product-image product-two">

                    <span class="product-category">
                        Men
                    </span>

                </div>

                <div class="product-info">

                    <p class="featured-brand">
                        Velour
                    </p>

                    <h3>
                        Noir Essence
                    </h3>

                    <div class="product-bottom">

                        <span class="price">
                            $145.00
                        </span>
                        <button class="cart-button" type="button" aria-label="View Noir Essence"
                            onclick="event.stopPropagation(); window.location.href='product.php?id=2';">

                            <i class="fa-solid fa-cart-shopping"></i>

                        </button>

                    </div>

                </div>

            </div>




            <!-- PRODUCT 3 -->

            <!-- PRODUCT 3 -->

            <div class="product-card home-featured-clickable" role="link" tabindex="0"
                onclick="window.location.href='product.php?id=6'"
                onkeydown="if(event.key === 'Enter'){ window.location.href='product.php?id=6'; }">

                <div class="product-image product-three">

                    <span class="product-category">
                        Unisex
                    </span>

                </div>


                <div class="product-info">

                    <p class="featured-brand">
                        Aurelia
                    </p>

                    <h3>
                        Golden Musk
                    </h3>

                    <div class="product-bottom">

                        <span class="price">
                            $150.00
                        </span>

                        <button class="cart-button" type="button" aria-label="View Golden Musk"
                            onclick="event.stopPropagation(); window.location.href='product.php?id=6';">

                            <i class="fa-solid fa-cart-shopping"></i>

                        </button>

                    </div>

                </div>

            </div>


        </div>



        <!-- =================================
             AUTHENTICITY VERIFICATION
        ================================= -->

        <div class="authenticity-card">


            <div class="authenticity-content">


                <span class="auth-small-text">
                    BUY WITH CONFIDENCE
                </span>


                <h2>
                    Authenticity
                    <br>
                    Verification
                </h2>


                <p>

                    Enter the authenticity code to verify
                    that your perfume is genuine.

                </p>



                <form action="verify.php" method="GET" class="verify-form">

                    <input type="text" name="code" placeholder="Enter authenticity code">

                    <button type="submit">
                        Verify
                    </button>

                </form>



                <div class="authenticity-note">

                    <i class="fa-solid fa-shield-halved"></i>

                    <span>
                        Authentic products. Real peace of mind.
                    </span>

                </div>


            </div>

        </div>


    </div>

</section>



<!-- =================================
     TRUST / BENEFITS SECTION
================================= -->

<section class="trust-section">


    <!-- CURATED BRANDS -->

    <div class="trust-item">

        <div class="trust-icon">

            <i class="fa-solid fa-leaf"></i>

        </div>


        <div>

            <strong>
                Curated Luxury Brands
            </strong>

            <small>
                Only authentic, premium perfumes
            </small>

        </div>

    </div>




    <!-- FRAGRANCE -->

    <div class="trust-item">

        <div class="trust-icon">

            <i class="fa-regular fa-gem"></i>

        </div>


        <div>

            <strong>
                A Fragrance for Every Story
            </strong>

            <small>
                Iconic scents for every occasion
            </small>

        </div>

    </div>




    <!-- DELIVERY -->

    <div class="trust-item">

        <div class="trust-icon">

            <i class="fa-solid fa-truck-fast"></i>

        </div>


        <div>

            <strong>
                Secure Delivery
            </strong>

            <small>
                Bringing luxury to your doorstep
            </small>

        </div>

    </div>




    <!-- CUSTOMER CARE -->

    <div class="trust-item">

        <div class="trust-icon">

            <i class="fa-regular fa-heart"></i>

        </div>


        <div>

            <strong>
                Exceptional Customer Care
            </strong>

            <small>
                Your satisfaction, our priority
            </small>

        </div>

    </div>


</section>



<?php
require_once 'includes/footer.php';
?>