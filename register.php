<?php

require_once 'config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    /* -----------------------------
       BASIC VALIDATION
    ----------------------------- */

    if (
        empty($full_name) ||
        empty($email) ||
        empty($password) ||
        empty($confirm_password)
    ) {

        $message = "Please complete all required fields.";
        $message_type = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    } elseif (strlen($password) < 8) {

        $message = "Password must contain at least 8 characters.";
        $message_type = "error";

    } elseif ($password !== $confirm_password) {

        $message = "Passwords do not match.";
        $message_type = "error";

    } else {

        /* -----------------------------
           CHECK DUPLICATE EMAIL
        ----------------------------- */

        $check_sql =
            "SELECT user_id
             FROM users
             WHERE email = ?";

        $check_stmt = $conn->prepare($check_sql);

        $check_stmt->bind_param(
            "s",
            $email
        );

        $check_stmt->execute();

        $check_result =
            $check_stmt->get_result();


        if ($check_result->num_rows > 0) {

            $message =
                "An account with this email already exists.";

            $message_type =
                "error";

        } else {

            /* -----------------------------
               HASH PASSWORD
            ----------------------------- */

            $password_hash =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );


            /* -----------------------------
               INSERT CUSTOMER
            ----------------------------- */

            $role = "customer";

            $insert_sql =
                "INSERT INTO users
                (
                    full_name,
                    email,
                    password_hash,
                    phone,
                    role
                )
                VALUES (?, ?, ?, ?, ?)";

            $insert_stmt =
                $conn->prepare(
                    $insert_sql
                );

            $insert_stmt->bind_param(
                "sssss",
                $full_name,
                $email,
                $password_hash,
                $phone,
                $role
            );


            if ($insert_stmt->execute()) {

                $message =
                    "Account created successfully. You can now log in.";

                $message_type =
                    "success";

                /* Clear old form values after successful registration */
                $full_name = "";
                $email = "";
                $phone = "";

            } else {

                $message =
                    "Registration failed. Please try again.";

                $message_type =
                    "error";
            }

            $insert_stmt->close();
        }

        $check_stmt->close();
    }
}

require_once 'includes/header.php';

?>


<!-- ======================================
     REGISTER PAGE
====================================== -->

<section class="auth-page">

    <div class="auth-container">


        <!-- LEFT SIDE -->

        <div class="auth-intro">

            <p class="auth-eyebrow">
                JOIN AUTHENTIC PERFUME
            </p>

            <h1>
                Begin Your
                <br>
                Fragrance Journey
            </h1>

            <p class="auth-intro-text">
                Create your account to discover authentic luxury
                perfumes, manage your shopping cart and keep track
                of your orders.
            </p>


            <div class="auth-benefit">

                <i class="fa-solid fa-shield-halved"></i>

                <span>
                    Authentic products with secure account access
                </span>

            </div>


            <div class="auth-benefit">

                <i class="fa-solid fa-cart-shopping"></i>

                <span>
                    Save products and manage your shopping cart
                </span>

            </div>


            <div class="auth-benefit">

                <i class="fa-solid fa-box"></i>

                <span>
                    Place orders and view order information
                </span>

            </div>

        </div>



        <!-- REGISTER CARD -->

        <div class="auth-card">

            <div class="auth-card-heading">

                <p>
                    CREATE ACCOUNT
                </p>

                <h2>
                    Register
                </h2>

                <span>
                    Enter your details to create a customer account.
                </span>

            </div>


            <?php if (!empty($message)): ?>

                <div class="form-message
                    <?php echo $message_type; ?>">

                    <?php
                    echo htmlspecialchars($message);
                    ?>

                </div>

            <?php endif; ?>


            <form action="register.php" method="POST" class="auth-form">


                <!-- FULL NAME -->

                <div class="form-group">

                    <label for="full_name">
                        Full Name
                    </label>

                    <div class="input-wrapper">

                        <i class="fa-regular fa-user"></i>

                        <input type="text" id="full_name" name="full_name" placeholder="Enter your full name" value="<?php
                        echo isset($full_name)
                            ? htmlspecialchars($full_name)
                            : '';
                        ?>" required>

                    </div>

                </div>



                <!-- EMAIL -->

                <div class="form-group">

                    <label for="email">
                        Email Address
                    </label>

                    <div class="input-wrapper">

                        <i class="fa-regular fa-envelope"></i>

                        <input type="email" id="email" name="email" placeholder="Enter your email address" value="<?php
                        echo isset($email)
                            ? htmlspecialchars($email)
                            : '';
                        ?>" required>

                    </div>

                </div>



                <!-- PHONE -->

                <div class="form-group">

                    <label for="phone">
                        Phone Number
                        <span class="optional">
                            (Optional)
                        </span>
                    </label>

                    <div class="input-wrapper">

                        <i class="fa-solid fa-phone"></i>

                        <input type="text" id="phone" name="phone" placeholder="Enter your phone number" value="<?php
                        echo isset($phone)
                            ? htmlspecialchars($phone)
                            : '';
                        ?>">

                    </div>

                </div>



                <!-- PASSWORD -->

                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <div class="input-wrapper">

                        <i class="fa-solid fa-lock"></i>

                        <input type="password" id="password" name="password" placeholder="Minimum 8 characters"
                            minlength="8" required>

                    </div>

                </div>



                <!-- CONFIRM PASSWORD -->

                <div class="form-group">

                    <label for="confirm_password">
                        Confirm Password
                    </label>

                    <div class="input-wrapper">

                        <i class="fa-solid fa-lock"></i>

                        <input type="password" id="confirm_password" name="confirm_password"
                            placeholder="Enter your password again" minlength="8" required>

                    </div>

                </div>



                <button type="submit" class="auth-submit-button">
                    Create Account
                    <span>→</span>
                </button>


            </form>


            <div class="auth-switch">

                Already have an account?

                <a href="login.php">
                    Login
                </a>

            </div>

        </div>

    </div>

</section>


<?php
require_once 'includes/footer.php';
?>