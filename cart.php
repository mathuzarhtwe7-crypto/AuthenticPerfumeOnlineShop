<?php

require_once 'config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* ======================================
   LOGIN REQUIRED
====================================== */

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit;
}


$user_id = (int) $_SESSION["user_id"];


/* ======================================
   FIND CUSTOMER CART
====================================== */

$cart_id = 0;
$cart_total = 0;

$cart_sql =
    "SELECT
        cart_id,
        total_amount
     FROM carts
     WHERE user_id = ?
     LIMIT 1";


$cart_stmt =
    $conn->prepare($cart_sql);

$cart_stmt->bind_param(
    "i",
    $user_id
);

$cart_stmt->execute();

$cart_result =
    $cart_stmt->get_result();


if ($cart_result->num_rows === 1) {

    $cart =
        $cart_result->fetch_assoc();

    $cart_id =
        (int) $cart["cart_id"];

    $cart_total =
        (float) $cart["total_amount"];
}


$cart_stmt->close();


/* ======================================
   LOAD CART ITEMS
====================================== */

$cart_items = null;


if ($cart_id > 0) {

    $item_sql =
        "SELECT
            ci.cart_item_id,
            ci.quantity,
            ci.unit_price,
            ci.subtotal,

            p.product_id,
            p.product_name,
            p.brand,
            p.stock_quantity,
            p.image_url,

            c.category_name

         FROM cart_items ci

         INNER JOIN products p
            ON ci.product_id = p.product_id

         INNER JOIN categories c
            ON p.category_id = c.category_id

         WHERE ci.cart_id = ?

         ORDER BY ci.cart_item_id DESC";


    $item_stmt =
        $conn->prepare($item_sql);

    $item_stmt->bind_param(
        "i",
        $cart_id
    );

    $item_stmt->execute();

    $cart_items =
        $item_stmt->get_result();
}


require_once 'includes/header.php';

?>


<!-- ======================================
     CART PAGE
====================================== -->

<section class="cart-page">

    <div class="cart-page-container">


        <!-- PAGE HEADING -->

        <div class="cart-page-heading">

            <div>

                <p class="cart-eyebrow">
                    YOUR SELECTION
                </p>

                <h1>
                    Shopping Cart
                </h1>

            </div>


            <a href="shop.php" class="continue-shopping">
                ← Continue Shopping
            </a>

        </div>



        <!-- MESSAGE -->

        <?php if (isset($_SESSION["cart_message"])): ?>

            <?php
            $cart_message_type =
                $_SESSION["cart_message_type"]
                ?? "success";
            ?>

            <div class="cart-page-message
                <?php echo htmlspecialchars(
                    $cart_message_type
                ); ?>">

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



        <?php if (
            $cart_items &&
            $cart_items->num_rows > 0
        ): ?>


            <div class="cart-layout">


                <!-- ========================
                     CART ITEMS
                ========================= -->

                <div class="cart-items-panel">


                    <form action="update_cart.php" method="POST">


                        <?php while (
                            $item =
                            $cart_items->fetch_assoc()
                        ): ?>


                            <div class="cart-item">


                                <!-- IMAGE -->

                                <a href="product.php?id=<?php
                                echo (int) 
                                    $item["product_id"];
                                ?>" class="cart-item-image">


                                    <?php

                                    $image_path =
                                        $item["image_url"];

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
                                            $item[
                                                "product_name"
                                            ]
                                        );
                                        ?>">


                                    <?php else: ?>


                                        <div class="cart-image-placeholder">
                                        </div>


                                    <?php endif; ?>


                                    <span class="cart-category">

                                        <?php
                                        echo htmlspecialchars(
                                            $item[
                                                "category_name"
                                            ]
                                        );
                                        ?>

                                    </span>


                                </a>



                                <!-- PRODUCT INFO -->

                                <div class="cart-item-info">


                                    <p class="cart-item-brand">

                                        <?php
                                        echo htmlspecialchars(
                                            $item["brand"]
                                        );
                                        ?>

                                    </p>


                                    <h2>

                                        <a href="product.php?id=<?php
                                        echo (int) 
                                            $item["product_id"];
                                        ?>">

                                            <?php
                                            echo htmlspecialchars(
                                                $item[
                                                    "product_name"
                                                ]
                                            );
                                            ?>

                                        </a>

                                    </h2>


                                    <p class="cart-unit-price">

                                        $
                                        <?php
                                        echo number_format(
                                            $item[
                                                "unit_price"
                                            ],
                                            2
                                        );
                                        ?>

                                        each

                                    </p>


                                    <div class="cart-item-controls">


                                        <!-- QUANTITY -->

                                        <div class="cart-quantity">

                                            <label>
                                                Quantity
                                            </label>

                                            <input type="number" name="quantities[<?php
                                            echo (int) 
                                                $item[
                                                    "cart_item_id"
                                                ];
                                            ?>]" value="<?php
                                            echo (int) 
                                                $item[
                                                    "quantity"
                                                ];
                                            ?>" min="1" max="<?php
                                            echo (int) 
                                                $item[
                                                    "stock_quantity"
                                                ];
                                            ?>" required>

                                        </div>



                                        <!-- SUBTOTAL -->

                                        <div class="cart-item-subtotal">

                                            <span>
                                                Subtotal
                                            </span>

                                            <strong>

                                                $
                                                <?php
                                                echo number_format(
                                                    $item[
                                                        "subtotal"
                                                    ],
                                                    2
                                                );
                                                ?>

                                            </strong>

                                        </div>

                                    </div>


                                </div>



                                <!-- REMOVE -->

                                <button type="submit" form="remove-item-<?php
                                echo (int) 
                                    $item["cart_item_id"];
                                ?>" class="remove-cart-item" aria-label="Remove <?php
                                echo htmlspecialchars(
                                    $item[
                                        "product_name"
                                    ]
                                );
                                ?>">

                                    <i class="fa-regular fa-trash-can"></i>

                                </button>


                            </div>


                        <?php endwhile; ?>



                        <div class="cart-update-row">

                            <a href="shop.php">
                                ← Add More Perfumes
                            </a>


                            <button type="submit" class="update-cart-button">
                                Update Cart
                            </button>

                        </div>


                    </form>

                </div>



                <!-- ========================
                     ORDER SUMMARY
                ========================= -->

                <aside class="cart-summary">


                    <p class="summary-eyebrow">
                        ORDER SUMMARY
                    </p>

                    <h2>
                        Your Total
                    </h2>


                    <div class="summary-line">

                        <span>
                            Subtotal
                        </span>

                        <strong>
                            $
                            <?php
                            echo number_format(
                                $cart_total,
                                2
                            );
                            ?>
                        </strong>

                    </div>


                    <div class="summary-line">

                        <span>
                            Delivery
                        </span>

                        <span class="summary-muted">
                            Calculated at checkout
                        </span>

                    </div>


                    <div class="summary-divider"></div>


                    <div class="summary-total">

                        <span>
                            Grand Total
                        </span>

                        <strong>
                            $
                            <?php
                            echo number_format(
                                $cart_total,
                                2
                            );
                            ?>
                        </strong>

                    </div>


                    <a href="checkout.php" class="checkout-button">
                        Proceed to Checkout
                        <span>→</span>
                    </a>


                    <div class="summary-note">

                        <i class="fa-solid fa-shield-halved"></i>

                        <span>
                            Secure checkout and authentic products.
                        </span>

                    </div>


                </aside>


            </div>


            <!-- REMOVE FORMS -->

            <?php

            $cart_items->data_seek(0);

            while (
                $remove_item =
                $cart_items->fetch_assoc()
            ):

                ?>

                <form id="remove-item-<?php
                echo (int) 
                    $remove_item[
                        "cart_item_id"
                    ];
                ?>" action="remove_cart_item.php" method="POST" class="hidden-remove-form">

                    <input type="hidden" name="cart_item_id" value="<?php
                    echo (int) 
                        $remove_item[
                            "cart_item_id"
                        ];
                    ?>">

                </form>

            <?php endwhile; ?>


        <?php else: ?>


            <!-- EMPTY CART -->

            <div class="empty-cart">

                <div class="empty-cart-icon">

                    <i class="fa-solid fa-bag-shopping"></i>

                </div>


                <h2>
                    Your cart is empty
                </h2>


                <p>
                    Discover authentic fragrances and add
                    your favourites to your cart.
                </p>


                <a href="shop.php" class="empty-cart-button">
                    Explore Collection
                </a>

            </div>


        <?php endif; ?>


    </div>

</section>


<?php

if (isset($item_stmt)) {
    $item_stmt->close();
}

require_once 'includes/footer.php';

?>