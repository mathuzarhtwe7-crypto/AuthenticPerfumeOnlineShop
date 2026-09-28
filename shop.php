<?php

require_once 'config/database.php';


/* ======================================
   GET SEARCH / FILTER VALUES
====================================== */

$search = trim($_GET["search"] ?? "");

$category_id =
    isset($_GET["category"]) &&
    $_GET["category"] !== ""
    ? (int) $_GET["category"]
    : 0;

$min_price =
    isset($_GET["min_price"]) &&
    is_numeric($_GET["min_price"])
    ? (float) $_GET["min_price"]
    : 0;

$max_price =
    isset($_GET["max_price"]) &&
    is_numeric($_GET["max_price"])
    ? (float) $_GET["max_price"]
    : 0;


/* ======================================
   LOAD CATEGORIES FOR FILTER
====================================== */

$category_sql =
    "SELECT
        category_id,
        category_name
     FROM categories
     ORDER BY category_name ASC";

$category_result =
    $conn->query($category_sql);


/* ======================================
   LOAD FILTERED PRODUCTS
====================================== */

$search_like =
    "%" . $search . "%";


$product_sql =
    "SELECT
        p.product_id,
        p.product_name,
        p.brand,
        p.description,
        p.price,
        p.stock_quantity,
        p.image_url,
        c.category_id,
        c.category_name

     FROM products p

     INNER JOIN categories c
        ON p.category_id = c.category_id
WHERE
    (
        ? = ''
        OR p.product_name LIKE ?
        OR p.brand LIKE ?
        OR c.category_name LIKE ?
    )

        AND
        (
            ? = 0
            OR c.category_id = ?
        )

        AND
        (
            ? = 0
            OR p.price >= ?
        )

        AND
        (
            ? = 0
            OR p.price <= ?
        )

     ORDER BY p.product_id ASC";


$stmt =
    $conn->prepare($product_sql);


$stmt->bind_param(
    "ssssiidddd",

    $search,
    $search_like,
    $search_like,
    $search_like,

    $category_id,
    $category_id,

    $min_price,
    $min_price,

    $max_price,
    $max_price
);


$stmt->execute();

$result =
    $stmt->get_result();


/* ======================================
   CHECK IF FILTERS ARE ACTIVE
====================================== */

$filters_active =
    $search !== "" ||
    $category_id !== 0 ||
    $min_price !== 0.0 ||
    $max_price !== 0.0;


require_once 'includes/header.php';

?>


<!-- ======================================
     SHOP HERO
====================================== -->

<section class="shop-hero">

    <div class="shop-hero-content">

        <p class="shop-eyebrow">
            AUTHENTIC LUXURY FRAGRANCES
        </p>

        <h1>
            The Collection
        </h1>

        <p>
            Discover a carefully selected collection of authentic
            fragrances created for every style and occasion.
        </p>

    </div>

</section>



<!-- ======================================
     SHOP SECTION
====================================== -->

<section class="shop-section">


    <!-- ==================================
         SEARCH & FILTER
    =================================== -->

    <div class="shop-filter-panel">


        <div class="filter-heading">

            <div>

                <p>
                    FIND YOUR FRAGRANCE
                </p>

                <h2>
                    Search & Filter
                </h2>

            </div>

            <?php if ($filters_active): ?>

                <a href="shop.php" class="clear-filter-top">
                    Clear All
                </a>

            <?php endif; ?>

        </div>



        <form action="shop.php" method="GET" class="shop-filter-form">


            <!-- SEARCH -->

            <div class="filter-group search-filter-group">

                <label for="search">
                    Search
                </label>

                <div class="filter-input-wrapper">

                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input type="text" id="search" name="search" placeholder="Search perfume or brand" value="<?php
                    echo htmlspecialchars(
                        $search
                    );
                    ?>">

                </div>

            </div>



            <!-- CATEGORY -->

            <div class="filter-group">

                <label for="category">
                    Category
                </label>

                <select id="category" name="category">

                    <option value="">
                        All Categories
                    </option>


                    <?php
                    if (
                        $category_result &&
                        $category_result->num_rows > 0
                    ):
                        ?>


                        <?php
                        while (
                            $category =
                            $category_result->fetch_assoc()
                        ):
                            ?>


                            <option value="<?php
                            echo (int) 
                                $category["category_id"];
                            ?>" <?php
                            echo
                                $category_id ===
                                (int) 
                                $category["category_id"]
                                ? "selected"
                                : "";
                            ?>> <?php
                            echo htmlspecialchars(
                                $category["category_name"]
                            );
                            ?> </option>


                        <?php endwhile; ?>


                    <?php endif; ?>

                </select>

            </div>



            <!-- MIN PRICE -->

            <div class="filter-group price-filter">

                <label for="min_price">
                    Min Price
                </label>

                <div class="price-input">

                    <span>
                        $
                    </span>

                    <input type="number" id="min_price" name="min_price" placeholder="0" min="0" step="0.01" value="<?php
                    echo $min_price > 0
                        ? htmlspecialchars(
                            number_format(
                                $min_price,
                                2,
                                ".",
                                ""
                            )
                        )
                        : "";
                    ?>">

                </div>

            </div>



            <!-- MAX PRICE -->

            <div class="filter-group price-filter">

                <label for="max_price">
                    Max Price
                </label>

                <div class="price-input">

                    <span>
                        $
                    </span>

                    <input type="number" id="max_price" name="max_price" placeholder="500" min="0" step="0.01" value="<?php
                    echo $max_price > 0
                        ? htmlspecialchars(
                            number_format(
                                $max_price,
                                2,
                                ".",
                                ""
                            )
                        )
                        : "";
                    ?>">

                </div>

            </div>



            <!-- FILTER BUTTON -->

            <div class="filter-action">

                <button type="submit" class="filter-button">

                    <i class="fa-solid fa-sliders"></i>

                    Apply Filters

                </button>

            </div>


        </form>

    </div>



    <!-- ==================================
         PRODUCT HEADING
    =================================== -->

    <div class="shop-topbar">

        <div>

            <p class="shop-result-label">

                <?php if ($filters_active): ?>

                    SEARCH RESULTS

                <?php else: ?>

                    OUR PERFUMES

                <?php endif; ?>

            </p>


            <h2>

                <?php if ($filters_active): ?>

                    Matching Fragrances

                <?php else: ?>

                    Explore the Collection

                <?php endif; ?>

            </h2>

        </div>



        <div class="product-count">

            <?php
            $product_count =
                $result
                ? $result->num_rows
                : 0;

            echo $product_count;
            ?>

            <?php
            echo $product_count === 1
                ? "Product"
                : "Products";
            ?>

        </div>

    </div>



    <!-- ==================================
         PRODUCTS
    =================================== -->

    <?php if (
        $result &&
        $result->num_rows > 0
    ): ?>


        <div class="shop-product-grid">


            <?php while (
                $product =
                $result->fetch_assoc()
            ): ?>


                <article class="shop-product-card" role="link" tabindex="0"
                    onclick="window.location.href='product.php?id=<?php echo (int) $product['product_id']; ?>'"
                    onkeydown="if(event.key === 'Enter'){ window.location.href='product.php?id=<?php echo (int) $product['product_id']; ?>'; }">



                    <!-- PRODUCT IMAGE -->

                    <div class="shop-product-image">


                        <?php

                        $image_path =
                            $product["image_url"];

                        $full_image_path =
                            __DIR__ . "/" .
                            $image_path;

                        ?>


                        <?php if (
                            !empty($image_path) &&
                            file_exists(
                                $full_image_path
                            )
                        ): ?>


                            <img src="<?php
                            echo htmlspecialchars(
                                $image_path
                            );
                            ?>" alt="<?php
                            echo htmlspecialchars(
                                $product[
                                    "product_name"
                                ]
                            );
                            ?>">


                        <?php else: ?>

                            <div class="shop-image-placeholder">
                            </div>


                        <?php endif; ?>



                        <span class="product-category">

                            <?php
                            echo htmlspecialchars(
                                $product[
                                    "category_name"
                                ]
                            );
                            ?>

                        </span>


                    </div>



                    <!-- PRODUCT INFO -->

                    <div class="shop-product-info">


                        <p class="shop-product-brand">

                            <?php
                            echo htmlspecialchars(
                                $product["brand"]
                            );
                            ?>

                        </p>


                        <h3>

                            <?php
                            echo htmlspecialchars(
                                $product["product_name"]
                            );
                            ?>

                        </h3>


                        <p class="shop-product-description">

                            <?php
                            echo htmlspecialchars(
                                $product[
                                    "description"
                                ]
                            );
                            ?>

                        </p>



                        <!-- STOCK -->

                        <div class="shop-stock">


                            <?php if (
                                $product[
                                    "stock_quantity"
                                ] > 0
                            ): ?>


                                <span class="stock-dot"></span>

                                In Stock


                            <?php else: ?>


                                <span class="stock-dot out"></span>

                                Out of Stock


                            <?php endif; ?>


                        </div>



                        <!-- PRICE -->
                        <div class="shop-product-bottom">


                            <span class="shop-price">

                                $
                                <?php
                                echo number_format(
                                    $product["price"],
                                    2
                                );
                                ?>

                            </span>


                            <span class="shop-card-cart-icon" aria-hidden="true">

                                <i class="fa-solid fa-cart-shopping"></i>

                            </span>


                        </div>


                    </div>


                </article>


            <?php endwhile; ?>


        </div>


    <?php else: ?>


        <!-- NO RESULTS -->

        <div class="no-products">

            <i class="fa-solid fa-magnifying-glass"></i>

            <h3>
                No matching perfumes found
            </h3>

            <p>
                Try another search term or adjust
                your filters.
            </p>


            <a href="shop.php" class="reset-filter-button">
                Clear Filters
            </a>

        </div>


    <?php endif; ?>


</section>


<?php

$stmt->close();

require_once 'includes/footer.php';

?>