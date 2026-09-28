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
   POST ONLY
====================================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: products.php");
    exit;
}


/* ======================================
   GET PRODUCT ID
====================================== */

$product_id =
    isset($_POST["product_id"])
    ? (int) $_POST["product_id"]
    : 0;


if ($product_id <= 0) {

    header("Location: products.php");
    exit;
}


/* ======================================
   CHECK PRODUCT EXISTS
====================================== */

$product_sql =
    "SELECT product_name
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

    $_SESSION["admin_product_message"] =
        "Product not found.";

    $_SESSION["admin_product_message_type"] =
        "error";

    header("Location: products.php");
    exit;
}


$product =
    $product_result->fetch_assoc();


$product_name =
    $product["product_name"];


$product_stmt->close();


/* ======================================
   CHECK ORDER HISTORY
====================================== */

$order_check_sql =
    "SELECT COUNT(*) AS total
     FROM order_details
     WHERE product_id = ?";


$order_check_stmt =
    $conn->prepare($order_check_sql);


$order_check_stmt->bind_param(
    "i",
    $product_id
);


$order_check_stmt->execute();


$order_check_result =
    $order_check_stmt->get_result();


$order_check =
    $order_check_result->fetch_assoc();


$order_reference_count =
    (int) $order_check["total"];


$order_check_stmt->close();


if ($order_reference_count > 0) {

    $_SESSION["admin_product_message"] =
        "This product cannot be deleted because it is part of existing order history.";

    $_SESSION["admin_product_message_type"] =
        "error";


    header("Location: products.php");
    exit;
}


/* ======================================
   CHECK CUSTOMER CARTS
====================================== */

$cart_check_sql =
    "SELECT COUNT(*) AS total
     FROM cart_items
     WHERE product_id = ?";


$cart_check_stmt =
    $conn->prepare($cart_check_sql);


$cart_check_stmt->bind_param(
    "i",
    $product_id
);


$cart_check_stmt->execute();


$cart_check_result =
    $cart_check_stmt->get_result();


$cart_check =
    $cart_check_result->fetch_assoc();


$cart_reference_count =
    (int) $cart_check["total"];


$cart_check_stmt->close();


if ($cart_reference_count > 0) {

    $_SESSION["admin_product_message"] =
        "This product cannot be deleted because it is currently in a customer cart.";

    $_SESSION["admin_product_message_type"] =
        "error";


    header("Location: products.php");
    exit;
}


/* ======================================
   DELETE PRODUCT
====================================== */

$delete_sql =
    "DELETE FROM products
     WHERE product_id = ?";


$delete_stmt =
    $conn->prepare($delete_sql);


$delete_stmt->bind_param(
    "i",
    $product_id
);


$delete_stmt->execute();


if ($delete_stmt->affected_rows === 1) {

    $_SESSION["admin_product_message"] =
        $product_name .
        " deleted successfully.";

    $_SESSION["admin_product_message_type"] =
        "success";

} else {

    $_SESSION["admin_product_message"] =
        "The product could not be deleted.";

    $_SESSION["admin_product_message_type"] =
        "error";
}


$delete_stmt->close();


header("Location: products.php");
exit;

?>