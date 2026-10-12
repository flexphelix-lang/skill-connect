<?php

require_once "auth.php";
require_once "db.php";

// Only admins can access this page
if ($_SESSION["role"] != "admin") {
    die("Access denied. Admins only.");
}


// Get all bookings
$bookings = $conn->query("
    SELECT
        bookings.id,
        bookings.booking_date,
        bookings.booking_time,
        bookings.message,
        bookings.status,
        bookings.created_at,

        customer.full_name AS customer_name,

        provider.full_name AS provider_name,

        provider_profiles.skill

    FROM bookings

    INNER JOIN users AS customer
        ON bookings.customer_id = customer.id

    INNER JOIN users AS provider
        ON bookings.provider_id = provider.id

    LEFT JOIN provider_profiles
        ON bookings.provider_id = provider_profiles.user_id

    ORDER BY bookings.created_at DESC
");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Manage Bookings - SkillConnect</title>

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

        <p>Admin Panel</p>


        <nav>

            <a href="admin-dashboard.php">
                <i class="fas fa-home"></i>
                Dashboard
            </a>


            <a href="admin-users.php">
                <i class="fas fa-users"></i>
                Users
            </a>


            <a href="admin-providers.php">
                <i class="fas fa-user-tie"></i>
                Providers
            </a>


            <a href="admin-bookings.php">
                <i class="fas fa-calendar"></i>
                Bookings
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
            Manage Bookings
        </h1>

        <p>
            Monitor all bookings made on SkillConnect.
        </p>


        <div class="profile-card">

            <h2>
                All Bookings
            </h2>


            <?php if ($bookings->num_rows > 0): ?>

                <div style="overflow-x:auto;">

                    <table>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Customer
                            </th>

                            <th>
                                Provider
                            </th>

                            <th>
                                Skill
                            </th>

                            <th>
                                Date
                            </th>

                            <th>
                                Time
                            </th>

                            <th>
                                Message
                            </th>

                            <th>
                                Status
                            </th>

                        </tr>


                        <?php while ($booking = $bookings->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <?php
                                    echo $booking["id"];
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $booking["customer_name"]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $booking["provider_name"]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $booking["skill"] ?? "N/A"
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $booking["booking_date"]
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $booking["booking_time"] ?? "N/A"
                                    );
                                    ?>
                                </td>


                                <td>

                                    <?php

                                    if (!empty($booking["message"])) {

                                        echo htmlspecialchars(
                                            $booking["message"]
                                        );

                                    } else {

                                        echo "No message";

                                    }

                                    ?>

                                </td>


                                <td>

                                    <?php

                                    $status = $booking["status"];

                                    if ($status == "pending") {

                                        echo "⏳ Pending";

                                    } elseif ($status == "accepted") {

                                        echo "✅ Accepted";

                                    } elseif ($status == "completed") {

                                        echo "✔️ Completed";

                                    } elseif ($status == "cancelled") {

                                        echo "❌ Cancelled";

                                    } else {

                                        echo htmlspecialchars($status);

                                    }

                                    ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    </table>

                </div>

            <?php else: ?>

                <p>
                    No bookings have been made on SkillConnect yet.
                </p>

            <?php endif; ?>

        </div>


    </main>

</div>

</body>

</html>


<?php

$conn->close();

?>