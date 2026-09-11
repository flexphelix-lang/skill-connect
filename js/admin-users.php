<?php

require_once "auth.php";
require_once "db.php";

// Only admins can access this page
if ($_SESSION["role"] != "admin") {
    die("Access denied. Admins only.");
}


// Delete user
if (isset($_GET["delete"]) && is_numeric($_GET["delete"])) {

    $user_id = intval($_GET["delete"]);

    // Don't allow admin to delete their own account
    if ($user_id != $_SESSION["user_id"]) {

        $stmt = $conn->prepare("
            DELETE FROM users
            WHERE id = ?
        ");

        $stmt->bind_param("i", $user_id);

        $stmt->execute();

        $stmt->close();
    }

    header("Location: admin-users.php");
    exit();
}


// Get all users
$users = $conn->query("
    SELECT
        id,
        full_name,
        email,
        phone,
        role,
        created_at
    FROM users
    ORDER BY created_at DESC
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

    <title>Manage Users - JobWalk</title>

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
            Manage Users
        </h1>

        <p>
            View and manage JobWalk users.
        </p>


        <div class="profile-card">

            <h2>
                Registered Users
            </h2>


            <?php if ($users->num_rows > 0): ?>

                <div style="overflow-x:auto;">

                    <table>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Name
                            </th>

                            <th>
                                Email
                            </th>

                            <th>
                                Phone
                            </th>

                            <th>
                                Role
                            </th>

                            <th>
                                Date Joined
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>


                        <?php while ($user = $users->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <?php
                                    echo $user["id"];
                                    ?>
                                </td>


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
                                        $user["phone"]
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


                                <td>

                                    <?php
                                    if (
                                        $user["id"]
                                        !=
                                        $_SESSION["user_id"]
                                    ):
                                    ?>

                                        <a
                                            href="admin-users.php?delete=<?php echo $user["id"]; ?>"
                                            class="btn"
                                            onclick="return confirm('Are you sure you want to delete this user?');"
                                        >

                                            <i class="fas fa-trash"></i>

                                            Delete

                                        </a>

                                    <?php else: ?>

                                        <strong>
                                            Current Admin
                                        </strong>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    </table>

                </div>

            <?php else: ?>

                <p>
                    No users have registered on JobWalk yet.
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