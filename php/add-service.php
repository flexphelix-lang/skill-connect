<?php

require_once "auth.php";
require_once "db.php";

// Only providers can access this page
if ($_SESSION["role"] != "provider") {
    die("Access denied. Providers only.");
}

$message = "";
$error = "";


// Add service
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $service_name = trim($_POST["service_name"]);
    $description = trim($_POST["description"]);
    $price = trim($_POST["price"]);


    // Check required fields
    if ($service_name == "" || $price == "") {

        $error = "Please enter the service name and price.";

    } elseif (!is_numeric($price) || $price < 0) {

        $error = "Please enter a valid price.";

    } else {

        $provider_id = $_SESSION["user_id"];

        $stmt = $conn->prepare("
            INSERT INTO services
            (provider_id, service_name, description, price)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "issd",
            $provider_id,
            $service_name,
            $description,
            $price
        );


        if ($stmt->execute()) {

            $message = "Service added successfully.";

        } else {

            $error = "Unable to add service.";

        }

        $stmt->close();
    }
}


// Get provider's services
$provider_id = $_SESSION["user_id"];

$stmt = $conn->prepare("
    SELECT
        id,
        service_name,
        description,
        price,
        created_at
    FROM services
    WHERE provider_id = ?
    ORDER BY created_at DESC
");

$stmt->bind_param("i", $provider_id);
$stmt->execute();

$services = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Add Services - SkillConnect</title>

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

        <p>Provider Dashboard</p>


        <nav>

            <a href="provider-dashboard.php">
                <i class="fas fa-home"></i>
                Dashboard
            </a>


            <a href="provider-profile.php">
                <i class="fas fa-user"></i>
                My Profile
            </a>


            <a href="add-service.php">
                <i class="fas fa-plus-circle"></i>
                My Services
            </a>


            <a href="provider-bookings.php">
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
            My Services
        </h1>

        <p>
            Add the services you offer on SkillConnect.
        </p>


        <!-- SUCCESS MESSAGE -->

        <?php if ($message != ""): ?>

            <div class="profile-card">

                <p>
                    ✅
                    <?php echo htmlspecialchars($message); ?>
                </p>

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


        <!-- ADD SERVICE FORM -->

        <div class="profile-card">

            <h2>
                Add New Service
            </h2>


            <form method="POST">


                <div class="input-group">

                    <label>
                        Service Name
                    </label>

                    <input
                        type="text"
                        name="service_name"
                        placeholder="Example: Website Design"
                        required
                    >

                </div>


                <div class="input-group">

                    <label>
                        Description
                    </label>

                    <textarea
                        name="description"
                        rows="5"
                        placeholder="Describe the service you offer..."
                    ></textarea>

                </div>


                <div class="input-group">

                    <label>
                        Price
                    </label>

                    <input
                        type="number"
                        name="price"
                        step="0.01"
                        min="0"
                        placeholder="Example: 50000"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="btn"
                >

                    <i class="fas fa-plus"></i>

                    Add Service

                </button>


            </form>

        </div>



        <!-- EXISTING SERVICES -->

        <div class="profile-card">

            <h2>
                My Services
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


                        <strong>

                            ₦<?php
                            echo number_format(
                                $service["price"],
                                2
                            );
                            ?>

                        </strong>


                        <br><br>


                        <small>

                            Added on
                            <?php
                            echo date(
                                "F j, Y",
                                strtotime(
                                    $service["created_at"]
                                )
                            );
                            ?>

                        </small>

                    </div>

                <?php endwhile; ?>


            <?php else: ?>

                <p>
                    You have not added any services yet.
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