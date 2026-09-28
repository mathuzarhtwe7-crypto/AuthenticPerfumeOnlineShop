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
   LOAD PRODUCTS
====================================== */

$product_sql =
    "SELECT
        p.product_id,
        p.product_name,
        p.brand,
        p.price,
        p.stock_quantity,
        p.authenticity_code,
        p.image_url,
        c.category_name

     FROM products p

     INNER JOIN categories c
        ON p.category_id = c.category_id

     ORDER BY p.product_id DESC";


$product_result =
    $conn->query($product_sql);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Product Management | Authentic Perfume
    </title>

    <link rel="stylesheet" href="../assets/css/style.css?v=23">

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


                <a href="dashboard.php">

                    <i class="fa-solid fa-chart-line"></i>

                    Dashboard

                </a>


                <a href="products.php" class="active">

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
         MAIN CONTENT
    =================================== -->

        <main class="admin-main">


            <!-- TOP -->

            <div class="admin-products-heading">


                <div>

                    <p>
                        CATALOGUE MANAGEMENT
                    </p>

                    <h1>
                        Products
                    </h1>

                    <span>
                        Manage perfume information, prices and stock levels.
                    </span>

                </div>


                <a href="add_product.php" class="admin-add-product">

                    <i class="fa-solid fa-plus"></i>

                    Add Product

                </a>


            </div>

            <?php if (
                isset($_SESSION["admin_product_message"])
            ): ?>

                <?php
                $message_type =
                    $_SESSION[
                        "admin_product_message_type"
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
                            "admin_product_message"
                        ]
                    );
                    ?>

                </div>


                <?php
                unset(
                    $_SESSION[
                        "admin_product_message"
                    ],
                    $_SESSION[
                        "admin_product_message_type"
                    ]
                );
                ?>

            <?php endif; ?>



            <!-- PRODUCT PANEL -->

            <section class="admin-panel">


                <div class="admin-panel-heading">

                    <div>

                        <p>
                            PRODUCT INVENTORY
                        </p>

                        <h2>
                            All Products
                        </h2>

                    </div>


                    <span class="admin-product-count">

                        <?php
                        echo $product_result
                            ? $product_result->num_rows
                            : 0;
                        ?>

                        Products

                    </span>


                </div>



                <?php if (
                    $product_result &&
                    $product_result->num_rows > 0
                ): ?>


                    <div class="admin-table-wrapper">


                        <table class="admin-table admin-products-table">


                            <thead>

                                <tr>

                                    <th>
                                        Product
                                    </th>

                                    <th>
                                        Category
                                    </th>

                                    <th>
                                        Price
                                    </th>

                                    <th>
                                        Stock
                                    </th>

                                    <th>
                                        Authenticity Code
                                    </th>

                                    <th>
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                                <?php while (
                                    $product =
                                    $product_result->fetch_assoc()
                                ): ?>


                                    <tr>


                                        <!-- PRODUCT -->

                                        <td>

                                            <div class="admin-product-cell">


                                                <div class="admin-product-thumb">


                                                    <?php

                                                    $image_path =
                                                        $product["image_url"];

                                                    $full_image_path =
                                                        dirname(__DIR__) .
                                                        "/" .
                                                        $image_path;

                                                    ?>


                                                    <?php if (
                                                        !empty($image_path) &&
                                                        file_exists(
                                                            $full_image_path
                                                        )
                                                    ): ?>


                                                        <img src="../<?php
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


                                                        <i class="fa-solid fa-spray-can-sparkles"></i>


                                                    <?php endif; ?>


                                                </div>


                                                <div>

                                                    <span>
                                                        <?php
                                                        echo htmlspecialchars(
                                                            $product["brand"]
                                                        );
                                                        ?>
                                                    </span>


                                                    <strong>

                                                        <?php
                                                        echo htmlspecialchars(
                                                            $product[
                                                                "product_name"
                                                            ]
                                                        );
                                                        ?>

                                                    </strong>

                                                </div>


                                            </div>

                                        </td>



                                        <!-- CATEGORY -->

                                        <td>

                                            <span class="admin-category-badge">

                                                <?php
                                                echo htmlspecialchars(
                                                    $product[
                                                        "category_name"
                                                    ]
                                                );
                                                ?>

                                            </span>

                                        </td>



                                        <!-- PRICE -->

                                        <td>

                                            <strong class="admin-product-price">

                                                $
                                                <?php
                                                echo number_format(
                                                    $product["price"],
                                                    2
                                                );
                                                ?>

                                            </strong>

                                        </td>



                                        <!-- STOCK -->

                                        <td>


                                            <?php if (
                                                $product[
                                                    "stock_quantity"
                                                ] <= 5
                                            ): ?>


                                                <span class="admin-stock low">

                                                    <span></span>

                                                    <?php
                                                    echo (int) 
                                                        $product[
                                                            "stock_quantity"
                                                        ];
                                                    ?>

                                                    Low

                                                </span>


                                            <?php else: ?>


                                                <span class="admin-stock">

                                                    <span></span>

                                                    <?php
                                                    echo (int) 
                                                        $product[
                                                            "stock_quantity"
                                                        ];
                                                    ?>

                                                    In Stock

                                                </span>


                                            <?php endif; ?>


                                        </td>



                                        <!-- AUTH CODE -->

                                        <td>

                                            <code class="admin-auth-code">

                                                                                            <?php
                                                                                            echo htmlspecialchars(
                                                                                                $product[
                                                                                                    "authenticity_code"
                                                                                                ]
                                                                                            );
                                                                                            ?>

                                                                                        </code>

                                        </td>



                                        <!-- ACTIONS -->

                                        <td>

                                            <div class="admin-product-actions">


                                                <a href="edit_product.php?id=<?php
                                                echo (int) 
                                                    $product[
                                                        "product_id"
                                                    ];
                                                ?>" class="admin-edit-button" title="Edit Product">

                                                    <i class="fa-regular fa-pen-to-square"></i>

                                                </a>


                                                <form action="delete_product.php" method="POST" class="admin-delete-form"
                                                    onsubmit="return confirm('Are you sure you want to delete this product?');">

                                                    <input type="hidden" name="product_id" value="<?php
                                                    echo (int) 
                                                        $product["product_id"];
                                                    ?>">


                                                    <button type="submit" class="admin-delete-button" title="Delete Product">

                                                        <i class="fa-regular fa-trash-can"></i>

                                                    </button>

                                                </form>


                                            </div>

                                        </td>


                                    </tr>


                                <?php endwhile; ?>


                            </tbody>


                        </table>


                    </div>


                <?php else: ?>


                    <div class="admin-empty">

                        No products are currently available.

                    </div>


                <?php endif; ?>


            </section>


        </main>


    </div>


</body>

</html>