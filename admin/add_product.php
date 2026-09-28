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
   LOAD CATEGORIES
====================================== */

$category_result =
    $conn->query(
        "SELECT
            category_id,
            category_name
         FROM categories
         ORDER BY category_name ASC"
    );


$error = "";


/* KEEP FORM VALUES */

$product_name = "";
$brand = "";
$description = "";
$price = "";
$stock_quantity = "";
$authenticity_code = "";
$image_url = "";
$category_id = 0;


/* ======================================
   ADD PRODUCT
====================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $product_name =
        trim($_POST["product_name"] ?? "");

    $brand =
        trim($_POST["brand"] ?? "");

    $description =
        trim($_POST["description"] ?? "");

    $price =
        trim($_POST["price"] ?? "");

    $stock_quantity =
        trim($_POST["stock_quantity"] ?? "");

    $authenticity_code =
        strtoupper(
            trim(
                $_POST["authenticity_code"]
                ?? ""
            )
        );

    $image_url =
        trim($_POST["image_url"] ?? "");

    $category_id =
        isset($_POST["category_id"])
        ? (int) $_POST["category_id"]
        : 0;


    /* BASIC VALIDATION */

    if (
        $product_name === "" ||
        $brand === "" ||
        $description === "" ||
        $price === "" ||
        $stock_quantity === "" ||
        $authenticity_code === "" ||
        $category_id <= 0
    ) {

        $error =
            "Please complete all required product fields.";

    } elseif (
        !is_numeric($price) ||
        (float) $price < 0
    ) {

        $error =
            "Please enter a valid product price.";

    } elseif (
        filter_var(
            $stock_quantity,
            FILTER_VALIDATE_INT
        ) === false ||
        (int) $stock_quantity < 0
    ) {

        $error =
            "Please enter a valid stock quantity.";

    } else {


        /* ==================================
           CHECK CATEGORY
        =================================== */

        $category_check_sql =
            "SELECT category_id
             FROM categories
             WHERE category_id = ?
             LIMIT 1";


        $category_check_stmt =
            $conn->prepare(
                $category_check_sql
            );


        $category_check_stmt->bind_param(
            "i",
            $category_id
        );


        $category_check_stmt->execute();


        $category_check_result =
            $category_check_stmt
                ->get_result();


        if (
            $category_check_result->num_rows !== 1
        ) {

            $error =
                "Please select a valid category.";

        }


        $category_check_stmt->close();



        /* ==================================
           CHECK AUTHENTICITY CODE
        =================================== */

        if ($error === "") {

            $code_sql =
                "SELECT product_id
                 FROM products
                 WHERE authenticity_code = ?
                 LIMIT 1";


            $code_stmt =
                $conn->prepare($code_sql);


            $code_stmt->bind_param(
                "s",
                $authenticity_code
            );


            $code_stmt->execute();


            $code_result =
                $code_stmt->get_result();


            if ($code_result->num_rows > 0) {

                $error =
                    "This authenticity code is already being used.";
            }


            $code_stmt->close();
        }



        /* ==================================
           INSERT PRODUCT
        =================================== */

        if ($error === "") {

            $price_value =
                (float) $price;

            $stock_value =
                (int) $stock_quantity;


            $insert_sql =
                "INSERT INTO products
                (
                    category_id,
                    product_name,
                    brand,
                    description,
                    price,
                    stock_quantity,
                    authenticity_code,
                    image_url
                )
                VALUES
                (
                    ?, ?, ?, ?, ?, ?, ?, ?
                )";


            $insert_stmt =
                $conn->prepare(
                    $insert_sql
                );


            $insert_stmt->bind_param(
                "isssdiss",
                $category_id,
                $product_name,
                $brand,
                $description,
                $price_value,
                $stock_value,
                $authenticity_code,
                $image_url
            );


            $insert_stmt->execute();


            $insert_stmt->close();


            $_SESSION["admin_product_message"] =
                "Product added successfully.";

            $_SESSION["admin_product_message_type"] =
                "success";


            header(
                "Location: products.php"
            );

            exit;
        }
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Add Product | Authentic Perfume
    </title>


    <link rel="stylesheet" href="../assets/css/style.css?v=23">


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>


<body class="admin-body">


    <div class="admin-layout">


        <!-- ================================
         SIDEBAR
    ================================= -->

        <aside class="admin-sidebar">


            <div class="admin-sidebar-brand">

                <div class="admin-sidebar-logo">
                    ✦
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



        <!-- ================================
         MAIN
    ================================= -->

        <main class="admin-main">


            <div class="admin-form-heading">


                <div>

                    <p>
                        PRODUCT MANAGEMENT
                    </p>

                    <h1>
                        Add Product
                    </h1>

                    <span>
                        Add a new perfume to the product catalogue.
                    </span>

                </div>


                <a href="products.php" class="admin-back-products">
                    ← Back to Products
                </a>


            </div>



            <!-- ERROR -->

            <?php if ($error !== ""): ?>

                <div class="admin-form-message error">

                    <?php
                    echo htmlspecialchars(
                        $error
                    );
                    ?>

                </div>

            <?php endif; ?>



            <!-- PRODUCT FORM -->

            <section class="admin-product-form-card">


                <div class="admin-product-form-title">

                    <div class="admin-form-title-icon">

                        <i class="fa-solid fa-plus"></i>

                    </div>


                    <div>

                        <p>
                            PRODUCT INFORMATION
                        </p>

                        <h2>
                            New Perfume
                        </h2>

                    </div>

                </div>



                <form method="POST" class="admin-product-form">


                    <!-- PRODUCT NAME -->

                    <div class="admin-form-group">

                        <label for="product_name">
                            Product Name
                        </label>


                        <input type="text" id="product_name" name="product_name" value="<?php
                        echo htmlspecialchars(
                            $product_name
                        );
                        ?>" placeholder="Example: Royal Oud" maxlength="150" required>

                    </div>



                    <!-- BRAND -->

                    <div class="admin-form-group">

                        <label for="brand">
                            Brand
                        </label>


                        <input type="text" id="brand" name="brand" value="<?php
                        echo htmlspecialchars(
                            $brand
                        );
                        ?>" placeholder="Example: Maison Luxe" maxlength="100" required>

                    </div>



                    <!-- CATEGORY -->

                    <div class="admin-form-group">

                        <label for="category_id">
                            Category
                        </label>


                        <select id="category_id" name="category_id" required>

                            <option value="">
                                Select Category
                            </option>


                            <?php if ($category_result): ?>

                                <?php while (
                                    $category =
                                    $category_result->fetch_assoc()
                                ): ?>

                                    <option value="<?php
                                    echo (int) 
                                        $category[
                                            "category_id"
                                        ];
                                    ?>" <?php
                                    echo (
                                        $category_id ===
                                        (int) 
                                        $category[
                                            "category_id"
                                        ]
                                    )
                                        ? "selected"
                                        : "";
                                    ?>>

                                        <?php
                                        echo htmlspecialchars(
                                            $category[
                                                "category_name"
                                            ]
                                        );
                                        ?>

                                    </option>

                                <?php endwhile; ?>

                            <?php endif; ?>


                        </select>

                    </div>



                    <!-- PRICE -->

                    <div class="admin-form-group">

                        <label for="price">
                            Price ($)
                        </label>


                        <input type="number" id="price" name="price" value="<?php
                        echo htmlspecialchars(
                            $price
                        );
                        ?>" placeholder="0.00" min="0" step="0.01" required>

                    </div>



                    <!-- STOCK -->

                    <div class="admin-form-group">

                        <label for="stock_quantity">
                            Stock Quantity
                        </label>


                        <input type="number" id="stock_quantity" name="stock_quantity" value="<?php
                        echo htmlspecialchars(
                            $stock_quantity
                        );
                        ?>" placeholder="0" min="0" step="1" required>

                    </div>



                    <!-- AUTH CODE -->

                    <div class="admin-form-group">

                        <label for="authenticity_code">
                            Authenticity Code
                        </label>


                        <input type="text" id="authenticity_code" name="authenticity_code" value="<?php
                        echo htmlspecialchars(
                            $authenticity_code
                        );
                        ?>" placeholder="Example: AUTH-RO-007" maxlength="100" required>

                    </div>



                    <!-- DESCRIPTION -->

                    <div class="admin-form-group full">

                        <label for="description">
                            Description
                        </label>


                        <textarea id="description" name="description" rows="5"
                            placeholder="Enter a short product description" required><?php
                            echo htmlspecialchars(
                                $description
                            );
                            ?></textarea>

                    </div>



                    <!-- IMAGE -->

                    <div class="admin-form-group full">

                        <label for="image_url">

                            Image URL

                            <span>
                                (Optional)
                            </span>

                        </label>


                        <input type="text" id="image_url" name="image_url" value="<?php
                        echo htmlspecialchars(
                            $image_url
                        );
                        ?>" placeholder="Example: assets/images/royal-oud.jpg" maxlength="255">


                        <small>
                            You can leave this empty until product images are added.
                        </small>

                    </div>



                    <!-- ACTIONS -->

                    <div class="admin-form-actions">


                        <a href="products.php" class="admin-cancel-button">
                            Cancel
                        </a>


                        <button type="submit" class="admin-save-product">

                            <i class="fa-solid fa-plus"></i>

                            Add Product

                        </button>


                    </div>


                </form>


            </section>


        </main>


    </div>


</body>

</html>