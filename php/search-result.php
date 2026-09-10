<?php

require_once "auth.php";
require_once "db.php";


$search = "";

if (isset($_GET["search"])) {
    $search = trim($_GET["search"]);
}


$providers = [];


if ($search != "") {

    $search_term = "%" . $search . "%";

    $stmt = $conn->prepare(
        "SELECT
            provider_profiles.id,
            provider_profiles.user_id,
            provider_profiles.skill,
            provider_profiles.description,
            provider_profiles.location,
            provider_profiles.price,
            users.full_name

        FROM provider_profiles

        INNER JOIN users
        ON provider_profiles.user_id = users.id

        WHERE provider_profiles.skill LIKE ?
        OR provider_profiles.description LIKE ?
        OR provider_profiles.location LIKE ?

        ORDER BY provider_profiles.id DESC"
    );

    $stmt->bind_param(
        "sss",
        $search_term,
        $search_term,
        $search_term
    );

    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {

        $providers[] = $row;

    }

    $stmt->close();
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

    <title>Find Service Providers | JobWalk</title>


   

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


            <li class="active">

                <a href="search-results.php">

                    <i class="fas fa-search"></i>

                    Find Providers

                </a>

            </li>


            <li>

                <a href="hirebooking.php">

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



   

    <main class="content">


      

        <header class="topbar">

            <div>

                <h1>Find Skilled Professionals</h1>

                <p>
                    Find professionals who can help you get the job done.
                </p>

            </div>


            <div class="profile">

                <span>

                    <?php
                    echo htmlspecialchars($_SESSION["full_name"]);
                    ?>

                </span>

            </div>

        </header>



        

        <div class="search-box">

            <form
                action="search-results.php"
                method="GET"
            >

                <input
                    type="text"
                    name="search"
                    placeholder="What service do you need?"
                    value="<?php echo htmlspecialchars($search); ?>"
                    required
                >

                <button type="submit">

                    <i class="fas fa-search"></i>

                    Search

                </button>

            </form>

        </div>



        

        <section class="cards">


            <div class="card">

                <h3>Category</h3>

                <select>

                    <option>All Categories</option>

                    <option>Plumber</option>

                    <option>Electrician</option>

                    <option>Carpenter</option>

                    <option>Mechanic</option>

                    <option>Graphic Designer</option>

                    <option>Web Developer</option>

                </select>

            </div>



            <div class="card">

                <h3>Location</h3>

                <input
                    type="text"
                    placeholder="Enter Location"
                >

            </div>



            <div class="card">

                <h3>Minimum Rating</h3>

                <select>

                    <option>Any</option>

                    <option>★★★★★</option>

                    <option>★★★★☆</option>

                    <option>★★★☆☆</option>

                </select>

            </div>



            <div class="card">

                <h3>Budget</h3>

                <input
                    type="number"
                    placeholder="₦ Amount"
                >

            </div>

        </section>



        

        <section class="providers">

            <h2>Available Providers</h2>


            <div class="provider-grid">


                <?php if ($search == ""): ?>

                    <p>
                        Enter a service above to find providers.
                    </p>


                <?php elseif (count($providers) == 0): ?>

                    <p>

                        No providers found for:

                        <strong>

                            <?php
                            echo htmlspecialchars($search);
                            ?>

                        </strong>

                    </p>


                <?php else: ?>


                    <?php foreach ($providers as $provider): ?>


                        <div class="provider-card">


                            <div class="provider-info">

                                <h3>

                                    <?php
                                    echo htmlspecialchars(
                                        $provider["full_name"]
                                    );
                                    ?>

                                </h3>


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
                                    echo htmlspecialchars(
                                        $provider["description"]
                                    );
                                    ?>

                                </p>


                                <p>

                                    <strong>

                                        ₦<?php
                                        echo number_format(
                                            $provider["price"],
                                            2
                                        );
                                        ?>

                                    </strong>

                                </p>


                            </div>


                            <div class="provider-actions">


                                <a
                                    href="view-provider.php?id=<?php echo $provider["user_id"]; ?>"
                                >

                                    View Profile

                                </a>


                                <br><br>


                                <a
                                    href="hirebooking.php?provider_id=<?php echo $provider["user_id"]; ?>"
                                >

                                    Hire Provider

                                </a>


                            </div>


                        </div>


                    <?php endforeach; ?>


                <?php endif; ?>


            </div>

        </section>


    </main>

</div>



<script src="../js/main.js"></script>


</body>

</html>