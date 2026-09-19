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


/* =========================================
   GET ADMIN INFORMATION
========================================= */

$sql = "SELECT * FROM admin WHERE Aid = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $Aid);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


/* =========================================
   CHECK ADMIN EXISTS
========================================= */

if (mysqli_num_rows($result) == 0) {
    echo "Admin profile not found.";
    exit();
}

$admin = mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Profile</title>

    <link rel="stylesheet" href="../Assests/profile.css">

</head>


<body>


<div class="container">


    <!-- =====================================
         PAGE HEADER
    ====================================== -->

    <div class="page-header">

        <div>
            <h1>My Profile</h1>
            <p>View your admin account information</p>
        </div>

        <a href="2.dashboard.php" class="back-btn">
            ← Back to Dashboard
        </a>

    </div>



    <!-- =====================================
         PROFILE CARD
    ====================================== -->

    <div class="profile-card">


        <!-- PROFILE IMAGE -->

        <div class="profile-image-section">

            <?php if (!empty($admin['profile_image'])) { ?>

                <img
                    src="../User/uploads/admin/<?php echo htmlspecialchars($admin['profile_image']); ?>"
                    alt="Admin Profile"
                    class="profile-image"
                >

            <?php } else { ?>

                <div class="profile-placeholder">
                    <?php echo strtoupper(substr($admin['name'], 0, 1)); ?>
                </div>

            <?php } ?>


            <h2>
                <?php echo htmlspecialchars($admin['name']); ?>
            </h2>

            <p>Administrator</p>

        </div>



        <!-- PROFILE INFORMATION -->

        <div class="profile-information">


            <div class="info-box">

                <span>Full Name</span>

                <strong>
                    <?php echo htmlspecialchars($admin['name']); ?>
                </strong>

            </div>


            <div class="info-box">

                <span>Username</span>

                <strong>
                    <?php echo htmlspecialchars($admin['username']); ?>
                </strong>

            </div>


            <div class="info-box">

                <span>Email</span>

                <strong>
                    <?php echo htmlspecialchars($admin['email']); ?>
                </strong>

            </div>


            <div class="info-box">

                <span>Phone</span>

                <strong>
                    <?php echo htmlspecialchars($admin['phone']); ?>
                </strong>

            </div>


            <div class="info-box">

                <span>Admin ID</span>

                <strong>
                    <?php echo htmlspecialchars($admin['Aid']); ?>
                </strong>

            </div>


            <div class="info-box">

                <span>Account Created</span>

                <strong>
                    <?php echo htmlspecialchars($admin['created_at']); ?>
                </strong>

            </div>


        </div>



        <!-- ACTION BUTTONS -->

        <div class="profile-actions">

            <a href="EditProfile.php?id=<?php echo $admin['Aid']; ?>" class="edit-btn">
                Edit Profile
            </a>

            <a href="ChangePassword.php" class="password-btn">
                Change Password
            </a>

            <a
                href="3.logout.php"
                class="logout-btn"
                onclick="return confirm('Are you sure you want to logout?');"
            >
                Logout
            </a>

        </div>


    </div>

</div>


</body>

</html>