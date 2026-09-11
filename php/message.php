<?php

require_once "auth.php";
require_once "db.php";

$user_id = $_SESSION["user_id"];
$message = "";


// SEND MESSAGE
if (isset($_POST["send_message"])) {

    $receiver_id = intval($_POST["receiver_id"]);
    $text = trim($_POST["message"]);

    if ($text != "") {

        $stmt = $conn->prepare(
            "INSERT INTO messages
            (sender_id, receiver_id, message)
            VALUES (?, ?, ?)"
        );

        $stmt->bind_param(
            "iis",
            $user_id,
            $receiver_id,
            $text
        );

        if ($stmt->execute()) {
            $message = "Message sent.";
        }

        $stmt->close();
    }
}


// GET USER TO MESSAGE
$receiver_id = null;

if (isset($_GET["user_id"]) && is_numeric($_GET["user_id"])) {

    $receiver_id = intval($_GET["user_id"]);

}


// GET CONVERSATION
$conversation = [];

if ($receiver_id !== null) {

    $stmt = $conn->prepare(
        "SELECT
            messages.id,
            messages.sender_id,
            messages.receiver_id,
            messages.message,
            messages.created_at,
            users.full_name

        FROM messages

        INNER JOIN users
            ON messages.sender_id = users.id

        WHERE
            (messages.sender_id = ? AND messages.receiver_id = ?)
            OR
            (messages.sender_id = ? AND messages.receiver_id = ?)

        ORDER BY messages.created_at ASC"
    );

    $stmt->bind_param(
        "iiii",
        $user_id,
        $receiver_id,
        $receiver_id,
        $user_id
    );

    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {

        $conversation[] = $row;

    }

    $stmt->close();
}


// GET USERS WHO HAVE MESSAGED THIS USER
$stmt = $conn->prepare(
    "SELECT DISTINCT
        users.id,
        users.full_name,
        users.role

    FROM messages

    INNER JOIN users
        ON users.id =
        CASE
            WHEN messages.sender_id = ? THEN messages.receiver_id
            ELSE messages.sender_id
        END

    WHERE
        messages.sender_id = ?
        OR
        messages.receiver_id = ?

    ORDER BY users.full_name ASC"
);

$stmt->bind_param(
    "iii",
    $user_id,
    $user_id,
    $user_id
);

$stmt->execute();

$users_result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Messages | SkillConnect</title>

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

            <h2>SkillConnect</h2>

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


            <li class="active">

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

                <h1>Messages</h1>

                <p>
                    Communicate with customers and providers.
                </p>

            </div>

        </header>



        <?php if ($message != ""): ?>

            <div class="card">

                <p>
                    <?php echo htmlspecialchars($message); ?>
                </p>

            </div>

        <?php endif; ?>



        <!-- USERS / CONVERSATIONS -->

        <section class="card">

            <h2>Conversations</h2>


            <?php if ($users_result->num_rows > 0): ?>


                <div class="conversation-list">

                    <?php while ($person = $users_result->fetch_assoc()): ?>

                        <a
                            href="messages.php?user_id=<?php echo $person["id"]; ?>"
                            class="conversation-item"
                        >

                            <strong>

                                <i class="fas fa-user"></i>

                                <?php
                                echo htmlspecialchars(
                                    $person["full_name"]
                                );
                                ?>

                            </strong>

                            <span>

                                <?php
                                echo htmlspecialchars(
                                    ucfirst($person["role"])
                                );
                                ?>

                                - Open conversation

                            </span>

                        </a>

                    <?php endwhile; ?>

                </div>


            <?php else: ?>

                <p>
                    No conversations yet.
                </p>

            <?php endif; ?>

        </section>



        <!-- CONVERSATION -->

        <?php if ($receiver_id !== null): ?>

         <section class="card">

    <h2>
        <i class="fas fa-comments"></i>
        Conversation
    </h2>

    <?php
    // Get the name of the person we are chatting with
    $chat_user_name = "User";

    $name_stmt = $conn->prepare(
        "SELECT full_name FROM users WHERE id = ?"
    );

    $name_stmt->bind_param(
        "i",
        $receiver_id
    );

    $name_stmt->execute();

    $name_result = $name_stmt->get_result();

    if ($name_row = $name_result->fetch_assoc()) {

        $chat_user_name = $name_row["full_name"];

    }

    $name_stmt->close();
    ?>

    <div class="chat-header">

        <i class="fas fa-user-circle"></i>

        <div>

            <strong>
                <?php echo htmlspecialchars($chat_user_name); ?>
            </strong>

            <small>
                Chat with this SkillConnect user
            </small>

        </div>

    </div>

                <?php if (count($conversation) > 0): ?>


                    <!-- MESSAGE BOX -->

                    <div class="message-box">

                        <?php foreach ($conversation as $chat): ?>

                            <?php

                            if ($chat["sender_id"] == $user_id) {

                                $message_class = "sent";

                            } else {

                                $message_class = "received";

                            }

                            ?>


                            <div
                                class="chat-message <?php echo $message_class; ?>"
                            >

                                <div class="message-content">


                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $chat["full_name"]
                                        );
                                        ?>

                                    </strong>


                                    <p>

                                        <?php
                                        echo nl2br(
                                            htmlspecialchars(
                                                $chat["message"]
                                            )
                                        );
                                        ?>

                                    </p>


                                    <small>

                                        <?php
                                        echo htmlspecialchars(
                                            $chat["created_at"]
                                        );
                                        ?>

                                    </small>


                                </div>

                            </div>


                        <?php endforeach; ?>

                    </div>


                <?php else: ?>

                    <div class="message-box">

                        <p>
                            No messages yet. Start the conversation.
                        </p>

                    </div>

                <?php endif; ?>



                <!-- SEND MESSAGE -->

                <form
                    method="POST"
                    class="message-form"
                >

                    <input
                        type="hidden"
                        name="receiver_id"
                        value="<?php echo $receiver_id; ?>"
                    >


                    <textarea
                        name="message"
                        rows="4"
                        placeholder="Write your message..."
                        required
                    ></textarea>


                    <br><br>


                    <button
                        type="submit"
                        name="send_message"
                        class="btn"
                    >

                        <i class="fas fa-paper-plane"></i>

                        Send Message

                    </button>

                </form>

            </section>

        <?php endif; ?>


    </main>

</div>


<script src="../js/main.js"></script>

</body>

</html>