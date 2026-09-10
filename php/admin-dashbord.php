<?php

require_once "auth.php";
require_once "db.php";

// Only admin can access this page
if ($_SESSION["role"] != "admin") {
    die("Access denied. Admins only.");
}


// Count total users
$result = $conn->query("SELECT COUNT(*) AS total FROM users");
$total_users = $result->fetch_assoc()["total"];


// Count providers
$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM users
    WHERE role = 'provider'
");
$total_providers = $result->fetch_assoc()["total"];


// Count customers
$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM users
    WHERE role = 'customer'
");
$total_customers = $result->fetch_assoc()["total"];


// Count bookings
$result = $conn->query("SELECT COUNT(*) AS total FROM bookings");
$total_bookings = $result->fetch_assoc()["total"];


// Count pending bookings
$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM bookings
    WHERE status = 'pending'
");
$pending_bookings = $result->fetch_assoc()["total"];


// Count completed bookings
$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM bookings
    WHERE status = 'completed'
");
$completed_bookings = $result->fetch_assoc()["total"];


// Get recent users
$users = $conn->query("
    SELECT id, full_name, email, role, created_at
    FROM users
    ORDER BY created_at DESC
    LIMIT 10
");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard - JobWalk</title>

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
            Admin Dashboard
        </h1>


        <p>
            Welcome,
            <strong>
                <?php echo htmlspecialchars($_SESSION["full_name"]); ?>
            </strong>
        </p>



        <!-- STATISTICS -->

        <div class="dashboard-cards">


            <!-- TOTAL USERS -->

            <div class="card">

                <i class="fas fa-users"></i>

                <h3>
                    Total Users
                </h3>

                <h2>
                    <?php echo $total_users; ?>
                </h2>

            </div>



            <!-- PROVIDERS -->

            <div class="card">

                <i class="fas fa-user-tie"></i>

                <h3>
                    Providers
                </h3>

                <h2>
                    <?php echo $total_providers; ?>
                </h2>

            </div>



            <!-- CUSTOMERS -->

            <div class="card">

                <i class="fas fa-user"></i>

                <h3>
                    Customers
                </h3>

                <h2>
                    <?php echo $total_customers; ?>
                </h2>

            </div>



            <!-- BOOKINGS -->

            <div class="card">

                <i class="fas fa-calendar-check"></i>

                <h3>
                    Total Bookings
                </h3>

                <h2>
                    <?php echo $total_bookings; ?>
                </h2>

            </div>



            <!-- PENDING -->

            <div class="card">

                <i class="fas fa-clock"></i>

                <h3>
                    Pending Bookings
                </h3>

                <h2>
                    <?php echo $pending_bookings; ?>
                </h2>

            </div>



            <!-- COMPLETED -->

            <div class="card">

                <i class="fas fa-check-circle"></i>

                <h3>
                    Completed Jobs
                </h3>

                <h2>
                    <?php echo $completed_bookings; ?>
                </h2>

            </div>


        </div>



        <!-- QUICK ACTIONS -->

        <div class="profile-card">

            <h2>
                Quick Actions
            </h2>


            <a href="admin-users.php" class="btn">
                <i class="fas fa-users"></i>
                Manage Users
            </a>


            <a href="admin-providers.php" class="btn">
                <i class="fas fa-user-tie"></i>
                Manage Providers
            </a>


            <a href="admin-bookings.php" class="btn">
                <i class="fas fa-calendar"></i>
                View Bookings
            </a>

        </div>



        <!-- RECENT USERS -->

        <div class="profile-card">

            <h2>
                Recent Users
            </h2>


            <?php if ($users->num_rows > 0): ?>

                <table>

                    <tr>

                        <th>
                            Name
                        </th>

                        <th>
                            Email
                        </th>

                        <th>
                            Role
                        </th>

                        <th>
                            Date Joined
                        </th>

                    </tr>


                    <?php while ($user = $users->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $user["full_name"]
                                );
                                ?>
                            </td>


                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $user["email"]
                                );
                                ?>
                            </td>


                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $user["role"]
                                );
                                ?>
                            </td>


                            <td>
                                <?php
                                echo date(
                                    "M j, Y",
                                    strtotime(
                                        $user["created_at"]
                                    )
                                );
                                ?>
                            </td>

                        </tr>

                    <?php endwhile; ?>

                </table>

            <?php else: ?>

                <p>
                    No users found.
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