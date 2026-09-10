<?php
require_once "auth.php";
require_once "db.php";

// Get provider ID
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
        provider_profiles.location,
        provider_profiles.price,
        provider_profiles.profile_image
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


// Get average rating and review count
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
$rating_data = $rating_result->fetch_assoc();

$average_rating = $rating_data["average_rating"];
$review_count = $rating_data["review_count"];

$stmt->close();


// Get individual reviews
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

$reviews_result = $stmt->get_result();


// Set rating display
if ($average_rating !== null) {
    $average_rating_display = number_format($average_rating, 1);
} else {
    $average_rating_display = "No rating";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo htmlspecialchars($provider["full_name"]); ?> - JobWalk
    </title>

    <link rel="stylesheet" href="../css/dashboard.css">

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    >

</head>

<body>

<div class="dashboard-container">

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <h2>JobWalk</h2>

        <p>Find the right person for the job.</p>

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

        <a href="search-results.php" class="btn">
            <i class="fas fa-arrow-left"></i>
            Back to Search
        </a>


        <!-- PROVIDER PROFILE -->

        <div class="profile-card">

            <div class="profile-header">

                <div>

                    <h1>
                        <?php echo htmlspecialchars($provider["full_name"]); ?>
                    </h1>

                    <h3>
                        <?php echo htmlspecialchars($provider["skill"]); ?>
                    </h3>

                </div>

            </div>


            <!-- RATING -->

            <div class="rating-section">

                <h3>
                    <i class="fas fa-star"></i>
                    Rating
                </h3>

                <?php if ($average_rating !== null): ?>

                    <p>
                        <strong>
                            <?php echo $average_rating_display; ?>/5
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


            <!-- PROVIDER INFORMATION -->

            <div class="profile-info">

                <p>
                    <strong>Skill:</strong>

                    <?php echo htmlspecialchars($provider["skill"]); ?>
                </p>


                <p>
                    <strong>Location:</strong>

                    <?php echo htmlspecialchars($provider["location"]); ?>
                </p>


                <p>
                    <strong>Price:</strong>

                    ₦<?php echo number_format($provider["price"], 2); ?>
                </p>


                <p>
                    <strong>Description:</strong>
                </p>

                <p>
                    <?php
                    echo nl2br(
                        htmlspecialchars($provider["description"])
                    );
                    ?>
                </p>

            </div>


            <!-- ACTION BUTTONS -->

            <div class="profile-actions">

                <a
                    href="hirebooking.php?provider_id=<?php echo $provider["id"]; ?>"
                    class="btn"
                >
                    <i class="fas fa-calendar-check"></i>
                    Hire This Provider
                </a>


                <a
                    href="messages.php?user_id=<?php echo $provider["id"]; ?>"
                    class="btn"
                >
                    <i class="fas fa-comment"></i>
                    Message Provider
                </a>

            </div>

        </div>


        <!-- REVIEWS -->

        <div class="reviews-section">

            <h2>
                <i class="fas fa-star"></i>
                Customer Reviews
            </h2>


            <?php if ($reviews_result->num_rows > 0): ?>

                <?php while ($review = $reviews_result->fetch_assoc()): ?>

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

                            for ($i = 1; $i <= 5; $i++) {

                                if ($i <= $review["rating"]) {

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
                                strtotime($review["created_at"])
                            );
                            ?>

                        </small>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <div class="review-card">

                    <p>
                        This provider has no reviews yet.
                    </p>

                </div>

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