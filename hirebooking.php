<?php

require_once "auth.php";
require_once "db.php";

// Only customers can book providers
if ($_SESSION["role"] != "customer") {
    die("Access denied. Customers only.");
}


// Check provider ID
if (!isset($_GET["provider_id"]) || !is_numeric($_GET["provider_id"])) {
    die("Invalid provider.");
}

$provider_id = intval($_GET["provider_id"]);


// Get provider information
$stmt = $conn->prepare("
    SELECT
        users.id,
        users.full_name,
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


// Booking message
$message = "";
$error = "";


// Handle booking
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $service_id = $_POST["service_id"] ?? "";
    $booking_date = $_POST["booking_date"] ?? "";
    $booking_time = $_POST["booking_time"] ?? "";
    $booking_message = trim($_POST["message"] ?? "");


    // Check required information
    if (
        !is_numeric($service_id) ||
        $booking_date == "" ||
        $booking_time == ""
    ) {

        $error = "Please select a service, date and time.";

    } else {

        $service_id = intval($service_id);


        // Check that selected service belongs to this provider
        $check = $conn->prepare("
            SELECT id
            FROM services
            WHERE id = ?
              AND provider_id = ?
        ");

        $check->bind_param(
            "ii",
            $service_id,
            $provider_id
        );

        $check->execute();

        $service_result = $check->get_result();


        if ($service_result->num_rows == 0) {

            $error = "Invalid service selected.";

        } else {

            // Insert booking
            $customer_id = $_SESSION["user_id"];

            $insert = $conn->prepare("
                INSERT INTO bookings
                (
                    customer_id,
                    provider_id,
                    service_id,
                    booking_date,
                    booking_time,
                    message
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $insert->bind_param(
                "iiisss",
                $customer_id,
                $provider_id,
                $service_id,
                $booking_date,
                $booking_time,
                $booking_message
            );


            if ($insert->execute()) {

                $message = "Booking submitted successfully.";

            } else {

                $error = "Something went wrong. Please try again.";

            }

            $insert->close();
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

    <title>
        Hire <?php echo htmlspecialchars($provider["full_name"]); ?>
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
            href="view-provider.php?id=<?php echo $provider_id; ?>"
            class="btn"
        >

            <i class="fas fa-arrow-left"></i>

            Back to Provider

        </a>


        <div class="profile-card">

            <h1>
                Hire
                <?php echo htmlspecialchars($provider["full_name"]); ?>
            </h1>


            <p>

                <strong>Skill:</strong>

                <?php
                echo htmlspecialchars(
                    $provider["skill"]
                );
                ?>

            </p>


            <p>

                <strong>Location:</strong>

                <?php
                echo htmlspecialchars(
                    $provider["location"]
                );
                ?>

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

        </div>



        <!-- SUCCESS MESSAGE -->

        <?php if ($message != ""): ?>

            <div class="profile-card">

                <p>
                    ✅
                    <?php echo htmlspecialchars($message); ?>
                </p>


                <a
                    href="my-bookings.php"
                    class="btn"
                >

                    View My Bookings

                </a>

            </div>

        <?php endif; ?>



        <!-- ERROR MESSAGE -->

        <?php if ($error != ""): ?>

            <div class="profile-card">

                <p>
                    ❌
                    <?php echo htmlspecialchars($error); ?>
                </p>

            </div>

        <?php endif; ?>



        <!-- BOOKING FORM -->

        <?php if ($services->num_rows > 0): ?>

            <div class="profile-card">

                <h2>
                    Book a Service
                </h2>


                <form method="POST">


                    <!-- SERVICE -->

                    <div class="input-group">

                        <label>
                            Select Service
                        </label>


                        <select
                            name="service_id"
                            required
                        >

                            <option value="">
                                -- Select a Service --
                            </option>


                            <?php while ($service = $services->fetch_assoc()): ?>

                                <option
                                    value="<?php echo $service["id"]; ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $service["service_name"]
                                    );
                                    ?>

                                    -
                                    ₦<?php
                                    echo number_format(
                                        $service["price"],
                                        2
                                    );
                                    ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>



                    <!-- DATE -->

                    <div class="input-group">

                        <label>
                            Booking Date
                        </label>


                        <input
                            type="date"
                            name="booking_date"
                            min="<?php echo date("Y-m-d"); ?>"
                            required
                        >

                    </div>



                    <!-- TIME -->

                    <div class="input-group">

                        <label>
                            Booking Time
                        </label>


                        <input
                            type="time"
                            name="booking_time"
                            required
                        >

                    </div>



                    <!-- MESSAGE -->

                    <div class="input-group">

                        <label>
                            Message to Provider
                        </label>


                        <textarea
                            name="message"
                            rows="5"
                            placeholder="Tell the provider what you need..."
                        ></textarea>

                    </div>



                    <!-- SUBMIT -->

                    <button
                        type="submit"
                        class="btn"
                    >

                        <i class="fas fa-calendar-check"></i>

                        Submit Booking

                    </button>


                </form>

            </div>


        <?php else: ?>


            <div class="profile-card">

                <h2>
                    No Services Available
                </h2>


                <p>
                    This provider has not added any services yet.
                </p>


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