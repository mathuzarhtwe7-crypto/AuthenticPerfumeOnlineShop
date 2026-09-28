<?php

require_once 'config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* ======================================
   LOGIN REQUIRED
====================================== */

if (!isset($_SESSION["user_id"])) {

    $_SESSION["cart_message"] =
        "Please login before adding products to your cart.";

    header("Location: login.php");
    exit;
}


/* ======================================
   POST REQUEST ONLY
====================================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: shop.php");
    exit;
}


$user_id =
    (int) $_SESSION["user_id"];

$product_id =
    isset($_POST["product_id"])
    ? (int) $_POST["product_id"]
    : 0;

$quantity =
    isset($_POST["quantity"])
    ? (int) $_POST["quantity"]
    : 1;


if ($product_id <= 0 || $quantity <= 0) {

    header("Location: shop.php");
    exit;
}


/* ======================================
   GET PRODUCT
====================================== */

$product_sql =
    "SELECT
        product_id,
        product_name,
        price,
        stock_quantity

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

    header("Location: shop.php");
    exit;
}


$product =
    $product_result->fetch_assoc();

$product_stmt->close();


/* ======================================
   CHECK STOCK
====================================== */

if ($quantity > $product["stock_quantity"]) {

    $_SESSION["cart_message"] =
        "Requested quantity exceeds available stock.";

    $_SESSION["cart_message_type"] = "error";

    header(
        "Location: product.php?id=" .
        $product_id
    );

    exit;
}

/* ======================================
   FIND OR CREATE CART
====================================== */

$cart_sql =
    "SELECT cart_id
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

} else {

    $create_cart_sql =
        "INSERT INTO carts
        (
            user_id,
            total_amount
        )
        VALUES (?, 0)";


    $create_cart_stmt =
        $conn->prepare(
            $create_cart_sql
        );


    $create_cart_stmt->bind_param(
        "i",
        $user_id
    );


    $create_cart_stmt->execute();


    $cart_id =
        $conn->insert_id;


    $create_cart_stmt->close();
}


$cart_stmt->close();


/* ======================================
   CHECK EXISTING CART ITEM
====================================== */

$item_sql =
    "SELECT
        cart_item_id,
        quantity

     FROM cart_items

     WHERE
        cart_id = ?
        AND product_id = ?

     LIMIT 1";


$item_stmt =
    $conn->prepare($item_sql);


$item_stmt->bind_param(
    "ii",
    $cart_id,
    $product_id
);


$item_stmt->execute();

$item_result =
    $item_stmt->get_result();


$unit_price =
    (float) $product["price"];


if ($item_result->num_rows === 1) {

    /* PRODUCT ALREADY IN CART */

    $existing_item =
        $item_result->fetch_assoc();


    $new_quantity =
        (int) $existing_item["quantity"]
        + $quantity;


    /* CHECK TOTAL QUANTITY AGAINST STOCK */

    if (
        $new_quantity >
        $product["stock_quantity"]
    ) {

        $item_stmt->close();

        $_SESSION["cart_message"] =
            "You cannot add more than the available stock.";

        $_SESSION["cart_message_type"] = "error";

        header(
            "Location: product.php?id=" .
            $product_id
        );

        exit;
    }


    $subtotal =
        $new_quantity *
        $unit_price;


    $update_sql =
        "UPDATE cart_items

         SET
            quantity = ?,
            unit_price = ?,
            subtotal = ?

         WHERE cart_item_id = ?";


    $update_stmt =
        $conn->prepare($update_sql);


    $update_stmt->bind_param(
        "iddi",
        $new_quantity,
        $unit_price,
        $subtotal,
        $existing_item["cart_item_id"]
    );


    $update_stmt->execute();

    $update_stmt->close();


} else {

    /* NEW CART ITEM */

    $subtotal =
        $quantity *
        $unit_price;


    $insert_sql =
        "INSERT INTO cart_items
        (
            cart_id,
            product_id,
            quantity,
            unit_price,
            subtotal
        )
        VALUES (?, ?, ?, ?, ?)";


    $insert_stmt =
        $conn->prepare($insert_sql);


    $insert_stmt->bind_param(
        "iiidd",
        $cart_id,
        $product_id,
        $quantity,
        $unit_price,
        $subtotal
    );


    $insert_stmt->execute();

    $insert_stmt->close();
}


$item_stmt->close();


/* ======================================
   UPDATE CART TOTAL
====================================== */

$total_sql =
    "SELECT
        COALESCE(
            SUM(subtotal),
            0
        ) AS cart_total

     FROM cart_items

     WHERE cart_id = ?";


$total_stmt =
    $conn->prepare($total_sql);


$total_stmt->bind_param(
    "i",
    $cart_id
);


$total_stmt->execute();


$total_result =
    $total_stmt->get_result();


$total_row =
    $total_result->fetch_assoc();


$cart_total =
    (float) 
    $total_row["cart_total"];


$total_stmt->close();



$update_cart_sql =
    "UPDATE carts
     SET total_amount = ?
     WHERE cart_id = ?";


$update_cart_stmt =
    $conn->prepare(
        $update_cart_sql
    );


$update_cart_stmt->bind_param(
    "di",
    $cart_total,
    $cart_id
);


$update_cart_stmt->execute();

$update_cart_stmt->close();


/* ======================================
   SUCCESS
====================================== */

$_SESSION["cart_message"] =
    $product["product_name"] .
    " was added to your cart.";

$_SESSION["cart_message_type"] = "success";

header(
    "Location: product.php?id=" .
    $product_id
);

exit;

?>