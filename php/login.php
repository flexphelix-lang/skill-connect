<?php

session_start();

require_once "db.php";

$message = "";

if (isset($_POST["login"])) {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

   
    $stmt = $conn->prepare(
        "SELECT id, full_name, email, password, role 
         FROM users 
         WHERE email = ?"
    );

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();

        
        if (password_verify($password, $user["password"])) {

           
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["full_name"] = $user["full_name"];
            $_SESSION["email"] = $user["email"];
            $_SESSION["role"] = $user["role"];

            
            if ($user["role"] === "provider") {

                header("Location: provider-dashboard.php");
                exit();

            } elseif ($user["role"] === "admin") {

                header("Location: admin-dashboard.php");
                exit();

            } else {

                header("Location: customer-dashboard.php");
                exit();
            }

        } else {

            $message = "Incorrect password.";

        }

    } else {

        $message = "Account not found.";

    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - JobWalk</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <div class="login-container">

        <h1>JobWalk</h1>

        <h2>Login</h2>

        <?php if ($message != ""): ?>

            <p>
                <?php echo htmlspecialchars($message); ?>
            </p>

        <?php endif; ?>

        <form method="POST" action="login.php">

            <input
                type="email"
                name="email"
                placeholder="Email Address"
                required
            >

            <input
                type="password"
                name="password"
                placeholder="Password"
                required
            >

            <button type="submit" name="login">
                Login
            </button>

        </form>

        <p>
            Don't have an account?
            <a href="register.php">Create Account</a>
        </p>

    </div>

</body>

</html>