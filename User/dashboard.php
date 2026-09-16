<?php
session_start();
if (!isset($_SESSION['Cid'])) {
    header("Location: login.php");
    exit();

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../Assests/css.css">
</head>

<body>
    <div class="container">
        <h1>
            Welcome,
            <?php echo htmlspecialchars($_SESSION['C_name']); ?>
        </h1>

        <h3>Vehicle Renting System (2-Wheelers)</h3>

        <p>You have successfully logged in.</p>

        <a href="vehicles.php">
            View Vehicles
        </a>
        <br><br>
        <a href="my_bookings.php">
            My Bookings
        </a>
        <br><br>
        <a href="logout.php" onclick="return confirmLogout();">
            Logout
        </a>

    </div>

    <script src="../Assests/js/project.js"></script>
</body>
</html>