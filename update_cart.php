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


if (
    $_SERVER["REQUEST_METHOD"] !== "POST" ||
    !isset($_POST["quantities"]) ||
    !is_array($_POST["quantities"])
) {

    header("Location: cart.php");
    exit;
}


$user_id =
    (int) $_SESSION["user_id"];


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


/* ======================================
   UPDATE ITEMS
====================================== */

$conn->begin_transaction();


try {

    $get_item_sql =
        "SELECT
            ci.cart_item_id,
            ci.unit_price,
            p.stock_quantity

         FROM cart_items ci

         INNER JOIN products p
            ON ci.product_id = p.product_id

         WHERE
            ci.cart_item_id = ?
            AND ci.cart_id = ?

         LIMIT 1";


    $get_item_stmt =
        $conn->prepare(
            $get_item_sql
        );


    $update_item_sql =
        "UPDATE cart_items

         SET
            quantity = ?,
            subtotal = ?

         WHERE
            cart_item_id = ?
            AND cart_id = ?";


    $update_item_stmt =
        $conn->prepare(
            $update_item_sql
        );


    foreach (
        $_POST["quantities"]
        as $cart_item_id => $quantity
    ) {

        $cart_item_id =
            (int) $cart_item_id;

        $quantity =
            (int) $quantity;


        if (
            $cart_item_id <= 0 ||
            $quantity <= 0
        ) {

            throw new Exception(
                "Invalid cart quantity."
            );
        }


        $get_item_stmt->bind_param(
            "ii",
            $cart_item_id,
            $cart_id
        );


        $get_item_stmt->execute();


        $item_result =
            $get_item_stmt->get_result();


        if ($item_result->num_rows !== 1) {

            continue;
        }


        $item =
            $item_result->fetch_assoc();


        if (
            $quantity >
            $item["stock_quantity"]
        ) {

            throw new Exception(
                "One or more quantities exceed available stock."
            );
        }


        $unit_price =
            (float) $item["unit_price"];


        $subtotal =
            $quantity *
            $unit_price;


        $update_item_stmt->bind_param(
            "idii",
            $quantity,
            $subtotal,
            $cart_item_id,
            $cart_id
        );


        $update_item_stmt->execute();
    }


    $get_item_stmt->close();

    $update_item_stmt->close();



    /* RECALCULATE CART TOTAL */

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



    $update_total_sql =
        "UPDATE carts
         SET total_amount = ?
         WHERE cart_id = ?";


    $update_total_stmt =
        $conn->prepare(
            $update_total_sql
        );


    $update_total_stmt->bind_param(
        "di",
        $cart_total,
        $cart_id
    );


    $update_total_stmt->execute();

    $update_total_stmt->close();


    $conn->commit();


    $_SESSION["cart_message"] =
        "Your cart has been updated.";

    $_SESSION["cart_message_type"] =
        "success";


} catch (Exception $e) {

    $conn->rollback();


    $_SESSION["cart_message"] =
        $e->getMessage();

    $_SESSION["cart_message_type"] =
        "error";
}


header("Location: cart.php");
exit;

?>