<?php

require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* ALREADY LOGGED IN AS ADMIN */

if (
    isset($_SESSION["user_id"]) &&
    isset($_SESSION["role"]) &&
    $_SESSION["role"] === "admin"
) {

    header("Location: dashboard.php");
    exit;
}


$error = "";


/* ======================================
   ADMIN LOGIN
====================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email =
        trim($_POST["email"] ?? "");

    $password =
        $_POST["password"] ?? "";


    if (
        $email === "" ||
        $password === ""
    ) {

        $error =
            "Please enter your email and password.";

    } else {

        $sql =
            "SELECT
                user_id,
                full_name,
                email,
                password_hash,
                role

             FROM users

             WHERE email = ?

             LIMIT 1";


        $stmt =
            $conn->prepare($sql);


        $stmt->bind_param(
            "s",
            $email
        );


        $stmt->execute();


        $result =
            $stmt->get_result();


        if ($result->num_rows === 1) {

            $user =
                $result->fetch_assoc();


            if (
                $user["role"] === "admin" &&
                password_verify(
                    $password,
                    $user["password_hash"]
                )
            ) {

                session_regenerate_id(true);


                $_SESSION["user_id"] =
                    (int) $user["user_id"];

                $_SESSION["full_name"] =
                    $user["full_name"];

                $_SESSION["email"] =
                    $user["email"];

                $_SESSION["role"] =
                    "admin";


                header(
                    "Location: dashboard.php"
                );

                exit;

            } else {

                $error =
                    "Invalid admin email or password.";
            }

        } else {

            $error =
                "Invalid admin email or password.";
        }


        $stmt->close();
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Admin Login | Authentic Perfume
    </title>

    <link rel="stylesheet" href="../assets/css/style.css?v=21">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>


<body class="admin-login-body">


    <section class="admin-login-page">


        <div class="admin-login-card">


            <div class="admin-login-brand">

                <div class="admin-login-icon">

                    <i class="fa-solid fa-user-shield"></i>

                </div>


                <p>
                    AUTHENTIC PERFUME
                </p>


                <h1>
                    Admin Login
                </h1>


                <span>
                    Secure access for authorised administrators.
                </span>

            </div>



            <?php if ($error !== ""): ?>

                <div class="form-message error">

                    <?php
                    echo htmlspecialchars(
                        $error
                    );
                    ?>

                </div>

            <?php endif; ?>



            <form method="POST" class="admin-login-form">


                <div class="admin-field">

                    <label for="email">
                        Email Address
                    </label>


                    <div>

                        <i class="fa-regular fa-envelope"></i>

                        <input type="email" id="email" name="email" placeholder="Enter admin email" required>

                    </div>

                </div>



                <div class="admin-field">

                    <label for="password">
                        Password
                    </label>


                    <div>

                        <i class="fa-solid fa-lock"></i>

                        <input type="password" id="password" name="password" placeholder="Enter password" required>

                    </div>

                </div>



                <button type="submit" class="admin-login-button">

                    Login to Dashboard

                    <span>→</span>

                </button>


            </form>



            <a href="../index.php" class="admin-back-store">
                ← Back to Store
            </a>


        </div>


    </section>


</body>

</html>