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
   LOAD CUSTOMER ORDERS
====================================== */

$order_sql =
    "SELECT
        order_id,
        order_date,
        total_amount,
        order_status,
        shipping_address

     FROM orders

     WHERE user_id = ?

     ORDER BY order_date DESC,
              order_id DESC";


$order_stmt =
    $conn->prepare($order_sql);

$order_stmt->bind_param(
    "i",
    $user_id
);

$order_stmt->execute();

$order_result =
    $order_stmt->get_result();


/* ======================================
   PREPARE ORDER DETAILS QUERY
====================================== */

$detail_sql =
    "SELECT
        od.quantity,
        od.unit_price,
        od.subtotal,

        p.product_name,
        p.brand

     FROM order_details od

     INNER JOIN products p
        ON od.product_id = p.product_id

     WHERE od.order_id = ?

     ORDER BY od.order_detail_id ASC";


$detail_stmt =
    $conn->prepare($detail_sql);


require_once 'includes/header.php';

?>


<!-- ======================================
     CUSTOMER ORDERS PAGE
====================================== -->

<section class="orders-page">

    <div class="orders-container">


        <!-- PAGE HEADING -->

        <div class="orders-heading">

            <div>

                <p class="orders-eyebrow">
                    YOUR PURCHASES
                </p>

                <h1>
                    My Orders
                </h1>

                <span>
                    Review your order history and current order status.
                </span>

            </div>


            <a href="shop.php" class="orders-shop-link">
                Continue Shopping →
            </a>

        </div>



        <?php if (
            $order_result->num_rows > 0
        ): ?>


            <div class="orders-list">


                <?php while (
                    $order =
                    $order_result->fetch_assoc()
                ): ?>


                    <?php

                    $status_class =
                        strtolower(
                            preg_replace(
                                '/[^a-zA-Z0-9]+/',
                                '-',
                                $order["order_status"]
                            )
                        );

                    ?>


                    <article class="order-card">


                        <!-- ORDER TOP -->

                        <div class="order-card-top">


                            <div class="order-number-block">

                                <span>
                                    ORDER
                                </span>

                                <strong>
                                    #
                                    <?php
                                    echo (int) 
                                        $order["order_id"];
                                    ?>
                                </strong>

                            </div>



                            <div class="order-meta">

                                <div>

                                    <span>
                                        Order Date
                                    </span>

                                    <strong>

                                        <?php
                                        echo date(
                                            "d M Y",
                                            strtotime(
                                                $order["order_date"]
                                            )
                                        );
                                        ?>

                                    </strong>

                                </div>


                                <div>

                                    <span>
                                        Total
                                    </span>

                                    <strong>

                                        $
                                        <?php
                                        echo number_format(
                                            $order["total_amount"],
                                            2
                                        );
                                        ?>

                                    </strong>

                                </div>


                                <div>

                                    <span>
                                        Status
                                    </span>

                                    <strong class="order-status
                                        status-<?php
                                        echo htmlspecialchars(
                                            $status_class
                                        );
                                        ?>">

                                        <?php
                                        echo htmlspecialchars(
                                            $order["order_status"]
                                        );
                                        ?>

                                    </strong>

                                </div>

                            </div>


                        </div>



                        <!-- DETAILS -->

                        <details class="order-details-box">


                            <summary>
                                View Order Details

                                <i class="fa-solid fa-chevron-down"></i>
                            </summary>


                            <div class="order-details-content">


                                <!-- PRODUCTS -->

                                <div class="ordered-products">


                                    <p class="order-section-title">
                                        ITEMS ORDERED
                                    </p>


                                    <?php

                                    $current_order_id =
                                        (int) 
                                        $order["order_id"];


                                    $detail_stmt->bind_param(
                                        "i",
                                        $current_order_id
                                    );


                                    $detail_stmt->execute();


                                    $detail_result =
                                        $detail_stmt->get_result();

                                    ?>


                                    <?php while (
                                        $item =
                                        $detail_result->fetch_assoc()
                                    ): ?>


                                        <div class="ordered-product-row">


                                            <div>

                                                <p class="ordered-brand">

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $item["brand"]
                                                    );
                                                    ?>

                                                </p>


                                                <strong>

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $item["product_name"]
                                                    );
                                                    ?>

                                                </strong>


                                                <span>

                                                    Qty:
                                                    <?php
                                                    echo (int) 
                                                        $item["quantity"];
                                                    ?>

                                                    ×

                                                    $
                                                    <?php
                                                    echo number_format(
                                                        $item["unit_price"],
                                                        2
                                                    );
                                                    ?>

                                                </span>

                                            </div>


                                            <strong class="ordered-subtotal">

                                                $
                                                <?php
                                                echo number_format(
                                                    $item["subtotal"],
                                                    2
                                                );
                                                ?>

                                            </strong>


                                        </div>


                                    <?php endwhile; ?>


                                </div>



                                <!-- SHIPPING -->

                                <div class="order-shipping">


                                    <p class="order-section-title">
                                        SHIPPING ADDRESS
                                    </p>


                                    <div class="shipping-address-box">

                                        <i class="fa-solid fa-location-dot"></i>

                                        <span>

                                            <?php
                                            echo nl2br(
                                                htmlspecialchars(
                                                    $order[
                                                        "shipping_address"
                                                    ]
                                                )
                                            );
                                            ?>

                                        </span>

                                    </div>


                                </div>


                            </div>


                        </details>


                    </article>


                <?php endwhile; ?>


            </div>


        <?php else: ?>


            <!-- EMPTY ORDERS -->

            <div class="empty-orders">


                <div class="empty-orders-icon">

                    <i class="fa-solid fa-box-open"></i>

                </div>


                <h2>
                    No orders yet
                </h2>


                <p>
                    When you place your first perfume order,
                    it will appear here with its current status.
                </p>


                <a href="shop.php" class="empty-orders-button">
                    Explore Collection
                </a>


            </div>


        <?php endif; ?>


    </div>

</section>


<?php

$detail_stmt->close();

$order_stmt->close();

require_once 'includes/footer.php';

?>