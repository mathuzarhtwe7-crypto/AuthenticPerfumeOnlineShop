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
   DASHBOARD STATISTICS
====================================== */

$total_products = 0;
$total_orders = 0;
$pending_orders = 0;
$low_stock_products = 0;


/* PRODUCTS */

$result =
    $conn->query(
        "SELECT COUNT(*) AS total
         FROM products"
    );

if ($result) {

    $row =
        $result->fetch_assoc();

    $total_products =
        (int) $row["total"];
}


/* ORDERS */

$result =
    $conn->query(
        "SELECT COUNT(*) AS total
         FROM orders"
    );

if ($result) {

    $row =
        $result->fetch_assoc();

    $total_orders =
        (int) $row["total"];
}


/* PENDING */

$result =
    $conn->query(
        "SELECT COUNT(*) AS total
         FROM orders
         WHERE order_status = 'Pending'"
    );

if ($result) {

    $row =
        $result->fetch_assoc();

    $pending_orders =
        (int) $row["total"];
}


/* LOW STOCK */

$result =
    $conn->query(
        "SELECT COUNT(*) AS total
         FROM products
         WHERE stock_quantity <= 5"
    );

if ($result) {

    $row =
        $result->fetch_assoc();

    $low_stock_products =
        (int) $row["total"];
}


/* ======================================
   RECENT ORDERS
====================================== */

$recent_orders =
    $conn->query(
        "SELECT
            o.order_id,
            o.order_date,
            o.total_amount,
            o.order_status,
            u.full_name

         FROM orders o

         INNER JOIN users u
            ON o.user_id = u.user_id

         ORDER BY o.order_date DESC,
                  o.order_id DESC

         LIMIT 5"
    );

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Admin Dashboard | Authentic Perfume
    </title>


    <link rel="stylesheet" href="../assets/css/style.css?v=21">


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

                    <svg class="flower-logo" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <g fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"
                            stroke-linejoin="round">
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


                <a href="dashboard.php" class="active">

                    <i class="fa-solid fa-chart-line"></i>

                    Dashboard

                </a>


                <a href="products.php">

                    <i class="fa-solid fa-box"></i>

                    Products

                </a>


                <a href="orders.php">

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


            <!-- TOP BAR -->

            <div class="admin-topbar">


                <div>

                    <p>
                        ADMINISTRATION
                    </p>

                    <h1>
                        Dashboard
                    </h1>

                </div>


                <div class="admin-user">

                    <div class="admin-user-icon">

                        <i class="fa-solid fa-user"></i>

                    </div>


                    <div>

                        <span>
                            Administrator
                        </span>

                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $_SESSION["full_name"]
                            );
                            ?>

                        </strong>

                    </div>

                </div>


            </div>



            <!-- STAT CARDS -->

            <section class="admin-stats">


                <div class="admin-stat-card">

                    <div class="admin-stat-icon">

                        <i class="fa-solid fa-box"></i>

                    </div>


                    <div>

                        <span>
                            Total Products
                        </span>

                        <strong>
                            <?php
                            echo $total_products;
                            ?>
                        </strong>

                    </div>

                </div>



                <div class="admin-stat-card">

                    <div class="admin-stat-icon">

                        <i class="fa-solid fa-bag-shopping"></i>

                    </div>


                    <div>

                        <span>
                            Total Orders
                        </span>

                        <strong>
                            <?php
                            echo $total_orders;
                            ?>
                        </strong>

                    </div>

                </div>



                <div class="admin-stat-card">

                    <div class="admin-stat-icon">

                        <i class="fa-regular fa-clock"></i>

                    </div>


                    <div>

                        <span>
                            Pending Orders
                        </span>

                        <strong>
                            <?php
                            echo $pending_orders;
                            ?>
                        </strong>

                    </div>

                </div>



                <div class="admin-stat-card">

                    <div class="admin-stat-icon">

                        <i class="fa-solid fa-triangle-exclamation"></i>

                    </div>


                    <div>

                        <span>
                            Low Stock
                        </span>

                        <strong>
                            <?php
                            echo $low_stock_products;
                            ?>
                        </strong>

                    </div>

                </div>


            </section>



            <!-- RECENT ORDERS -->

            <section class="admin-panel">


                <div class="admin-panel-heading">

                    <div>

                        <p>
                            LATEST ACTIVITY
                        </p>

                        <h2>
                            Recent Orders
                        </h2>

                    </div>


                    <a href="orders.php">
                        View All →
                    </a>

                </div>



                <?php if (
                    $recent_orders &&
                    $recent_orders->num_rows > 0
                ): ?>


                    <div class="admin-table-wrapper">


                        <table class="admin-table">


                            <thead>

                                <tr>

                                    <th>
                                        Order
                                    </th>

                                    <th>
                                        Customer
                                    </th>

                                    <th>
                                        Date
                                    </th>

                                    <th>
                                        Total
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                                <?php while (
                                    $order =
                                    $recent_orders->fetch_assoc()
                                ): ?>


                                    <?php

                                    $status_class =
                                        strtolower(
                                            preg_replace(
                                                '/[^a-zA-Z0-9]+/',
                                                '-',
                                                $order[
                                                    "order_status"
                                                ]
                                            )
                                        );

                                    ?>


                                    <tr>

                                        <td>

                                            <strong>
                                                #
                                                <?php
                                                echo (int) 
                                                    $order["order_id"];
                                                ?>
                                            </strong>

                                        </td>


                                        <td>

                                            <?php
                                            echo htmlspecialchars(
                                                $order["full_name"]
                                            );
                                            ?>

                                        </td>


                                        <td>

                                            <?php
                                            echo date(
                                                "d M Y",
                                                strtotime(
                                                    $order["order_date"]
                                                )
                                            );
                                            ?>

                                        </td>


                                        <td>

                                            $
                                            <?php
                                            echo number_format(
                                                $order["total_amount"],
                                                2
                                            );
                                            ?>

                                        </td>


                                        <td>

                                            <span class="admin-status
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

                                            </span>

                                        </td>

                                    </tr>


                                <?php endwhile; ?>


                            </tbody>


                        </table>


                    </div>


                <?php else: ?>


                    <div class="admin-empty">

                        No orders have been placed yet.

                    </div>


                <?php endif; ?>


            </section>


        </main>


    </div>


</body>

</html>