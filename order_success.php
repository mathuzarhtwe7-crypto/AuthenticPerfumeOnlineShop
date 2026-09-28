<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["last_order_id"])
) {

    header("Location: index.php");
    exit;
}


$order_id =
    (int) $_SESSION["last_order_id"];


unset(
    $_SESSION["last_order_id"]
);


require_once 'includes/header.php';

?>


<section class="order-success-page">

    <div class="order-success-card">


        <div class="order-success-icon">

            <i class="fa-solid fa-check"></i>

        </div>


        <p class="order-success-eyebrow">
            ORDER CONFIRMED
        </p>


        <h1>
            Thank You for Your Order
        </h1>


        <p class="order-success-text">

            Your order has been placed successfully.
            You can view its current status from your
            order history.

        </p>


        <div class="order-number">

            Order Number

            <strong>
                #
                <?php
                echo $order_id;
                ?>
            </strong>

        </div>


        <div class="order-success-actions">

            <a href="orders.php" class="success-primary">
                View My Orders
            </a>


            <a href="shop.php" class="success-secondary">
                Continue Shopping
            </a>

        </div>


    </div>

</section>


<?php
require_once 'includes/footer.php';
?>