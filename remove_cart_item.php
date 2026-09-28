<?php

require_once 'config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* LOGIN REQUIRED */

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit;
}


if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: cart.php");
    exit;
}


$user_id =
    (int) $_SESSION["user_id"];

$cart_item_id =
    isset($_POST["cart_item_id"])
    ? (int) $_POST["cart_item_id"]
    : 0;


if ($cart_item_id <= 0) {

    header("Location: cart.php");
    exit;
}


/* FIND USER CART */

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


if ($cart_result->num_rows !== 1) {

    $cart_stmt->close();

    header("Location: cart.php");
    exit;
}


$cart =
    $cart_result->fetch_assoc();

$cart_id =
    (int) $cart["cart_id"];

$cart_stmt->close();


/* REMOVE ITEM */

$delete_sql =
    "DELETE FROM cart_items
     WHERE
        cart_item_id = ?
        AND cart_id = ?";


$delete_stmt =
    $conn->prepare($delete_sql);


$delete_stmt->bind_param(
    "ii",
    $cart_item_id,
    $cart_id
);


$delete_stmt->execute();

$delete_stmt->close();


/* RECALCULATE TOTAL */

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


$update_sql =
    "UPDATE carts
     SET total_amount = ?
     WHERE cart_id = ?";


$update_stmt =
    $conn->prepare($update_sql);


$update_stmt->bind_param(
    "di",
    $cart_total,
    $cart_id
);


$update_stmt->execute();

$update_stmt->close();


$_SESSION["cart_message"] =
    "The product was removed from your cart.";

$_SESSION["cart_message_type"] =
    "success";


header("Location: cart.php");
exit;

?>