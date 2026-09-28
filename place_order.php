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


if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: cart.php");
    exit;
}


$user_id =
    (int) $_SESSION["user_id"];


$shipping_address =
    trim(
        $_POST["shipping_address"]
        ?? ""
    );


if ($shipping_address === "") {

    $_SESSION["checkout_error"] =
        "Please enter your shipping address.";

    header("Location: checkout.php");
    exit;
}


/* ======================================
   BEGIN ORDER TRANSACTION
====================================== */

$conn->begin_transaction();


try {

    /* FIND CART */

    $cart_sql =
        "SELECT
            cart_id,
            total_amount
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

        throw new Exception(
            "Your cart could not be found."
        );
    }


    $cart =
        $cart_result->fetch_assoc();


    $cart_id =
        (int) $cart["cart_id"];


    $cart_stmt->close();



    /* LOAD CURRENT CART ITEMS */

    $item_sql =
        "SELECT
            ci.product_id,
            ci.quantity,

            p.product_name,
            p.price,
            p.stock_quantity

         FROM cart_items ci

         INNER JOIN products p
            ON ci.product_id = p.product_id

         WHERE ci.cart_id = ?

         FOR UPDATE";


    $item_stmt =
        $conn->prepare($item_sql);


    $item_stmt->bind_param(
        "i",
        $cart_id
    );


    $item_stmt->execute();


    $item_result =
        $item_stmt->get_result();


    if ($item_result->num_rows === 0) {

        throw new Exception(
            "Your cart is empty."
        );
    }



    $items = [];

    $order_total = 0;


    while (
        $item =
        $item_result->fetch_assoc()
    ) {

        $quantity =
            (int) $item["quantity"];

        $stock =
            (int) $item["stock_quantity"];

        $unit_price =
            (float) $item["price"];


        if ($quantity > $stock) {

            throw new Exception(
                $item["product_name"] .
                " does not have enough stock."
            );
        }


        $subtotal =
            $quantity *
            $unit_price;


        $order_total +=
            $subtotal;


        $items[] = [

            "product_id" =>
                (int) $item["product_id"],

            "quantity" =>
                $quantity,

            "unit_price" =>
                $unit_price,

            "subtotal" =>
                $subtotal
        ];
    }


    $item_stmt->close();



    /* CREATE ORDER */

    $order_status =
        "Pending";


    $order_sql =
        "INSERT INTO orders
        (
            user_id,
            total_amount,
            order_status,
            shipping_address
        )
        VALUES (?, ?, ?, ?)";


    $order_stmt =
        $conn->prepare($order_sql);


    $order_stmt->bind_param(
        "idss",
        $user_id,
        $order_total,
        $order_status,
        $shipping_address
    );


    $order_stmt->execute();


    $order_id =
        $conn->insert_id;


    $order_stmt->close();



    /* CREATE ORDER DETAILS */

    $detail_sql =
        "INSERT INTO order_details
        (
            order_id,
            product_id,
            quantity,
            unit_price,
            subtotal
        )
        VALUES (?, ?, ?, ?, ?)";


    $detail_stmt =
        $conn->prepare($detail_sql);



    /* REDUCE PRODUCT STOCK */

    $stock_sql =
        "UPDATE products

         SET stock_quantity =
             stock_quantity - ?

         WHERE
            product_id = ?
            AND stock_quantity >= ?";


    $stock_stmt =
        $conn->prepare($stock_sql);



    foreach ($items as $item) {

        $detail_stmt->bind_param(
            "iiidd",
            $order_id,
            $item["product_id"],
            $item["quantity"],
            $item["unit_price"],
            $item["subtotal"]
        );


        $detail_stmt->execute();



        $stock_stmt->bind_param(
            "iii",
            $item["quantity"],
            $item["product_id"],
            $item["quantity"]
        );


        $stock_stmt->execute();


        if ($stock_stmt->affected_rows !== 1) {

            throw new Exception(
                "Stock changed while processing your order. Please try again."
            );
        }
    }


    $detail_stmt->close();

    $stock_stmt->close();



    /* CLEAR CART ITEMS */

    $clear_sql =
        "DELETE FROM cart_items
         WHERE cart_id = ?";


    $clear_stmt =
        $conn->prepare($clear_sql);


    $clear_stmt->bind_param(
        "i",
        $cart_id
    );


    $clear_stmt->execute();

    $clear_stmt->close();



    /* RESET CART TOTAL */

    $reset_sql =
        "UPDATE carts
         SET total_amount = 0
         WHERE cart_id = ?";


    $reset_stmt =
        $conn->prepare($reset_sql);


    $reset_stmt->bind_param(
        "i",
        $cart_id
    );


    $reset_stmt->execute();

    $reset_stmt->close();



    /* COMPLETE */

    $conn->commit();


    $_SESSION["last_order_id"] =
        $order_id;


    header(
        "Location: order_success.php"
    );

    exit;


} catch (Exception $e) {

    $conn->rollback();


    $_SESSION["checkout_error"] =
        $e->getMessage();


    header(
        "Location: checkout.php"
    );

    exit;
}

?>