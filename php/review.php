<?php

require_once "auth.php";
require_once "db.php";

// Only customers can leave reviews
if ($_SESSION["role"] !== "customer") {
    header("Location: customer-dashboard.php");
    exit();
}

$customer_id = $_SESSION["user_id"];
$message = "";

// Check provider ID
if (!isset($_GET["provider_id"]) || !is_numeric($_GET["provider_id"])) {
    die("Provider not found.");
}

$provider_id = intval($_GET["provider_id"]);


// CHECK IF CUSTOMER COMPLETED A JOB WITH THIS PROVIDER
$stmt = $conn->prepare(
    "SELECT id
     FROM bookings
     WHERE customer_id = ?
     AND provider_id = ?
     AND status = 'completed'"
);

$stmt->bind_param(
    "ii",
    $customer_id,
    $provider_id
);

$stmt->execute();

$booking_result = $stmt->get_result();

if ($booking_result->num_rows === 0) {

    die("You can only review a provider after completing a job with them.");

}

$stmt->close();


// GET PROVIDER INFORMATION
$stmt = $conn->prepare(
    "SELECT
        users.id,
        users.full_name,
        provider_profiles.skill

     FROM users

     INNER JOIN provider_profiles
        ON users.id = provider_profiles.user_id

     WHERE users.id = ?
     AND users.role = 'provider'"
);

$stmt->bind_param(
    "i",
    $provider_id
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {

    die("Provider not found.");

}

$provider = $result->fetch_assoc();

$stmt->close();


// SUBMIT REVIEW
if (isset($_POST["submit_review"])) {

    $rating = intval($_POST["rating"]);
    $comment = trim($_POST["comment"]);


    // CHECK RATING
    if ($rating < 1 || $rating > 5) {

        $message = "Rating must be between 1 and 5.";

    } else {


        // CHECK IF CUSTOMER ALREADY REVIEWED PROVIDER
        $check = $conn->prepare(
            "SELECT id
             FROM reviews
             WHERE customer_id = ?
             AND provider_id = ?"
        );

        $check->bind_param(
            "ii",
            $customer_id,
            $provider_id
        );

        $check->execute();

        $check_result = $check->get_result();


        if ($check_result->num_rows > 0) {

            $message = "You have already reviewed this provider.";

        } else {


            // INSERT REVIEW
            $stmt = $conn->prepare(
                "INSERT INTO reviews
                (customer_id, provider_id, rating, comment)
                VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "iiis",
                $customer_id,
                $provider_id,
                $rating,
                $comment
            );


            if ($stmt->execute()) {

                $message = "Review submitted successfully.";

            } else {

                $message = "Unable to submit review.";

            }

            $stmt->close();

        }

        $check->close();

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Review Provider | JobWalk</title>

    <link
        rel="stylesheet"
        href="../css/dashboard.css"
    >

</head>


<body>


<div class="dashboard">


    <!-- SIDEBAR -->

    <aside class="sidebar">

        <div class="logo">

            <h2>JobWalk</h2>

        </div>


        <ul>

            <li>

                <a href="customer-dashboard.php">
                    Dashboard
                </a>

            </li>


            <li>

                <a href="search-results.php">
                    Find Providers
                </a>

            </li>


            <li>

                <a href="my-bookings.php">
                    My Bookings
                </a>

            </li>


            <li>

                <a href="messages.php">
                    Messages
                </a>

            </li>


            <li>

                <a href="settings.php">
                    Settings
                </a>

            </li>


            <li>

                <a href="logout.php">
                    Logout
                </a>

            </li>

        </ul>

    </aside>



    <!-- MAIN CONTENT -->

    <main class="content">


        <header class="topbar">

            <h1>Review Provider</h1>

            <p>
                Share your experience with this provider.
            </p>

        </header>



        <!-- PROVIDER -->

        <section class="card">

            <h2>

                <?php
                echo htmlspecialchars(
                    $provider["full_name"]
                );
                ?>

            </h2>


            <p>

                <strong>Service:</strong>

                <?php
                echo htmlspecialchars(
                    $provider["skill"]
                );
                ?>

            </p>

        </section>



        <!-- MESSAGE -->

        <?php if ($message != ""): ?>

            <section class="card">

                <p>

                    <?php
                    echo htmlspecialchars($message);
                    ?>

                </p>

            </section>

        <?php endif; ?>



        <!-- REVIEW FORM -->

        <section class="card">

            <h2>Leave a Review</h2>

            <br>


            <form method="POST">


                <label>
                    Rating
                </label>

                <br>


                <select
                    name="rating"
                    required
                >

                    <option value="">
                        Select Rating
                    </option>

                    <option value="5">
                        ⭐⭐⭐⭐⭐ 5 - Excellent
                    </option>

                    <option value="4">
                        ⭐⭐⭐⭐ 4 - Very Good
                    </option>

                    <option value="3">
                        ⭐⭐⭐ 3 - Good
                    </option>

                    <option value="2">
                        ⭐⭐ 2 - Poor
                    </option>

                    <option value="1">
                        ⭐ 1 - Very Poor
                    </option>

                </select>


                <br><br>


                <label>
                    Comment
                </label>

                <br>


                <textarea
                    name="comment"
                    rows="5"
                    placeholder="Write your review..."
                    required
                ></textarea>


                <br><br>


                <button
                    type="submit"
                    name="submit_review"
                    class="btn"
                >

                    ⭐ Submit Review

                </button>


            </form>

        </section>


        <br>


        <a href="my-bookings.php">

            ← Back to My Bookings

        </a>


    </main>

</div>


</body>

</html>