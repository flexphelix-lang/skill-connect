<?php

require_once "auth.php";
require_once "db.php";

// Only customers can access this page
if ($_SESSION["role"] != "customer") {
    die("Access denied. Customers only.");
}

$customer_id = $_SESSION["user_id"];


// Get customer's bookings
$stmt = $conn->prepare("
    SELECT
        bookings.id,
        bookings.provider_id,
        bookings.booking_date,
        bookings.booking_time,
        bookings.message,
        bookings.status,
        bookings.created_at,

        users.full_name AS provider_name,

        provider_profiles.skill,
        provider_profiles.location,

        services.service_name,
        services.description AS service_description,
        services.price AS service_price

    FROM bookings

    INNER JOIN users
        ON bookings.provider_id = users.id

    LEFT JOIN provider_profiles
        ON bookings.provider_id = provider_profiles.user_id

    LEFT JOIN services
        ON bookings.service_id = services.id

    WHERE bookings.customer_id = ?

    ORDER BY bookings.created_at DESC
");

$stmt->bind_param("i", $customer_id);
$stmt->execute();

$bookings = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Bookings - SkillConnect</title>

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

        <h1>
            My Bookings
        </h1>

        <p>
            View and track your SkillConnect bookings.
        </p>


        <?php if ($bookings->num_rows > 0): ?>


            <?php while ($booking = $bookings->fetch_assoc()): ?>


                <div class="profile-card">


                    <!-- PROVIDER -->

                    <h2>

                        <?php
                        echo htmlspecialchars(
                            $booking["provider_name"]
                        );
                        ?>

                    </h2>


                    <!-- SERVICE -->

                    <p>

                        <strong>
                            Service:
                        </strong>

                        <?php

                        if (!empty($booking["service_name"])) {

                            echo htmlspecialchars(
                                $booking["service_name"]
                            );

                        } else {

                            echo "Service not specified";

                        }

                        ?>

                    </p>


                    <!-- SERVICE PRICE -->

                    <p>

                        <strong>
                            Price:
                        </strong>

                        <?php

                        if ($booking["service_price"] !== null) {

                            echo "₦" . number_format(
                                $booking["service_price"],
                                2
                            );

                        } else {

                            echo "Price not available";

                        }

                        ?>

                    </p>


                    <!-- SKILL -->

                    <p>

                        <strong>
                            Provider Skill:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $booking["skill"] ?? "N/A"
                        );
                        ?>

                    </p>


                    <!-- LOCATION -->

                    <p>

                        <strong>
                            Location:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $booking["location"] ?? "N/A"
                        );
                        ?>

                    </p>


                    <!-- DATE -->

                    <p>

                        <strong>
                            Booking Date:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $booking["booking_date"]
                        );
                        ?>

                    </p>


                    <!-- TIME -->

                    <p>

                        <strong>
                            Booking Time:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $booking["booking_time"] ?? "N/A"
                        );
                        ?>

                    </p>


                    <!-- MESSAGE -->

                    <p>

                        <strong>
                            Your Message:
                        </strong>

                    </p>


                    <?php if (!empty($booking["message"])): ?>

                        <p>

                            <?php
                            echo nl2br(
                                htmlspecialchars(
                                    $booking["message"]
                                )
                            );
                            ?>

                        </p>

                    <?php else: ?>

                        <p>
                            No message provided.
                        </p>

                    <?php endif; ?>


                    <!-- STATUS -->

                    <p>

                        <strong>
                            Status:
                        </strong>


                        <?php

                        if ($booking["status"] == "pending") {

                            echo "⏳ Pending";

                        } elseif ($booking["status"] == "accepted") {

                            echo "✅ Accepted";

                        } elseif ($booking["status"] == "completed") {

                            echo "✔️ Completed";

                        } elseif ($booking["status"] == "cancelled") {

                            echo "❌ Cancelled";

                        } else {

                            echo htmlspecialchars(
                                $booking["status"]
                            );

                        }

                        ?>

                    </p>


                    <!-- ACTIONS -->

                    <div class="profile-actions">


                        <a
                            href="view-provider.php?id=<?php echo $booking["provider_id"]; ?>"
                            class="btn"
                        >

                            <i class="fas fa-user"></i>

                            View Provider

                        </a>



                        <a
                            href="messages.php?user_id=<?php echo $booking["provider_id"]; ?>"
                            class="btn"
                        >

                            <i class="fas fa-comment"></i>

                            Message Provider

                        </a>



                        <?php if ($booking["status"] == "completed"): ?>

                            <a
                                href="review.php?provider_id=<?php echo $booking["provider_id"]; ?>"
                                class="btn"
                            >

                                ⭐ Leave a Review

                            </a>

                        <?php endif; ?>


                    </div>


                </div>


            <?php endwhile; ?>


        <?php else: ?>


            <div class="profile-card">

                <h2>
                    No Bookings Yet
                </h2>


                <p>
                    You have not booked a service on SkillConnect yet.
                </p>


                <a
                    href="search-results.php"
                    class="btn"
                >

                    <i class="fas fa-search"></i>

                    Find a Provider

                </a>

            </div>


        <?php endif; ?>


    </main>

</div>

</body>

</html>


<?php

$stmt->close();
$conn->close();

?>