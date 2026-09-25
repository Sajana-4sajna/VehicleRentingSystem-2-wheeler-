
<?php

session_start();

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "vehicle_renting_system"
);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}


/* ===============================
   CHECK ADMIN LOGIN
================================ */

if (!isset($_SESSION['Aid'])) {
    header("Location: 1.login.php");
    exit();
}


/* ===============================
   CHECK BOOKING ID
================================ */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: 2.4.bookingSlidebar.php");
    exit();
}

$booking_id = intval($_GET['id']);


/* ===============================
   FETCH BOOKING DETAILS
================================ */

$sql = "SELECT
            booking.Bid,
            booking.pickup_location,
            booking.pickup_date,
            booking.return_date,
            booking.status,
            booking.created_at,

            customer.Cid,
            customer.C_name,
            customer.email,
            customer.phone,
            customer.address,
            customer.citizenship,
            customer.liscense_no,
            customer.liscense,

            vehicle.Vid,
            vehicle.name AS vehicle_name,
            vehicle.brand,
            vehicle.Vno,
            vehicle.type,
            vehicle.category,
            vehicle.model,
            vehicle.price_per_day,
            vehicle.image

        FROM booking

        INNER JOIN customer
            ON booking.Cid = customer.Cid

        INNER JOIN vehicle
            ON booking.Vid = vehicle.Vid

        WHERE booking.Bid = ?";


$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $booking_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$booking = mysqli_fetch_assoc($result);


/* ===============================
   BOOKING NOT FOUND
================================ */

if (!$booking) {
    die("Booking not found.");
}


/* ===============================
   UPDATE BOOKING STATUS
================================ */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['status'])) {

        $new_status = $_POST['status'];

        $allowed_status = [
            "Pending",
            "Approved",
            "Completed",
            "Cancelled"
        ];

        if (in_array($new_status, $allowed_status)) {

            $update_sql = "UPDATE booking
                           SET status = ?
                           WHERE Bid = ?";

            $update_stmt = mysqli_prepare(
                $conn,
                $update_sql
            );

            mysqli_stmt_bind_param(
                $update_stmt,
                "si",
                $new_status,
                $booking_id
            );

            mysqli_stmt_execute($update_stmt);

            header(
                "Location: 2.4.1.viewBooking.php?id="
                . $booking_id
            );

            exit();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>View Booking</title>

    <link rel="stylesheet"
          href="../Assests/2.4.1.viewBooking.css">

</head>


<body>


<div class="booking-page">


    <!-- ===============================
         PAGE HEADER
    ================================= -->

    <div class="page-header">

        <div>

            <h1>Booking Details</h1>

            <p>
                Booking ID:
                <strong>
                    #<?php echo $booking['Bid']; ?>
                </strong>
            </p>

        </div>


        <a href="2.4.bookingSlidebar.php"
           class="back-btn">

            ← Back to Bookings

        </a>

    </div>



    <!-- ===============================
         BOOKING INFORMATION
    ================================= -->

    <div class="section">

        <h2>Booking Information</h2>


        <div class="details-grid">

            <div class="detail">

                <span>Booking ID</span>

                <strong>
                    #<?php echo $booking['Bid']; ?>
                </strong>

            </div>


            <div class="detail">

                <span>Status</span>

                <strong>
                    <?php echo htmlspecialchars($booking['status']); ?>
                </strong>

            </div>


            <div class="detail">

                <span>Pickup Location</span>

                <strong>
                    <?php
                    echo htmlspecialchars(
                        $booking['pickup_location']
                    );
                    ?>
                </strong>

            </div>


            <div class="detail">

                <span>Pickup Date</span>

                <strong>
                    <?php echo $booking['pickup_date']; ?>
                </strong>

            </div>


            <div class="detail">

                <span>Return Date</span>

                <strong>
                    <?php echo $booking['return_date']; ?>
                </strong>

            </div>


            <div class="detail">

                <span>Booked On</span>

                <strong>
                    <?php echo $booking['created_at']; ?>
                </strong>

            </div>

        </div>

    </div>



    <!-- ===============================
         CUSTOMER INFORMATION
    ================================= -->

    <div class="section">

        <h2>Customer Information</h2>


        <div class="details-grid">

            <div class="detail">

                <span>Customer ID</span>

                <strong>
                    <?php echo $booking['Cid']; ?>
                </strong>

            </div>


            <div class="detail">

                <span>Name</span>

                <strong>
                    <?php
                    echo htmlspecialchars(
                        $booking['C_name']
                    );
                    ?>
                </strong>

            </div>


            <div class="detail">

                <span>Email</span>

                <strong>
                    <?php
                    echo htmlspecialchars(
                        $booking['email']
                    );
                    ?>
                </strong>

            </div>


            <div class="detail">

                <span>Phone</span>

                <strong>
                    <?php
                    echo htmlspecialchars(
                        $booking['phone']
                    );
                    ?>
                </strong>

            </div>


            <div class="detail">

                <span>Address</span>

                <strong>
                    <?php
                    echo htmlspecialchars(
                        $booking['address']
                    );
                    ?>
                </strong>

            </div>


            <div class="detail">

                <span>License Number</span>

                <strong>
                    <?php
                    echo htmlspecialchars(
                        $booking['liscense_no']
                    );
                    ?>
                </strong>

            </div>

        </div>

    </div>



    <!-- ===============================
         VEHICLE INFORMATION
    ================================= -->

    <div class="section">

        <h2>Vehicle Information</h2>


        <div class="vehicle-box">


            <?php if (!empty($booking['image'])) { ?>

                <img
                    src="../User/uploads/vehicles/<?php
                    echo htmlspecialchars($booking['image']);
                    ?>"
                    alt="Vehicle"
                    class="vehicle-image"
                >

            <?php } else { ?>

                <div class="no-image">
                    No Vehicle Image
                </div>

            <?php } ?>


            <div class="vehicle-details">


                <div class="detail">

                    <span>Vehicle ID</span>

                    <strong>
                        <?php echo $booking['Vid']; ?>
                    </strong>

                </div>


                <div class="detail">

                    <span>Vehicle Name</span>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $booking['vehicle_name']
                        );
                        ?>
                    </strong>

                </div>


                <div class="detail">

                    <span>Brand</span>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $booking['brand']
                        );
                        ?>
                    </strong>

                </div>


                <div class="detail">

                    <span>Vehicle Number</span>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $booking['Vno']
                        );
                        ?>
                    </strong>

                </div>


                <div class="detail">

                    <span>Type</span>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $booking['type']
                        );
                        ?>
                    </strong>

                </div>


                <div class="detail">

                    <span>Category</span>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $booking['category']
                        );
                        ?>
                    </strong>

                </div>


                <div class="detail">

                    <span>Model</span>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $booking['model']
                        );
                        ?>
                    </strong>

                </div>


                <div class="detail">

                    <span>Price Per Day</span>

                    <strong>
                        Rs.
                        <?php
                        echo htmlspecialchars(
                            $booking['price_per_day']
                        );
                        ?>
                    </strong>

                </div>

            </div>

        </div>

    </div>



    <!-- ===============================
         CHANGE STATUS
    ================================= -->

    <div class="section">

        <h2>Update Booking Status</h2>


        <form method="POST"
              class="status-form">


            <select name="status">

                <option value="Pending"
                    <?php
                    if ($booking['status'] == 'Pending')
                        echo 'selected';
                    ?>>
                    Pending
                </option>


                <option value="Approved"
                    <?php
                    if ($booking['status'] == 'Approved')
                        echo 'selected';
                    ?>>
                    Approved
                </option>


                <option value="Completed"
                    <?php
                    if ($booking['status'] == 'Completed')
                        echo 'selected';
                    ?>>
                    Completed
                </option>


                <option value="Cancelled"
                    <?php
                    if ($booking['status'] == 'Cancelled')
                        echo 'selected';
                    ?>>
                    Cancelled
                </option>

            </select>


            <button type="submit"
                    onclick="return confirm('Are you sure you want to update the booking status?');">

                Update Status

            </button>

        </form>

    </div>


</div>


</body>

</html>
