
<?php

session_start();

$conn = mysqli_connect("localhost", "root", "", "vehicle_renting_system");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}


/* =========================================
   CHECK ADMIN LOGIN
========================================= */

if (!isset($_SESSION['Aid'])) {
    header("Location: 1.login.php");
    exit();
}

$Aid = (int) $_SESSION['Aid'];

$error = "";
$success = "";


/* =========================================
   CHANGE PASSWORD
========================================= */

if (isset($_POST['change_password'])) {

    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];


    /* -----------------------------------------
       CHECK EMPTY FIELDS
    ----------------------------------------- */

    if (
        empty($current_password) ||
        empty($new_password) ||
        empty($confirm_password)
    ) {

        $error = "Please fill in all fields.";

    }


    /* -----------------------------------------
       CHECK NEW PASSWORD MATCH
    ----------------------------------------- */

    elseif ($new_password !== $confirm_password) {

        $error = "New password and confirm password do not match.";

    }


    /* -----------------------------------------
       CHECK PASSWORD LENGTH
    ----------------------------------------- */

    elseif (strlen($new_password) < 6) {

        $error = "New password must be at least 6 characters long.";

    }


    else {

        /* -----------------------------------------
           GET CURRENT ADMIN PASSWORD
        ----------------------------------------- */

        $sql = "SELECT password FROM admin WHERE Aid = ?";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param($stmt, "i", $Aid);

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);


        if (mysqli_num_rows($result) == 0) {

            $error = "Admin account not found.";

        } else {

            $admin = mysqli_fetch_assoc($result);

            $stored_password = $admin['password'];


            /* -----------------------------------------
               VERIFY CURRENT PASSWORD
            ----------------------------------------- */

            if (!password_verify($current_password, $stored_password)) {

                $error = "Current password is incorrect.";

            }


            /* -----------------------------------------
               CHECK NEW PASSWORD SAME AS OLD
            ----------------------------------------- */

            elseif (password_verify($new_password, $stored_password)) {

                $error = "New password must be different from the current password.";

            }


            else {

                /* -----------------------------------------
                   HASH NEW PASSWORD
                ----------------------------------------- */

                $hashed_password = password_hash(
                    $new_password,
                    PASSWORD_DEFAULT
                );


                /* -----------------------------------------
                   UPDATE PASSWORD
                ----------------------------------------- */

                $update_sql = "UPDATE admin
                               SET password = ?
                               WHERE Aid = ?";

                $update_stmt = mysqli_prepare($conn, $update_sql);

                mysqli_stmt_bind_param(
                    $update_stmt,
                    "si",
                    $hashed_password,
                    $Aid
                );


                if (mysqli_stmt_execute($update_stmt)) {

                    $success = "Password changed successfully.";

                } else {

                    $error = "Failed to change password.";

                }

            }

        }

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Change Password</title>

    <link rel="stylesheet" href="../Assests/changePassword.css">

</head>


<body>


<div class="container">


    <!-- =====================================
         PAGE HEADER
    ====================================== -->

    <div class="page-header">

        <div>

            <h1>Change Password</h1>

            <p>
                Update your admin account password
            </p>

        </div>


        <a href="profile.php" class="back-btn">
            ← Back to Profile
        </a>

    </div>



    <!-- =====================================
         PASSWORD CARD
    ====================================== -->

    <div class="password-card">


        <?php if (!empty($error)) { ?>

            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php } ?>


        <?php if (!empty($success)) { ?>

            <div class="success-message">
                <?php echo htmlspecialchars($success); ?>
            </div>

        <?php } ?>


        <form method="POST">


            <!-- CURRENT PASSWORD -->

            <div class="form-group">

                <label for="current_password">
                    Current Password
                </label>

                <input
                    type="password"
                    name="current_password"
                    id="current_password"
                    required
                >

            </div>


            <!-- NEW PASSWORD -->

            <div class="form-group">

                <label for="new_password">
                    New Password
                </label>

                <input
                    type="password"
                    name="new_password"
                    id="new_password"
                    required
                >

                <small>
                    Password must be at least 6 characters.
                </small>

            </div>


            <!-- CONFIRM PASSWORD -->

            <div class="form-group">

                <label for="confirm_password">
                    Confirm New Password
                </label>

                <input
                    type="password"
                    name="confirm_password"
                    id="confirm_password"
                    required
                >

            </div>


            <!-- BUTTONS -->

            <div class="form-actions">

                <a href="profile.php" class="cancel-btn">
                    Cancel
                </a>

                <button
                    type="submit"
                    name="change_password"
                    class="change-btn"
                >
                    Change Password
                </button>

            </div>


        </form>


    </div>


</div>


</body>

</html>


