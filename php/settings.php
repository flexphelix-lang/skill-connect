<?php

require_once "auth.php";
require_once "db.php";

$user_id = $_SESSION["user_id"];

$message = "";


// GET CURRENT USER INFORMATION
$stmt = $conn->prepare(
    "SELECT full_name, email, phone
     FROM users
     WHERE id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();


// UPDATE PROFILE
if (isset($_POST["update_profile"])) {

    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);

    // Check if another account already uses this email
    $check = $conn->prepare(
        "SELECT id
         FROM users
         WHERE email = ?
         AND id != ?"
    );

    $check->bind_param(
        "si",
        $email,
        $user_id
    );

    $check->execute();

    $check_result = $check->get_result();

    if ($check_result->num_rows > 0) {

        $message = "That email is already being used.";

    } else {

        $stmt = $conn->prepare(
            "UPDATE users
             SET full_name = ?,
                 email = ?,
                 phone = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "sssi",
            $full_name,
            $email,
            $phone,
            $user_id
        );

        if ($stmt->execute()) {

            $_SESSION["full_name"] = $full_name;
            $_SESSION["email"] = $email;

            $message = "Profile updated successfully.";

            // Update displayed information
            $user["full_name"] = $full_name;
            $user["email"] = $email;
            $user["phone"] = $phone;

        } else {

            $message = "Unable to update profile.";

        }

        $stmt->close();
    }

    $check->close();
}


// CHANGE PASSWORD
if (isset($_POST["change_password"])) {

    $current_password = $_POST["current_password"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];


    // Get current password
    $stmt = $conn->prepare(
        "SELECT password
         FROM users
         WHERE id = ?"
    );

    $stmt->bind_param(
        "i",
        $user_id
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $account = $result->fetch_assoc();

    $stmt->close();


    // Check current password
    if (!password_verify(
        $current_password,
        $account["password"]
    )) {

        $message = "Current password is incorrect.";

    } elseif ($new_password !== $confirm_password) {

        $message = "New passwords do not match.";

    } elseif (strlen($new_password) < 6) {

        $message = "New password must be at least 6 characters.";

    } else {

        $hashed_password = password_hash(
            $new_password,
            PASSWORD_DEFAULT
        );

        $stmt = $conn->prepare(
            "UPDATE users
             SET password = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "si",
            $hashed_password,
            $user_id
        );

        if ($stmt->execute()) {

            $message = "Password changed successfully.";

        } else {

            $message = "Unable to change password.";

        }

        $stmt->close();
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

    <title>Settings | JobWalk</title>

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

            <?php if ($_SESSION["role"] === "customer"): ?>

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


                <li>

                    <a href="my-bookings.php">

                        <i class="fas fa-calendar-check"></i>

                        My Bookings

                    </a>

                </li>

            <?php else: ?>

                <li>

                    <a href="provider-dashboard.php">

                        <i class="fas fa-home"></i>

                        Dashboard

                    </a>

                </li>


                <li>

                    <a href="provider-bookings.php">

                        <i class="fas fa-calendar-check"></i>

                        Bookings

                    </a>

                </li>


                <li>

                    <a href="provider-profile.php">

                        <i class="fas fa-user"></i>

                        My Profile

                    </a>

                </li>

            <?php endif; ?>


            <li>

                <a href="messages.php">

                    <i class="fas fa-comments"></i>

                    Messages

                </a>

            </li>


            <li class="active">

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

                <h1>Settings</h1>

                <p>
                    Manage your JobWalk account.
                </p>

            </div>

        </header>



        <!-- MESSAGE -->

        <?php if ($message != ""): ?>

            <section class="card">

                <p>
                    <?php echo htmlspecialchars($message); ?>
                </p>

            </section>

        <?php endif; ?>



        <!-- PROFILE SETTINGS -->

        <section class="card">

            <h2>Profile Information</h2>

            <br>


            <form method="POST">


                <label>
                    Full Name
                </label>

                <br>

                <input
                    type="text"
                    name="full_name"
                    value="<?php
                    echo htmlspecialchars(
                        $user["full_name"]
                    );
                    ?>"
                    required
                >

                <br><br>


                <label>
                    Email Address
                </label>

                <br>

                <input
                    type="email"
                    name="email"
                    value="<?php
                    echo htmlspecialchars(
                        $user["email"]
                    );
                    ?>"
                    required
                >

                <br><br>


                <label>
                    Phone Number
                </label>

                <br>

                <input
                    type="text"
                    name="phone"
                    value="<?php
                    echo htmlspecialchars(
                        $user["phone"] ?? ""
                    );
                    ?>"
                >

                <br><br>


                <button
                    type="submit"
                    name="update_profile"
                    class="btn"
                >

                    <i class="fas fa-save"></i>

                    Save Changes

                </button>


            </form>

        </section>



        <!-- PASSWORD SETTINGS -->

        <section class="card">

            <h2>Change Password</h2>

            <br>


            <form method="POST">


                <label>
                    Current Password
                </label>

                <br>

                <input
                    type="password"
                    name="current_password"
                    required
                >

                <br><br>


                <label>
                    New Password
                </label>

                <br>

                <input
                    type="password"
                    name="new_password"
                    required
                >

                <br><br>


                <label>
                    Confirm New Password
                </label>

                <br>

                <input
                    type="password"
                    name="confirm_password"
                    required
                >

                <br><br>


                <button
                    type="submit"
                    name="change_password"
                    class="btn"
                >

                    <i class="fas fa-lock"></i>

                    Change Password

                </button>


            </form>

        </section>



        <!-- ACCOUNT -->

        <section class="card">

            <h2>Account</h2>

            <p>

                <strong>Account Type:</strong>

                <?php
                echo htmlspecialchars(
                    ucfirst($_SESSION["role"])
                );
                ?>

            </p>

            <br>

            <a href="logout.php">

                <i class="fas fa-sign-out-alt"></i>

                Logout

            </a>

        </section>


    </main>

</div>


<script src="../js/main.js"></script>

</body>

</html>