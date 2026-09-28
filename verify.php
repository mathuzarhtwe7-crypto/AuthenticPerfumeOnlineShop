<?php

require_once 'config/database.php';


/* ======================================
   GET AUTHENTICITY CODE
====================================== */

$code =
    strtoupper(
        trim(
            $_GET["code"] ?? ""
        )
    );


$verification_status = "";
$product = null;


/* ======================================
   VERIFY CODE
====================================== */

if ($code !== "") {

    $sql =
        "SELECT
            p.product_id,
            p.product_name,
            p.brand,
            p.price,
            p.authenticity_code,
            c.category_name

         FROM products p

         INNER JOIN categories c
            ON p.category_id = c.category_id

         WHERE p.authenticity_code = ?

         LIMIT 1";


    $stmt =
        $conn->prepare($sql);


    $stmt->bind_param(
        "s",
        $code
    );


    $stmt->execute();


    $result =
        $stmt->get_result();


    if ($result->num_rows === 1) {

        $product =
            $result->fetch_assoc();

        $verification_status =
            "verified";

    } else {

        $verification_status =
            "invalid";
    }


    $stmt->close();
}


require_once 'includes/header.php';

?>


<!-- ======================================
     AUTHENTICITY VERIFICATION PAGE
====================================== -->

<section class="verification-page">

    <div class="verification-container">


        <!-- PAGE HEADING -->

        <div class="verification-heading">

            <p class="verification-eyebrow">
                BUY WITH CONFIDENCE
            </p>

            <h1>
                Verify Authenticity
            </h1>

            <p>
                Enter the authenticity code provided with your
                perfume to confirm that the product is genuine.
            </p>

        </div>



        <!-- SEARCH CARD -->

        <div class="verification-card">


            <form action="verify.php" method="GET" class="verification-form">

                <label for="code">
                    Authenticity Code
                </label>


                <div class="verification-input-row">

                    <div class="verification-input">

                        <i class="fa-solid fa-shield-halved"></i>

                        <input type="text" id="code" name="code" placeholder="Example: AUTH-MO-001" value="<?php
                        echo htmlspecialchars(
                            $code
                        );
                        ?>" maxlength="100" required>

                    </div>


                    <button type="submit">

                        Verify

                        <span>→</span>

                    </button>

                </div>

            </form>



            <!-- ==================================
                 VERIFIED RESULT
            =================================== -->

            <?php if (
                $verification_status === "verified"
            ): ?>


                <div class="verification-result verified">


                    <div class="verification-result-icon">

                        <i class="fa-solid fa-check"></i>

                    </div>


                    <div class="verification-result-content">

                        <p class="result-label">
                            AUTHENTIC PRODUCT
                        </p>

                        <h2>
                            Verification Successful
                        </h2>

                        <p class="result-description">
                            This authenticity code matches a
                            registered product in our collection.
                        </p>



                        <div class="verified-product">


                            <div>

                                <span>
                                    Brand
                                </span>

                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $product["brand"]
                                    );
                                    ?>

                                </strong>

                            </div>


                            <div>

                                <span>
                                    Product
                                </span>

                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $product[
                                            "product_name"
                                        ]
                                    );
                                    ?>

                                </strong>

                            </div>


                            <div>

                                <span>
                                    Category
                                </span>

                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $product[
                                            "category_name"
                                        ]
                                    );
                                    ?>

                                </strong>

                            </div>


                            <div>

                                <span>
                                    Authenticity Code
                                </span>

                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $product[
                                            "authenticity_code"
                                        ]
                                    );
                                    ?>

                                </strong>

                            </div>


                        </div>


                        <a href="product.php?id=<?php
                        echo (int) 
                            $product["product_id"];
                        ?>"
                            class="verification-product-link"
                            >
                            View Product
                            <span>→</span>
                        </a>


                    </div>

                </div>



                <!-- ==================================
                 INVALID RESULT
            =================================== -->

            <?php elseif (
                $verification_status === "invalid"
            ): ?>


                <div class="verification-result invalid">


                    <div class="verification-result-icon">

                        <i class="fa-solid fa-xmark"></i>

                    </div>


                    <div class="verification-result-content">

                        <p class="result-label">
                            CODE NOT RECOGNIZED
                        </p>

                        <h2>
                            Verification Unsuccessful
                        </h2>

                        <p class="result-description">

                            We could not find a product matching

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $code
                                );
                                ?>
                            </strong>.

                            Please check the code and try again.

                        </p>

                    </div>

                </div>



                <!-- ==================================
                 DEFAULT STATE
            =================================== -->

            <?php else: ?>


                <div class="verification-default">

                    <i class="fa-solid fa-gem"></i>

                    <h2>
                        Genuine Fragrances, Verified
                    </h2>

                    <p>
                        Each registered perfume has a unique
                        authenticity code that can be checked
                        through this verification service.
                    </p>

                </div>


            <?php endif; ?>


        </div>

    </div>

</section>


<?php
require_once 'includes/footer.php';
?>