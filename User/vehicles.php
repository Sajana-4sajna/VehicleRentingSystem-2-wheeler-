
<?php

session_start();
include("db.php");


/* =========================================
   CHECK CUSTOMER LOGIN
========================================= */

if (!isset($_SESSION['Cid'])) {

    header("Location: login.php");
    exit();

}


/* =========================================
   FETCH ALL AVAILABLE VEHICLES
========================================= */

$sql = "SELECT * FROM vehicle
        WHERE status = 'Available'";

$result = mysqli_query($conn, $sql);

if (!$result) {

    die("Query Failed: " . mysqli_error($conn));

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Available Vehicles</title>

    <link rel="stylesheet" href="../Assests/css1.css">

</head>


<body>


<!-- =========================================
     HEADER
========================================= -->

<header>

    <h2>
        Vehicle Renting System (2-Wheelers)
    </h2>


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



<!-- =========================================
     PAGE TITLE
========================================= -->

<h1>
    Available Vehicles
</h1>



<!-- =========================================
     VEHICLE CONTAINER
========================================= -->

<div class="vehicle-container">


<?php

if (mysqli_num_rows($result) > 0) {

    while ($row = mysqli_fetch_assoc($result)) {

?>


    <!-- =====================================
         VEHICLE CARD
    ====================================== -->

    <div class="vehicle-card">


        <!-- Vehicle Image -->

        <img
            src="uploads/vehicles/<?php echo htmlspecialchars($row['image']); ?>"
            alt="<?php echo htmlspecialchars($row['name']); ?>"
        >


        <!-- Vehicle Name -->

        <h2>

            <?php echo htmlspecialchars($row['name']); ?>

        </h2>


        <!-- Brand -->

        <p>

            <strong>Brand:</strong>

            <?php echo htmlspecialchars($row['brand']); ?>

        </p>


        <!-- Model -->

        <p>

            <strong>Model:</strong>

            <?php echo htmlspecialchars($row['model']); ?>

        </p>


        <!-- Price -->

        <p>

            <strong>Price:</strong>

            Rs. <?php echo htmlspecialchars($row['price_per_day']); ?> / Day

        </p>


        <!-- Status -->

        <p>

            <strong>Status:</strong>

            <?php echo htmlspecialchars($row['status']); ?>

        </p>


        <!-- Book Button -->

        <a
            href="booking.php?id=<?php echo $row['Vid']; ?>"
            class="btn"
        >
            Book Now
        </a>


    </div>


<?php

    }

} else {

?>


    <!-- No Available Vehicles -->

    <p>
        No vehicles are currently available.
    </p>


<?php

}

?>


</div>



<!-- =========================================
     JAVASCRIPT
========================================= -->

<script src="../Assests/js/project.js"></script>


</body>

</html>

