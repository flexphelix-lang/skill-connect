<?php

require_once "auth.php";
require_once "db.php";

// Only customers can access this page
if ($_SESSION["role"] !== "customer") {
    header("Location: provider-dashboard.php");
    exit();
}

$customer_id = $_SESSION["user_id"];

// Get customer's bookings
$stmt = $conn->prepare(
    "SELECT
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
        provider_profiles.price

    FROM bookings

    INNER JOIN users
        ON bookings.provider_id = users.id

    LEFT JOIN provider_profiles
        ON bookings.provider_id = provider_profiles.user_id

    WHERE bookings.customer_id = ?

    ORDER BY bookings.created_at DESC"
);

$stmt->bind_param("i", $customer_id);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Bookings | JobWalk</title>

    <link
        rel="stylesheet"
        href="../css/dashboard.css"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
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

                    <i class="fas fa-home"></i>

                    Dashboard

                </a>

            </li>


            <li>

                <a href="search-results.php">

                    <i class="fas fa-search"></i>

                    Find Providers

                </a>

            </li>


            <li class="active">

                <a href="my-bookings.php">

                    <i class="fas fa-calendar-check"></i>

                    My Bookings

                </a>

            </li>


            <li>

                <a href="messages.php">

                    <i class="fas fa-comments"></i>

                    Messages

                </a>

            </li>


            <li>

                <a href="settings.php">

                    <i class="fas fa-cog"></i>

                    Settings

                </a>

            </li>


            <li>

                <a href="logout.php">

                    <i class="fas fa-sign-out-alt"></i>

                    Logout

                </a>

            </li>

        </ul>

    </aside>



    <!-- MAIN CONTENT -->

    <main class="content">


        <header class="topbar">

            <div>

                <h1>My Bookings</h1>

                <p>
                    View and track your service requests.
                </p>

            </div>

        </header>



        <!-- BOOKINGS -->

        <?php if ($result->num_rows > 0): ?>


            <?php while ($booking = $result->fetch_assoc()): ?>


                <section class="card">


                    <h2>

                        <?php
                        echo htmlspecialchars(
                            $booking["provider_name"]
                        );
                        ?>

                    </h2>


                    <p>

                        <strong>Service:</strong>

                        <?php
                        echo htmlspecialchars(
                            $booking["skill"]
                        );
                        ?>

                    </p>


                    <p>

                        <strong>Location:</strong>

                        <?php
                        echo htmlspecialchars(
                            $booking["location"]
                        );
                        ?>

                    </p>


                    <p>

                        <strong>Price:</strong>

                        ₦<?php
                        echo number_format(
                            $booking["price"],
                            2
                        );
                        ?>

                    </p>


                    <p>

                        <strong>Booking Date:</strong>

                        <?php
                        echo htmlspecialchars(
                            $booking["booking_date"]
                        );
                        ?>

                    </p>


                    <p>

                        <strong>Booking Time:</strong>

                        <?php
                        echo htmlspecialchars(
                            $booking["booking_time"]
                        );
                        ?>

                    </p>


                    <p>

                        <strong>Your Message:</strong>

                    </p>


                    <p>

                        <?php
                        echo nl2br(
                            htmlspecialchars(
                                $booking["message"]
                            )
                        );
                        ?>

                    </p>


                    <p>

                        <strong>Status:</strong>

                        <?php
                        echo htmlspecialchars(
                            ucfirst($booking["status"])
                        );
                        ?>

                    </p>



                    <!-- PENDING -->

                    <?php if ($booking["status"] === "pending"): ?>

                        <p>
                            ⏳ Waiting for the provider to respond.
                        </p>


                    <!-- ACCEPTED -->

                    <?php elseif ($booking["status"] === "accepted"): ?>

                        <p>
                            ✅ Your booking has been accepted!
                        </p>


                    <!-- CANCELLED -->

                    <?php elseif ($booking["status"] === "cancelled"): ?>

                        <p>
                            ❌ This booking was cancelled.
                        </p>


                    <!-- COMPLETED -->

                    <?php elseif ($booking["status"] === "completed"): ?>

                        <p>
                            🎉 This job has been completed.
                        </p>

                        <br>

                        <a
                            href="review.php?provider_id=<?php echo $booking["provider_id"]; ?>"
                            class="btn"
                        >

                            ⭐ Leave a Review

                        </a>

                    <?php endif; ?>


                </section>


            <?php endwhile; ?>


        <?php else: ?>


            <!-- NO BOOKINGS -->

            <section class="card">

                <h2>No Bookings Yet</h2>

                <p>
                    You haven't booked a provider yet.
                </p>

                <br>

                <a
                    href="search-results.php"
                    class="btn"
                >

                    <i class="fas fa-search"></i>

                    Find a Provider

                </a>

            </section>


        <?php endif; ?>


    </main>

</div>


<script src="../js/main.js"></script>

</body>

</html>