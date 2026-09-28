<?php

require_once 'config/database.php';


/* ======================================
   GET PRODUCT ID
====================================== */

$product_id =
    isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;


if ($product_id <= 0) {

    header("Location: shop.php");
    exit;
}


/* ======================================
   LOAD PRODUCT
====================================== */

$sql =
    "SELECT
        p.product_id,
        p.product_name,
        p.brand,
        p.description,
        p.price,
        p.stock_quantity,
        p.authenticity_code,
        p.image_url,
        c.category_name

     FROM products p

     INNER JOIN categories c
        ON p.category_id = c.category_id

     WHERE p.product_id = ?

     LIMIT 1";


$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $product_id
);

$stmt->execute();

$result =
    $stmt->get_result();


if ($result->num_rows !== 1) {

    $stmt->close();

    header("Location: shop.php");
    exit;
}


$product =
    $result->fetch_assoc();


$stmt->close();


require_once 'includes/header.php';

?>


<section class="product-detail-page">

    <div class="product-detail-back">

        <a href="shop.php" class="back-to-shop">
            ← Back to Collection
        </a>

    </div>


    <div class="product-detail-container">

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
                file_exists($full_image_path)
            ): ?>

                <img src="<?php
                echo htmlspecialchars(
                    $image_path
                );
                ?>" alt="<?php
                echo htmlspecialchars(
                    $product["product_name"]
                );
                ?>">

                 <?php else: ?>

                <div class="shop-image-placeholder">
                </div>

                        <?php endif; ?>


            <span class="product-category">

                            <?php
                            echo htmlspecialchars(
                                $product["category_name"]
                            );
                            ?>

            </span>

        </div>


        <!-- PRODUCT INFORMATION -->

        <div class="product-detail-info">


            <?php if (isset($_SESSION["cart_message"])): ?>

                <?php
                $cart_message_type =
                    $_SESSION["cart_message_type"] ?? "success";
                ?>

                <div class="product-cart-message
        <?php echo htmlspecialchars($cart_message_type); ?>">

                    <?php
                    echo htmlspecialchars(
                        $_SESSION["cart_message"]
                    );
                    ?>

                </div>

                <?php
                unset(
                    $_SESSION["cart_message"],
                    $_SESSION["cart_message_type"]
                );
                ?>

            <?php endif; ?>




            <p class="product-detail-category">

                <?php
                echo htmlspecialchars(
                    $product["category_name"]
                );
                ?>

            </p>


            <p class="product-detail-brand">

                <?php
                echo htmlspecialchars(
                    $product["brand"]
                );
                ?>

            </p>


            <h1>

                <?php
                echo htmlspecialchars(
                    $product["product_name"]
                );
                ?>

            </h1>


            <div class="product-detail-price">

                $
                <?php
                echo number_format(
                    $product["price"],
                    2
                );
                ?>

            </div>


            <p class="product-detail-description">

                <?php
                echo htmlspecialchars(
                    $product["description"]
                );
                ?>

            </p>



            <!-- STOCK -->

            <div class="product-detail-stock">


                <?php if (
                    $product["stock_quantity"] > 0
                ): ?>

                    <span class="stock-dot"></span>

                    In Stock

                    <small>
                        (
                        <?php echo (int) $product["stock_quantity"]; ?> available)
                    </small>


                <?php else: ?>

                    <span class="stock-dot out"></span>

                    Out of Stock

                <?php endif; ?>


            </div>



            <!-- ADD TO CART FORM -->

            <?php if (
                $product["stock_quantity"] > 0
            ): ?>


                <form action="add_to_cart.php" method="POST" class="add-cart-form">

                    <input type="hidden" name="product_id" value="<?php
                    echo (int) 
                        $product["product_id"];
                    ?>">


                    <div class="quantity-field">

                        <label for="quantity">
                            Quantity
                        </label>

                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php
                        echo (int) 
                            $product["stock_quantity"];
                        ?>" required>

                    </div>


                    <button type="submit" class="add-cart-button">

                        <i class="fa-solid fa-cart-shopping"></i>

                        Add to Cart

                    </button>

                </form>


            <?php endif; ?>



            <!-- AUTHENTICITY MESSAGE -->

            <div class="product-authenticity">

                <i class="fa-solid fa-shield-halved"></i>

                <div>

                    <strong>
                        Authenticity Guaranteed
                    </strong>

                    <p>
                        Every fragrance can be verified
                        using its unique authenticity code.
                    </p>

                </div>

            </div>


        </div>

    </div>

</section>


<?php
require_once 'includes/footer.php';
?>