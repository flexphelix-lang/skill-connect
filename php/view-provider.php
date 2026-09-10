<?php

require_once "auth.php";
require_once "db.php";

// Only customers can view provider profiles
if ($_SESSION["role"] != "customer") {
    die("Access denied.");
}


// Check provider ID
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid provider.");
}

$provider_id = intval($_GET["id"]);


// Get provider information
$stmt = $conn->prepare("
    SELECT
        users.id,
        users.full_name,
        users.email,
        users.phone,
        provider_profiles.skill,
        provider_profiles.description,
        provider_profiles.location
    FROM users

    INNER JOIN provider_profiles
        ON users.id = provider_profiles.user_id

    WHERE users.id = ?
      AND users.role = 'provider'
");

$stmt->bind_param("i", $provider_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Provider not found.");
}

$provider = $result->fetch_assoc();

$stmt->close();


// Get provider services
$stmt = $conn->prepare("
    SELECT
        id,
        service_name,
        description,
        price
    FROM services
    WHERE provider_id = ?
    ORDER BY service_name ASC
");

$stmt->bind_param("i", $provider_id);
$stmt->execute();

$services = $stmt->get_result();

$stmt->close();


// Get provider rating
$stmt = $conn->prepare("
    SELECT
        AVG(rating) AS average_rating,
        COUNT(*) AS review_count
    FROM reviews
    WHERE provider_id = ?
");

$stmt->bind_param("i", $provider_id);
$stmt->execute();

$rating_result = $stmt->get_result();
$rating = $rating_result->fetch_assoc();

$stmt->close();

$average_rating = $rating["average_rating"];
$review_count = $rating["review_count"];


// Get reviews
$stmt = $conn->prepare("
    SELECT
        reviews.rating,
        reviews.comment,
        reviews.created_at,
        users.full_name AS customer_name

    FROM reviews

    INNER JOIN users
        ON reviews.customer_id = users.id

    WHERE reviews.provider_id = ?

    ORDER BY reviews.created_at DESC
");

$stmt->bind_param("i", $provider_id);
$stmt->execute();

$reviews = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php echo htmlspecialchars($provider["full_name"]); ?>
        - SkillConnect
    </title>

    <link
        rel="stylesheet"
        href="../css/dashboard.css"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    >

</head>


<body>

<div class="dashboard-container">


    <!-- SIDEBAR -->

    <aside class="sidebar">

        <h2>SkillConnect</h2>

        <p>Customer Dashboard</p>

        <nav>

            <a href="customer-dashboard.php">
                <i class="fas fa-home"></i>
                Dashboard
            </a>

            <a href="search-results.php">
                <i class="fas fa-search"></i>
                Search
            </a>

            <a href="my-bookings.php">
                <i class="fas fa-calendar"></i>
                My Bookings
            </a>

            <a href="messages.php">
                <i class="fas fa-comments"></i>
                Messages
            </a>

            <a href="settings.php">
                <i class="fas fa-cog"></i>
                Settings
            </a>

            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>

        </nav>

    </aside>



    <!-- MAIN CONTENT -->

    <main class="main-content">


        <a
            href="search-results.php"
            class="btn"
        >

            <i class="fas fa-arrow-left"></i>

            Back to Search

        </a>



        <!-- PROVIDER PROFILE -->

        <div class="profile-card">

            <h1>
                <?php
                echo htmlspecialchars(
                    $provider["full_name"]
                );
                ?>
            </h1>


            <h3>

                <i class="fas fa-briefcase"></i>

                <?php
                echo htmlspecialchars(
                    $provider["skill"]
                );
                ?>

            </h3>


            <p>

                <strong>
                    Location:
                </strong>

                <?php
                echo htmlspecialchars(
                    $provider["location"]
                );
                ?>

            </p>


            <p>

                <strong>
                    About:
                </strong>

            </p>


            <p>

                <?php
                echo nl2br(
                    htmlspecialchars(
                        $provider["description"]
                    )
                );
                ?>

            </p>


            <!-- RATING -->

            <div class="rating-section">

                <h3>
                    Rating
                </h3>


                <?php if ($average_rating !== null): ?>

                    <p>

                        <strong>

                            <?php
                            echo number_format(
                                $average_rating,
                                1
                            );
                            ?>

                            / 5

                        </strong>

                        ⭐⭐⭐⭐⭐

                        (<?php echo $review_count; ?> reviews)

                    </p>

                <?php else: ?>

                    <p>
                        No reviews yet.
                    </p>

                <?php endif; ?>

            </div>


            <!-- ACTION BUTTONS -->

            <div class="profile-actions">

                <a
                    href="hirebooking.php?provider_id=<?php echo $provider_id; ?>"
                    class="btn"
                >

                    <i class="fas fa-calendar-check"></i>

                    Hire Provider

                </a>


                <a
                    href="messages.php?user_id=<?php echo $provider_id; ?>"
                    class="btn"
                >

                    <i class="fas fa-comment"></i>

                    Message Provider

                </a>

            </div>

        </div>



        <!-- SERVICES -->

        <div class="profile-card">

            <h2>

                <i class="fas fa-briefcase"></i>

                Services Offered

            </h2>


            <?php if ($services->num_rows > 0): ?>


                <?php while ($service = $services->fetch_assoc()): ?>

                    <div class="review-card">


                        <h3>

                            <?php
                            echo htmlspecialchars(
                                $service["service_name"]
                            );
                            ?>

                        </h3>


                        <p>

                            <?php

                            if (!empty($service["description"])) {

                                echo nl2br(
                                    htmlspecialchars(
                                        $service["description"]
                                    )
                                );

                            } else {

                                echo "No description provided.";

                            }

                            ?>

                        </p>


                        <h3>

                            ₦<?php

                            echo number_format(
                                $service["price"],
                                2
                            );

                            ?>

                        </h3>


                        <a
    href="hirebooking.php?provider_id=<?php echo $provider_id; ?>&service_id=<?php echo $service['id']; ?>"
    class="btn"
>
    <i class="fas fa-calendar-check"></i>
    Book This Service
</a>


                    </div>

                <?php endwhile; ?>


            <?php else: ?>


                <p>
                    This provider has not added any services yet.
                </p>


            <?php endif; ?>

        </div>



        <!-- REVIEWS -->

        <div class="profile-card">

            <h2>

                <i class="fas fa-star"></i>

                Customer Reviews

            </h2>


            <?php if ($reviews->num_rows > 0): ?>


                <?php while ($review = $reviews->fetch_assoc()): ?>


                    <div class="review-card">

                        <h3>

                            <?php
                            echo htmlspecialchars(
                                $review["customer_name"]
                            );
                            ?>

                        </h3>


                        <p>

                            <?php

                            for (
                                $i = 1;
                                $i <= 5;
                                $i++
                            ) {

                                if (
                                    $i <= $review["rating"]
                                ) {

                                    echo "⭐";

                                } else {

                                    echo "☆";

                                }

                            }

                            ?>

                        </p>


                        <?php if (!empty($review["comment"])): ?>

                            <p>

                                <?php
                                echo nl2br(
                                    htmlspecialchars(
                                        $review["comment"]
                                    )
                                );
                                ?>

                            </p>

                        <?php else: ?>

                            <p>
                                No comment provided.
                            </p>

                        <?php endif; ?>


                        <small>

                            Reviewed on

                            <?php
                            echo date(
                                "F j, Y",
                                strtotime(
                                    $review["created_at"]
                                )
                            );
                            ?>

                        </small>

                    </div>


                <?php endwhile; ?>


            <?php else: ?>


                <p>
                    No reviews yet.
                </p>


            <?php endif; ?>

        </div>


    </main>

</div>

</body>

</html>

<?php

$stmt->close();
$conn->close();

?>