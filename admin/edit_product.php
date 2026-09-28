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
   GET PRODUCT ID
====================================== */

$product_id =
    isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;


if ($product_id <= 0) {

    header("Location: products.php");
    exit;
}


/* ======================================
   LOAD PRODUCT
====================================== */

$product_sql =
    "SELECT
        product_id,
        category_id,
        product_name,
        brand,
        description,
        price,
        stock_quantity,
        authenticity_code,
        image_url

     FROM products

     WHERE product_id = ?

     LIMIT 1";


$product_stmt =
    $conn->prepare($product_sql);


$product_stmt->bind_param(
    "i",
    $product_id
);


$product_stmt->execute();


$product_result =
    $product_stmt->get_result();


if ($product_result->num_rows !== 1) {

    $product_stmt->close();

    header("Location: products.php");
    exit;
}


$product =
    $product_result->fetch_assoc();

$product_stmt->close();


/* ======================================
   FORM VALUES
====================================== */

$product_name =
    $product["product_name"];

$brand =
    $product["brand"];

$description =
    $product["description"];

$price =
    $product["price"];

$stock_quantity =
    $product["stock_quantity"];

$authenticity_code =
    $product["authenticity_code"];

$image_url =
    $product["image_url"];

$category_id =
    (int) $product["category_id"];


$error = "";


/* ======================================
   UPDATE PRODUCT
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


    /* VALIDATION */

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

    }


    /* CHECK CATEGORY */

    if ($error === "") {

        $category_sql =
            "SELECT category_id
             FROM categories
             WHERE category_id = ?
             LIMIT 1";


        $category_stmt =
            $conn->prepare($category_sql);


        $category_stmt->bind_param(
            "i",
            $category_id
        );


        $category_stmt->execute();


        $category_result =
            $category_stmt->get_result();


        if ($category_result->num_rows !== 1) {

            $error =
                "Please select a valid category.";
        }


        $category_stmt->close();
    }


    /* CHECK DUPLICATE AUTH CODE */

    if ($error === "") {

        $code_sql =
            "SELECT product_id

             FROM products

             WHERE
                authenticity_code = ?
                AND product_id != ?

             LIMIT 1";


        $code_stmt =
            $conn->prepare($code_sql);


        $code_stmt->bind_param(
            "si",
            $authenticity_code,
            $product_id
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


    /* UPDATE */

    if ($error === "") {

        $price_value =
            (float) $price;

        $stock_value =
            (int) $stock_quantity;


        $update_sql =
            "UPDATE products

             SET
                category_id = ?,
                product_name = ?,
                brand = ?,
                description = ?,
                price = ?,
                stock_quantity = ?,
                authenticity_code = ?,
                image_url = ?

             WHERE product_id = ?";


        $update_stmt =
            $conn->prepare($update_sql);


        $update_stmt->bind_param(
            "isssdissi",
            $category_id,
            $product_name,
            $brand,
            $description,
            $price_value,
            $stock_value,
            $authenticity_code,
            $image_url,
            $product_id
        );


        $update_stmt->execute();

        $update_stmt->close();


        $_SESSION["admin_product_message"] =
            "Product updated successfully.";

        $_SESSION["admin_product_message_type"] =
            "success";


        header(
            "Location: products.php"
        );

        exit;
    }
}


/* ======================================
   LOAD CATEGORIES
====================================== */

$categories =
    $conn->query(
        "SELECT
            category_id,
            category_name

         FROM categories

         ORDER BY category_name ASC"
    );

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Edit Product | Authentic Perfume
    </title>


    <link rel="stylesheet" href="../assets/css/style.css?v=23">


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>


<body class="admin-body">


    <div class="admin-layout">


        <!-- SIDEBAR -->

        <aside class="admin-sidebar">


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



    <!-- MAIN -->

    <main class="admin-main">


        <div class="admin-form-heading">


            <div>

                <p>
                    PRODUCT MANAGEMENT
                </p>

                <h1>
                    Edit Product
                </h1>

                <span>
                    Update product information and stock levels.
                </span>

            </div>


            <a href="products.php" class="admin-back-products">
                ← Back to Products
            </a>


        </div>



        <?php if ($error !== ""): ?>

            <div class="admin-form-message error">

                <?php
                echo htmlspecialchars(
                    $error
                );
                ?>

            </div>

        <?php endif; ?>



        <section class="admin-product-form-card">


            <div class="admin-product-form-title">

                <div class="admin-form-title-icon">

                    <i class="fa-regular fa-pen-to-square"></i>

                </div>


                <div>

                    <p>
                        PRODUCT INFORMATION
                    </p>

                    <h2>
                        Edit Perfume
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
                    ?>" maxlength="150" required>

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
                    ?>" maxlength="100" required>

                </div>



                <!-- CATEGORY -->

                <div class="admin-form-group">

                    <label for="category_id">
                        Category
                    </label>

                    <select id="category_id" name="category_id" required>


                        <?php while (
                            $category =
                            $categories->fetch_assoc()
                        ): ?>

                            <option value="<?php
                            echo (int) 
                                $category["category_id"];
                            ?>" <?php
                            echo (
                                (int) 
                                $category["category_id"]
                                ===
                                (int) 
                                $category_id
                            )
                                ? "selected"
                                : "";
                            ?>>

                                <?php
                                echo htmlspecialchars(
                                    $category["category_name"]
                                );
                                ?>

                            </option>

                        <?php endwhile; ?>


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
                    ?>" min="0" step="0.01" required>

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
                    ?>" min="0" step="1" required>

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
                    ?>" maxlength="100" required>

                </div>



                <!-- DESCRIPTION -->

                <div class="admin-form-group full">

                    <label for="description">
                        Description
                    </label>

                    <textarea id="description" name="description" rows="5" required><?php
                    echo htmlspecialchars(
                        $description
                    );
                    ?></textarea>

                </div>



                <!-- IMAGE URL -->

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
                    ?>" maxlength="255">

                </div>



                <!-- ACTIONS -->

                <div class="admin-form-actions">


                    <a href="products.php" class="admin-cancel-button">
                        Cancel
                    </a>


                    <button type="submit" class="admin-save-product">

                        <i class="fa-solid fa-floppy-disk"></i>

                        Save Changes

                    </button>


                </div>


            </form>


        </section>


    </main>


    </div>


</body>

</html>