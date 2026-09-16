<?php

session_start();
include("db.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username == "" || $password == "") {

        $message = "Please enter username and password.";

    } else {

        $sql = "SELECT * FROM customer WHERE username = ?";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param($stmt, "s", $username);

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {

            $row = mysqli_fetch_assoc($result);
         

            if (password_verify($password, $row['password'])) {

                session_regenerate_id(true);

                $_SESSION['Cid'] = $row['Cid'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['C_name'] = $row['C_name'];

                header("Location: dashboard.php");
                exit();

            } else {

                $message = "Incorrect password.";

            }

        } else {

            $message = "Username not found.";

        }

        mysqli_stmt_close($stmt);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="../Assests/css.css">
</head>

<body>
    <div class="form-container">
        <h2>User Login</h2>

        <?php if ($message != "") { ?>

            <div class="error-message">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php } ?>

        <form method="POST" action="login.php" onsubmit="return validateLogin();">
            <label>Username</label>
            <input type="text" id="username" name="username" placeholder="Enter your username">
            <label>Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password">
            <input type="submit" value="Login">
        </form>

        <p>
            <a href="forgot_password.php">Forgot Password?</a>
        </p>

        <p>
            Don't have an account?
            <a href="register.php">Register</a>
        </p>

    </div>

    <script src="../Assests/js/project.js"></script>

</body>
</html>