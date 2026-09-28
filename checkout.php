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


$user_id =
    (int) $_SESSION["user_id"];


/* ======================================
   LOAD CUSTOMER
====================================== */

$user_sql =
    "SELECT
        full_name,
        email,
        phone
     FROM users
     WHERE user_id = ?
     LIMIT 1";


$user_stmt =
    $conn->prepare($user_sql);

$user_stmt->bind_param(
    "i",
    $user_id
);

$user_stmt->execute();

$user_result =
    $user_stmt->get_result();


if ($user_result->num_rows !== 1) {

    $user_stmt->close();

    header("Location: logout.php");
    exit;
}


$user =
    $user_result->fetch_assoc();

$user_stmt->close();


/* ======================================
   LOAD CART
====================================== */

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


if ($cart_result->num_rows !== 1) {

    $cart_stmt->close();

    header("Location: cart.php");
    exit;
}


$cart =
    $cart_result->fetch_assoc();

$cart_id =
    (int) $cart["cart_id"];

$cart_total =
    (float) $cart["total_amount"];

$cart_stmt->close();


/* ======================================
   LOAD CART ITEMS
====================================== */

$item_sql =
    "SELECT
        ci.quantity,
        ci.unit_price,
        ci.subtotal,

        p.product_name

     FROM cart_items ci

     INNER JOIN products p
        ON ci.product_id = p.product_id

     WHERE ci.cart_id = ?

     ORDER BY ci.cart_item_id ASC";


$item_stmt =
    $conn->prepare($item_sql);

$item_stmt->bind_param(
    "i",
    $cart_id
);

$item_stmt->execute();

$cart_items =
    $item_stmt->get_result();


if ($cart_items->num_rows === 0) {

    $item_stmt->close();

    header("Location: cart.php");
    exit;
}


require_once 'includes/header.php';

?>


<section class="checkout-page">

    <div class="checkout-container">


        <!-- BACK -->

        <a href="cart.php" class="checkout-back">
            ← Back to Cart
        </a>



        <div class="checkout-heading">

            <p>
                SECURE CHECKOUT
            </p>

            <h1>
                Complete Your Order
            </h1>

            <span>
                Review your details and provide a delivery address.
            </span>

        </div>



        <?php if (
            isset($_SESSION["checkout_error"])
        ): ?>

            <div class="cart-page-message error">

                <?php
                echo htmlspecialchars(
                    $_SESSION["checkout_error"]
                );

                unset(
                    $_SESSION["checkout_error"]
                );
                ?>

            </div>

        <?php endif; ?>



        <div class="checkout-layout">


            <!-- ========================
                 CUSTOMER DETAILS
            ========================= -->

            <div class="checkout-form-card">


                <div class="checkout-card-title">

                    <span>
                        01
                    </span>

                    <div>

                        <p>
                            DELIVERY INFORMATION
                        </p>

                        <h2>
                            Shipping Details
                        </h2>

                    </div>

                </div>



                <form action="place_order.php" method="POST" class="checkout-form">


                    <div class="checkout-field">

                        <label>
                            Full Name
                        </label>

                        <input type="text" value="<?php
                        echo htmlspecialchars(
                            $user["full_name"]
                        );
                        ?>" readonly>

                    </div>



                    <div class="checkout-field">

                        <label>
                            Email Address
                        </label>

                        <input type="email" value="<?php
                        echo htmlspecialchars(
                            $user["email"]
                        );
                        ?>" readonly>

                    </div>



                    <div class="checkout-field">

                        <label>
                            Phone Number
                        </label>

                        <input type="text" value="<?php
                        echo htmlspecialchars(
                            $user["phone"]
                            ?? ""
                        );
                        ?>" readonly>

                    </div>



                    <div class="checkout-field">

                        <label for="shipping_address">
                            Shipping Address
                        </label>

                        <textarea id="shipping_address" name="shipping_address" rows="5"
                            placeholder="Enter your complete delivery address" required></textarea>

                    </div>



                    <button type="submit" class="place-order-button">

                        <i class="fa-solid fa-lock"></i>

                        Place Order

                        <span>→</span>

                    </button>


                </form>

            </div>



            <!-- ========================
                 ORDER REVIEW
            ========================= -->

            <aside class="checkout-summary">


                <p class="summary-eyebrow">
                    ORDER REVIEW
                </p>

                <h2>
                    Your Selection
                </h2>



                <div class="checkout-products">


                    <?php while (
                        $item =
                        $cart_items->fetch_assoc()
                    ): ?>


                        <div class="checkout-product-row">


                            <div>

                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $item[
                                            "product_name"
                                        ]
                                    );
                                    ?>

                                </strong>

                                <span>

                                    Qty:
                                    <?php
                                    echo (int) 
                                        $item["quantity"];
                                    ?>

                                </span>

                            </div>


                            <span>

                                $
                                <?php
                                echo number_format(
                                    $item["subtotal"],
                                    2
                                );
                                ?>

                            </span>


                        </div>


                    <?php endwhile; ?>


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



                <div class="summary-note">

                    <i class="fa-solid fa-shield-halved"></i>

                    <span>
                        Your order is processed securely
                        and products are verified as authentic.
                    </span>

                </div>


            </aside>


        </div>

    </div>

</section>


<?php

$item_stmt->close();

require_once 'includes/footer.php';

?>