<?php

require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* ======================================
   ADMIN ONLY
====================================== */

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "admin"
) {

    header("Location: login.php");
    exit;
}


/* ======================================
   LOAD ALL ORDERS
====================================== */

$order_sql =
    "SELECT
        o.order_id,
        o.user_id,
        o.order_date,
        o.total_amount,
        o.order_status,
        o.shipping_address,

        u.full_name,
        u.email,
        u.phone

     FROM orders o

     INNER JOIN users u
        ON o.user_id = u.user_id

     ORDER BY
        o.order_date DESC,
        o.order_id DESC";


$order_result =
    $conn->query($order_sql);


/* ======================================
   PREPARE ORDER DETAILS
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

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Order Management | Authentic Perfume
    </title>

    <link rel="stylesheet" href="../assets/css/style.css?v=25">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>


<body class="admin-body">


    <div class="admin-layout">


        <!-- ==================================
         SIDEBAR
    =================================== -->

        <aside class="admin-sidebar">


            <div class="admin-sidebar-brand">

             <div class="admin-sidebar-logo">

    <svg
        class="flower-logo"
        viewBox="0 0 64 64"
        xmlns="http://www.w3.org/2000/svg"
        aria-hidden="true"
    >
        <g
            fill="none"
            stroke="currentColor"
            stroke-width="2.4"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <path d="M32 31 C21 26 18 16 22 8 C31 11 36 20 32 31Z" />
            <path d="M33 31 C38 20 47 16 55 20 C52 29 43 34 33 31Z" />
            <path d="M32 33 C43 38 46 47 42 56 C33 52 28 43 32 33Z" />
            <path d="M30 32 C20 37 10 34 6 26 C14 20 24 22 30 32Z" />
            <circle cx="32" cy="32" r="3.2" />
            <path d="M32 35 C32 43 30 50 26 57" />
        </g>
    </svg>

</div>


                <div>

                    <strong>
                        Authentic Perfume
                    </strong>

                    <span>
                        ADMIN PANEL
                    </span>

                </div>

            </div>



            <nav class="admin-menu">


                <a href="dashboard.php">

                    <i class="fa-solid fa-chart-line"></i>

                    Dashboard

                </a>


                <a href="products.php">

                    <i class="fa-solid fa-box"></i>

                    Products

                </a>


                <a href="orders.php" class="active">

                    <i class="fa-solid fa-receipt"></i>

                    Orders

                </a>


                <a href="../index.php">

                    <i class="fa-solid fa-store"></i>

                    View Store

                </a>


            </nav>



            <div class="admin-sidebar-bottom">

                <a href="logout.php">

                    <i class="fa-solid fa-right-from-bracket"></i>

                    Logout

                </a>

            </div>


        </aside>



        <!-- ==================================
         MAIN
    =================================== -->

        <main class="admin-main">


            <!-- PAGE HEADING -->

            <div class="admin-orders-heading">


                <div>

                    <p>
                        ORDER MANAGEMENT
                    </p>

                    <h1>
                        Customer Orders
                    </h1>

                    <span>
                        Review customer purchases, delivery details
                        and current order status.
                    </span>

                </div>


                <div class="admin-orders-count">

                    <?php
                    echo $order_result
                        ? $order_result->num_rows
                        : 0;
                    ?>

                    Orders

                </div>


            </div>


            <?php if (
    isset($_SESSION["admin_order_message"])
): ?>

    <?php
    $message_type =
        $_SESSION[
            "admin_order_message_type"
        ]
        ?? "success";
    ?>


    <div class="admin-form-message
        <?php
        echo htmlspecialchars(
            $message_type
        );
        ?>
    ">

        <?php
        echo htmlspecialchars(
            $_SESSION[
                "admin_order_message"
            ]
        );
        ?>

    </div>


    <?php
    unset(
        $_SESSION[
            "admin_order_message"
        ],
        $_SESSION[
            "admin_order_message_type"
        ]
    );
    ?>

<?php endif; ?>



            <!-- ==================================
             ORDERS
        =================================== -->

            <?php if (
                $order_result &&
                $order_result->num_rows > 0
            ): ?>


                <div class="admin-orders-list">


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


                        <article class="admin-order-card">


                            <!-- TOP -->

                            <div class="admin-order-top">


                                <div class="admin-order-number">

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



                                <div class="admin-order-info">


                                    <div>

                                        <span>
                                            Customer
                                        </span>

                                        <strong>
                                            <?php
                                            echo htmlspecialchars(
                                                $order["full_name"]
                                            );
                                            ?>
                                        </strong>

                                    </div>



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


    <form
        action="update_order_status.php"
        method="POST"
        class="admin-status-form"
    >

        <input
            type="hidden"
            name="order_id"
            value="<?php
                echo (int)
                $order["order_id"];
            ?>"
        >


        <select
            name="order_status"
            class="admin-status-select
            status-<?php
                echo htmlspecialchars(
                    $status_class
                );
            ?>"
        >

            <option
                value="Pending"
                <?php
                echo (
                    $order["order_status"] ===
                    "Pending"
                )
                ? "selected"
                : "";
                ?>
            >
                Pending
            </option>


            <option
                value="Processing"
                <?php
                echo (
                    $order["order_status"] ===
                    "Processing"
                )
                ? "selected"
                : "";
                ?>
            >
                Processing
            </option>


            <option
                value="Shipped"
                <?php
                echo (
                    $order["order_status"] ===
                    "Shipped"
                )
                ? "selected"
                : "";
                ?>
            >
                Shipped
            </option>


            <option
                value="Delivered"
                <?php
                echo (
                    $order["order_status"] ===
                    "Delivered"
                )
                ? "selected"
                : "";
                ?>
            >
                Delivered
            </option>

        </select>


        <button
            type="submit"
            class="admin-status-update"
            title="Update Status"
        >

            <i class="fa-solid fa-check"></i>

        </button>


    </form>

</div>


                                </div>


                            </div>



                            <!-- DETAILS -->

                            <details class="admin-order-details">


                                <summary>

                                    View Order Details

                                    <i class="fa-solid fa-chevron-down"></i>

                                </summary>



                                <div class="admin-order-details-content">


                                    <!-- CUSTOMER -->

                                    <div class="admin-order-section">


                                        <p class="admin-order-section-title">
                                            CUSTOMER INFORMATION
                                        </p>


                                        <div class="admin-customer-details">


                                            <div>

                                                <i class="fa-regular fa-user"></i>

                                                <span>

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $order["full_name"]
                                                    );
                                                    ?>

                                                </span>

                                            </div>


                                            <div>

                                                <i class="fa-regular fa-envelope"></i>

                                                <span>

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $order["email"]
                                                    );
                                                    ?>

                                                </span>

                                            </div>


                                            <div>

                                                <i class="fa-solid fa-phone"></i>

                                                <span>

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $order["phone"]
                                                        ?? "-"
                                                    );
                                                    ?>

                                                </span>

                                            </div>


                                        </div>


                                    </div>



                                    <!-- PRODUCTS -->

                                    <div class="admin-order-section">


                                        <p class="admin-order-section-title">
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


                                        <div class="admin-ordered-products">


                                            <?php while (
                                                $item =
                                                $detail_result->fetch_assoc()
                                            ): ?>


                                                <div class="admin-ordered-item">


                                                    <div>

                                                        <p>
                                                            <?php
                                                            echo htmlspecialchars(
                                                                $item["brand"]
                                                            );
                                                            ?>
                                                        </p>


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


                                                    <strong>

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


                                    </div>



                                    <!-- SHIPPING -->

                                    <div class="admin-order-section">


                                        <p class="admin-order-section-title">
                                            SHIPPING ADDRESS
                                        </p>


                                        <div class="admin-shipping-address">

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


                <div class="admin-orders-empty">

                    <i class="fa-solid fa-box-open"></i>

                    <h2>
                        No Orders Yet
                    </h2>

                    <p>
                        Customer orders will appear here after checkout.
                    </p>

                </div>


            <?php endif; ?>


        </main>


    </div>


</body>

</html>


<?php

$detail_stmt->close();

?>