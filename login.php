<?php

require_once 'config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = "";
$message_type = "";
$email = "";


/* Already logged in */
if (isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];


    /* -----------------------------
       BASIC VALIDATION
    ----------------------------- */

    if (empty($email) || empty($password)) {

        $message = "Please enter your email and password.";
        $message_type = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    } else {

        /* -----------------------------
           FIND USER
        ----------------------------- */

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

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "s",
            $email
        );

        $stmt->execute();

        $result = $stmt->get_result();


        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();


            /* -----------------------------
               VERIFY PASSWORD
            ----------------------------- */

            if (
                password_verify(
                    $password,
                    $user["password_hash"]
                )
            ) {

                /*
                 Customer Login only.
                 Admin login will be created separately.
                */

                if ($user["role"] !== "customer") {

                    $message =
                        "Please use the administrator login page.";

                    $message_type =
                        "error";

                } else {

                    /* Prevent session fixation */

                    session_regenerate_id(true);


                    /* Save user information */

                    $_SESSION["user_id"] =
                        $user["user_id"];

                    $_SESSION["full_name"] =
                        $user["full_name"];

                    $_SESSION["email"] =
                        $user["email"];

                    $_SESSION["role"] =
                        $user["role"];


                    /* Go to home page */

                    header("Location: index.php");
                    exit;
                }

            } else {

                $message =
                    "Invalid email or password.";

                $message_type =
                    "error";
            }

        } else {

            $message =
                "Invalid email or password.";

            $message_type =
                "error";
        }

        $stmt->close();
    }
}


require_once 'includes/header.php';

?>


<!-- ======================================
     LOGIN PAGE
====================================== -->

<section class="auth-page">

    <div class="auth-container">


        <!-- LEFT SIDE -->

        <div class="auth-intro">

            <p class="auth-eyebrow">
                WELCOME BACK
            </p>

            <h1>
                Return to Your
                <br>
                Fragrance Journey
            </h1>

            <p class="auth-intro-text">

                Login to access your account, manage your
                shopping cart and view your perfume orders.

            </p>


            <div class="auth-benefit">

                <i class="fa-solid fa-shield-halved"></i>

                <span>
                    Secure access to your customer account
                </span>

            </div>


            <div class="auth-benefit">

                <i class="fa-solid fa-cart-shopping"></i>

                <span>
                    Continue managing your shopping cart
                </span>

            </div>


            <div class="auth-benefit">

                <i class="fa-solid fa-box"></i>

                <span>
                    View your orders and order information
                </span>

            </div>

        </div>



        <!-- LOGIN CARD -->

        <div class="auth-card">


            <div class="auth-card-heading">

                <p>
                    CUSTOMER ACCESS
                </p>

                <h2>
                    Login
                </h2>

                <span>
                    Enter your registered email and password.
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



            <form action="login.php" method="POST" class="auth-form">


                <!-- EMAIL -->

                <div class="form-group">

                    <label for="email">
                        Email Address
                    </label>

                    <div class="input-wrapper">

                        <i class="fa-regular fa-envelope"></i>

                        <input type="email" id="email" name="email" placeholder="Enter your email address" value="<?php
                        echo htmlspecialchars($email);
                        ?>" autocomplete="email" required>

                    </div>

                </div>



                <!-- PASSWORD -->

                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <div class="input-wrapper">

                        <i class="fa-solid fa-lock"></i>

                        <input type="password" id="password" name="password" placeholder="Enter your password"
                            autocomplete="current-password" required>

                    </div>

                </div>



                <!-- LOGIN BUTTON -->

                <button type="submit" class="auth-submit-button">

                    Login

                    <span>→</span>

                </button>


            </form>



            <div class="auth-switch">

                Don't have an account?

                <a href="register.php">
                    Register
                </a>

            </div>


        </div>

    </div>

</section>


<?php
require_once 'includes/footer.php';
?>