<?php

$conn = mysqli_connect("localhost","root", "","vehicle_renting_system");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

/* Check whether vehicle ID is received */
if (!isset($_GET['id'])) {
    header("Location: 2.3.vehicleSlidebar.php");
    exit();
}

$id = (int) $_GET['id'];

/* Get vehicle information */
$sql = "SELECT * FROM vehicle WHERE Vid = ?";
$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

/* Check whether vehicle exists */
if (mysqli_num_rows($result) == 0) {
    echo "Vehicle not found.";
    exit();
}

$row = mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>View Vehicle</title>

    <link rel="stylesheet" href="../Assests/2.3.2.veiwVehicle.css">
</head>

<body>

<div class="container">

    <!-- Page Header -->
    <div class="page-header">

        <h1>Vehicle Details</h1>

        <a href="2.3.vehicleSlidebar.php" class="back-btn">
            ← Back to Vehicles
        </a>

    </div>


    <!-- Vehicle Details Card -->
    <div class="vehicle-card">

        <!-- Vehicle Image -->
        <div class="vehicle-image">

            <?php if (!empty($row["image"])) { ?>

                <img 
                    src="../User/uploads/vehicles/<?php echo htmlspecialchars($row["image"]); ?>" 
                    alt="Vehicle Image"
                >

            <?php } else { ?>

                <div class="no-image">
                    No Image
                </div>

            <?php } ?>

        </div>


        <!-- Vehicle Information -->
        <div class="vehicle-info">

            <h2>
                <?php echo htmlspecialchars($row["name"]); ?>
            </h2>

            <p class="vehicle-number">
                Vehicle No: <?php echo htmlspecialchars($row["Vno"]); ?>
            </p>


            <div class="details">

                <div class="detail-box">
                    <span>Brand</span>
                    <strong>
                        <?php echo htmlspecialchars($row["brand"]); ?>
                    </strong>
                </div>


                <div class="detail-box">
                    <span>Model</span>
                    <strong>
                        <?php echo htmlspecialchars($row["model"]); ?>
                    </strong>
                </div>


                <div class="detail-box">
                    <span>Type</span>
                    <strong>
                        <?php echo htmlspecialchars($row["type"]); ?>
                    </strong>
                </div>


                <div class="detail-box">
                    <span>Category</span>
                    <strong>
                        <?php echo htmlspecialchars($row["category"]); ?>
                    </strong>
                </div>


                <div class="detail-box">
                    <span>Price Per Day</span>
                    <strong>
                        Rs. <?php echo htmlspecialchars($row["price_per_day"]); ?>
                    </strong>
                </div>


                <div class="detail-box">
                    <span>Status</span>

                    <?php
                    $status = $row["status"];

                    if ($status == "Available") {
                        echo '<strong class="status available">Available</strong>';
                    } 
                    elseif ($status == "Rented") {
                        echo '<strong class="status rented">Rented</strong>';
                    } 
                    else {
                        echo '<strong class="status unavailable">Unavailable</strong>';
                    }
                    ?>

                </div>

            </div>


            <!-- Dates -->
            <div class="dates">

                <p>
                    <strong>Created At:</strong>
                    <?php echo htmlspecialchars($row["created_at"]); ?>
                </p>

                <p>
                    <strong>Updated At:</strong>
                    <?php echo htmlspecialchars($row["updated_at"]); ?>
                </p>

            </div>


            <!-- Action Buttons -->
            <div class="actions">

                <a 
                    href="2.3.3.editVehicle.php?id=<?php echo $row['Vid']; ?>" 
                    class="edit-btn"
                >
                    Edit Vehicle
                </a>

                <a 
                    href="2.3.4.deleteVehicle.php?id=<?php echo $row['Vid']; ?>" 
                    class="delete-btn"
                    onclick="return confirm('Are you sure you want to delete this vehicle?');"
                >
                    Delete Vehicle
                </a>

            </div>

        </div>

    </div>

</div>

</body>
</html>