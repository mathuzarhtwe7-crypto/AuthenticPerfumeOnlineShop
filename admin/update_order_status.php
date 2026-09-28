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

    header("Location: orders.php");
    exit;
}


/* ======================================
   GET VALUES
====================================== */

$order_id =
    isset($_POST["order_id"])
    ? (int) $_POST["order_id"]
    : 0;


$order_status =
    trim(
        $_POST["order_status"]
        ?? ""
    );


/* ======================================
   VALID STATUS
====================================== */

$allowed_statuses = [
    "Pending",
    "Processing",
    "Shipped",
    "Delivered"
];


if (
    $order_id <= 0 ||
    !in_array(
        $order_status,
        $allowed_statuses,
        true
    )
) {

    $_SESSION["admin_order_message"] =
        "Invalid order status.";

    $_SESSION["admin_order_message_type"] =
        "error";

    header("Location: orders.php");
    exit;
}


/* ======================================
   UPDATE ORDER
====================================== */

$update_sql =
    "UPDATE orders

     SET order_status = ?

     WHERE order_id = ?";


$update_stmt =
    $conn->prepare($update_sql);


$update_stmt->bind_param(
    "si",
    $order_status,
    $order_id
);


$update_stmt->execute();


if ($update_stmt->affected_rows >= 0) {

    $_SESSION["admin_order_message"] =
        "Order #" .
        $order_id .
        " status updated to " .
        $order_status .
        ".";

    $_SESSION["admin_order_message_type"] =
        "success";

}


$update_stmt->close();


header("Location: orders.php");
exit;

?>