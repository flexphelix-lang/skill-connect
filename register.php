<?php

require_once "db.php";

$message = "";

if (isset($_POST["register"])) {

    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $password = $_POST["password"];
    $role = $_POST["role"];

    
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {

        $message = "Email already exists.";

    } else {

       
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        
        $stmt = $conn->prepare(
            "INSERT INTO users (full_name, email, phone, password, role)
             VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "sssss",
            $full_name,
            $email,
            $phone,
            $hashed_password,
            $role
        );

        if ($stmt->execute()) {

            $message = "Registration successful! You can now login.";

        } else {

            $message = "Registration failed. Please try again.";
        }

        $stmt->close();
    }

    $check->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register - JobWalk</title>

    <link rel="stylesheet" href="css/register.css">
</head>

<body>

    <div class="register-container">

        <h1>JobWalk</h1>

        <h2>Create Account</h2>

        <?php if ($message != ""): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form method="POST" action="register.php">

            <input
                type="text"
                name="full_name"
                placeholder="Full Name"
                required
            >

            <input
                type="email"
                name="email"
                placeholder="Email Address"
                required
            >

            <input
                type="text"
                name="phone"
                placeholder="Phone Number"
            >

            <input
                type="password"
                name="password"
                placeholder="Password"
                required
            >

            <select name="role" required>
                <option value="customer">I need a service</option>
                <option value="provider">I provide services</option>
            </select>

            <button type="submit" name="register">
                Create Account
            </button>

        </form>

        <p>
            Already have an account?
            <a href="login.php">Login</a>
        </p>

    </div>

</body>

</html>