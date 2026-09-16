<?php

include("db.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check empty fields
    if (
        $username == "" ||
        $email == "" ||
        $new_password == "" ||
        $confirm_password == ""
    ) {

        $message = "Please fill in all fields.";

    }

    // Check password match
    elseif ($new_password !== $confirm_password) {

        $message = "Passwords do not match.";

    }

    // Check password length
    elseif (strlen($new_password) < 6) {

        $message = "Password must contain at least 6 characters.";

    }

    else {

        // Find user by username and email
        $sql = "SELECT id FROM users WHERE username = ? AND email = ?";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "ss",
            $username,
            $email
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {

            $row = mysqli_fetch_assoc($result);

            // Hash new password
            $hashed_password = password_hash(
                $new_password,
                PASSWORD_DEFAULT
            );

            // Update password
            $update_sql = "UPDATE users SET password = ? WHERE id = ?";

            $update_stmt = mysqli_prepare(
                $conn,
                $update_sql
            );

            mysqli_stmt_bind_param(
                $update_stmt,
                "si",
                $hashed_password,
                $row['id']
            );

            if (mysqli_stmt_execute($update_stmt)) {

                $message = "Password reset successful. You can now login.";

            } else {

                $message = "Failed to reset password.";

            }

            mysqli_stmt_close($update_stmt);

        } else {

            $message = "Username and email do not match.";

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
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../Assests/css.css">
</head>
<body>
    <div class="form-container">
        <h2>Reset Password</h2>
        <?php if ($message != "") { ?>
            <div class="error-message">
                <?php
                echo htmlspecialchars($message);
                ?>
            </div>
        <?php } ?>

        <form method="POST" action="forgot_password.php">
            <label>Username</label>
            <input type="text" name="username" placeholder="Enter your username">

            <label>Registered Email</label>
            <input type="email" name="email" placeholder="Enter your registered email">

            <label>New Password</label>
            <input type="password" name="new_password" placeholder="Enter new password">

            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" placeholder="Confirm new password">

            <input type="submit" value="Reset Password">
        </form>

        <p>
            Remember your password?
            <a href="login.php">Login</a>
        </p>
    </div>
</body>
</html>