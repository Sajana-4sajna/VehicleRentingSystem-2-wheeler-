<?php

session_start();
include("db.php");

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();

}

$sql = "SELECT * FROM vehicles WHERE status = 'Available'";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Vehicles</title>
    <link rel="stylesheet" href="../assets/css1.css">
</head>
<body>
    <header>
        <h2>Vehicle Renting System(2-Wheleers)</h2>
        <nav>
            <a href="dashboard.php">
                Dashboard
            </a>

            <a href="my_bookings.php">
                My Bookings
            </a>

            <a href="logout.php" onclick="return confirmLogout();">
                Logout
            </a>
        </nav>
    </header>

    <h1>Available Vehicles</h1>

    <div class="vehicle-container">
        <?php

        if (mysqli_num_rows($result) > 0)

            while ($row = mysqli_fetch_assoc($result)) {

        ?>

        <div class="vehicle-card">

            <img src="../assets/images/<?php echo htmlspecialchars($row['image']); ?>" alt="Vehicle">
            <h2>
                <?php echo htmlspecialchars($row['vehicle_name']); ?>
            </h2>

            <p>
                <strong>Brand:</strong>
                <?php echo htmlspecialchars($row['brand']); ?>
            </p>

            <p>
                <strong>Model:</strong>
                <?php echo htmlspecialchars($row['model']); ?>
            </p>

            <p>
                <strong>Price:</strong>
                Rs. <?php echo htmlspecialchars($row['price']); ?> / Day
            </p>

            <p>
                <strong>Status:</strong>
                <?php echo htmlspecialchars($row['status']); ?>
            </p>
            <?php if (isset($_SESSION['user_id'])) { ?>
            <a href="booking.php?id=<?php echo $row['id']; ?>" class="btn">
                Book Now
            </a>

        </div>

        <?php } else { ?>
        <a href="login.php" class="btn">
            Login To Book.
        </a>
        <?php } }?>
    </div>

    <script src="../assets/js/project.js"></script>
</body>
</html>