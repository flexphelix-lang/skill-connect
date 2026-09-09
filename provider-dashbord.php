<?php

require_once "auth.php";
require_once "db.php";


if ($_SESSION["role"] !== "provider") {
    header("Location: customer-dashboard.php");
    exit();
}

$user_id = $_SESSION["user_id"];


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



$stmt = $conn->prepare(
    "SELECT skill, description, location, price
     FROM provider_profiles
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$profile_result = $stmt->get_result();
$profile = $profile_result->fetch_assoc();

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Provider Dashboard - JobWalk</title>

    <link rel="stylesheet" href="css/dashboard.css">

</head>

<body>

<header>

    <h1>JobWalk</h1>

    <a href="logout.php">Logout</a>

</header>


<main>

    <h2>
        Welcome,
        <?php echo htmlspecialchars($user["full_name"]); ?>
    </h2>

    <p>
        Provider Dashboard
    </p>


   

    <section>

        <h3>My Information</h3>

        <p>
            <strong>Email:</strong>
            <?php echo htmlspecialchars($user["email"]); ?>
        </p>

        <p>
            <strong>Phone:</strong>
            <?php echo htmlspecialchars($user["phone"] ?? "Not provided"); ?>
        </p>

    </section>


   

    <section>

        <h3>My Service Profile</h3>

        <?php if ($profile): ?>

            <p>
                <strong>Skill:</strong>
                <?php echo htmlspecialchars($profile["skill"]); ?>
            </p>

            <p>
                <strong>Description:</strong>
                <?php echo htmlspecialchars($profile["description"]); ?>
            </p>

            <p>
                <strong>Location:</strong>
                <?php echo htmlspecialchars($profile["location"]); ?>
            </p>

            <p>
                <strong>Starting Price:</strong>
                ₦<?php echo htmlspecialchars($profile["price"]); ?>
            </p>

        <?php else: ?>

            <p>
                You have not created your service profile yet.
            </p>

            <a href="provider-profile.php">
                Create My Profile
            </a>

        <?php endif; ?>

    </section>


   

    <section>

        <h3>Quick Actions</h3>

        <a href="provider-profile.php">
            Edit Profile
        </a>

        <br><br>

        <a href="add-service.php">
            Add Service
        </a>

        <br><br>

        <a href="provider-bookings.php">
            View Booking Requests
        </a>

        <br><br>

        <a href="messages.php">
            Messages
        </a>

        <br><br>

        <a href="settings.php">
            Settings
        </a>

    </section>

</main>

</body>

</html>