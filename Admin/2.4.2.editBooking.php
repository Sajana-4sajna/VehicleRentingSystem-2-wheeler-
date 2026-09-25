
<?php

session_start();

/* =====================================================
   DATABASE CONNECTION
===================================================== */

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "vehicle_renting_system"
);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}


/* =====================================================
   CHECK ADMIN LOGIN
===================================================== */

if (!isset($_SESSION['Aid'])) {
    header("Location: 1.login.php");
    exit();
}


/* =====================================================
   CHECK BOOKING ID
===================================================== */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid booking ID.");
}

$Bid = (int) $_GET['id'];


/* =====================================================
   UPDATE BOOKING
===================================================== */

if (isset($_POST['update_booking'])) {

    $Cid = $_POST['Cid'];
    $Vid = $_POST['Vid'];
    $pickup_location = trim($_POST['pickup_location']);
    $pickup_date = $_POST['pickup_date'];
    $return_date = $_POST['return_date'];
    $status = $_POST['status'];


    /* ---------------------------------------------
       BASIC VALIDATION
    --------------------------------------------- */

    if (
        empty($Cid) ||
        empty($Vid) ||
        empty($pickup_location) ||
        empty($pickup_date) ||
        empty($return_date) ||
        empty($status)
    ) {
        $error = "Please fill all the fields.";
    }

    elseif (strtotime($return_date) < strtotime($pickup_date)) {
        $error = "Return date cannot be before pickup date.";
    }

    else {

        /* -----------------------------------------
           UPDATE QUERY
        ----------------------------------------- */

        $update_sql = "UPDATE booking
                       SET
                           Cid = ?,
                           Vid = ?,
                           pickup_location = ?,
                           pickup_date = ?,
                           return_date = ?,
                           status = ?
                       WHERE Bid = ?";

        $stmt = mysqli_prepare($conn, $update_sql);

        mysqli_stmt_bind_param(
            $stmt,
            "iissssi",
            $Cid,
            $Vid,
            $pickup_location,
            $pickup_date,
            $return_date,
            $status,
            $Bid
        );


        if (mysqli_stmt_execute($stmt)) {

            header("Location: 2.4.bookingSlidebar.php");
            exit();

        } else {

            $error = "Failed to update booking: " .
                     mysqli_error($conn);
        }

        mysqli_stmt_close($stmt);
    }
}


/* =====================================================
   GET BOOKING INFORMATION
===================================================== */

$sql = "SELECT *
        FROM booking
        WHERE Bid = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $Bid
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$booking = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* =====================================================
   CHECK BOOKING EXISTS
===================================================== */

if (!$booking) {
    die("Booking not found.");
}


/* =====================================================
   GET CUSTOMERS
===================================================== */

$customer_sql = "SELECT Cid, C_name, phone
                 FROM customer
                 ORDER BY C_name ASC";

$customer_result = mysqli_query(
    $conn,
    $customer_sql
);


/* =====================================================
   GET VEHICLES
===================================================== */

$vehicle_sql = "SELECT Vid, name, Vno, price_per_day
                FROM vehicle
                ORDER BY name ASC";

$vehicle_result = mysqli_query(
    $conn,
    $vehicle_sql
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Edit Booking - Admin</title>

    <!-- MATERIAL SYMBOLS -->

    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined"
    >

    <!-- EDIT BOOKING CSS -->

    <link
        rel="stylesheet"
        href="../Assests/2.4.2.editBooking.css"
    >

</head>


<body>


<!-- =====================================================
     MAIN CONTENT
===================================================== -->

<div class="main-content">


    <div class="page-header">

        <div>

            <h1>Edit Booking</h1>

            <p>
                Update booking information
            </p>

        </div>


        <a
            href="2.4.bookingSlidebar.php"
            class="back-btn"
        >

            <span class="material-symbols-outlined">
                arrow_back
            </span>

            Back to Bookings

        </a>

    </div>


    <!-- =================================================
         ERROR MESSAGE
    ================================================== -->

    <?php if (isset($error)) { ?>

        <div class="error-message">

            <span class="material-symbols-outlined">
                error
            </span>

            <?php echo htmlspecialchars($error); ?>

        </div>

    <?php } ?>


    <!-- =================================================
         FORM
    ================================================== -->

    <div class="form-card">


        <div class="booking-id">

            <span class="material-symbols-outlined">
                confirmation_number
            </span>

            Booking #<?php echo $booking['Bid']; ?>

        </div>


        <form method="POST">


            <!-- CUSTOMER -->

            <div class="form-group">

                <label for="Cid">
                    Customer
                </label>

                <select
                    name="Cid"
                    id="Cid"
                    required
                >

                    <option value="">
                        Select Customer
                    </option>


                    <?php while ($customer = mysqli_fetch_assoc($customer_result)) { ?>

                        <option
                            value="<?php echo $customer['Cid']; ?>"
                            <?php
                            if ($booking['Cid'] == $customer['Cid']) {
                                echo "selected";
                            }
                            ?>
                        >

                            <?php
                            echo htmlspecialchars(
                                $customer['C_name']
                            );
                            ?>

                            -
                            <?php
                            echo htmlspecialchars(
                                $customer['phone']
                            );
                            ?>

                        </option>

                    <?php } ?>

                </select>

            </div>


            <!-- VEHICLE -->

            <div class="form-group">

                <label for="Vid">
                    Vehicle
                </label>

                <select
                    name="Vid"
                    id="Vid"
                    required
                >

                    <option value="">
                        Select Vehicle
                    </option>


                    <?php while ($vehicle = mysqli_fetch_assoc($vehicle_result)) { ?>

                        <option
                            value="<?php echo $vehicle['Vid']; ?>"
                            <?php
                            if ($booking['Vid'] == $vehicle['Vid']) {
                                echo "selected";
                            }
                            ?>
                        >

                            <?php
                            echo htmlspecialchars(
                                $vehicle['name']
                            );
                            ?>

                            -

                            <?php
                            echo htmlspecialchars(
                                $vehicle['Vno']
                            );
                            ?>

                            -
                            Rs.
                            <?php
                            echo htmlspecialchars(
                                $vehicle['price_per_day']
                            );
                            ?>/day

                        </option>

                    <?php } ?>

                </select>

            </div>


            <!-- PICKUP LOCATION -->

            <div class="form-group">

                <label for="pickup_location">
                    Pickup Location
                </label>

                <input
                    type="text"
                    name="pickup_location"
                    id="pickup_location"
                    value="<?php
                        echo htmlspecialchars(
                            $booking['pickup_location']
                        );
                    ?>"
                    placeholder="Enter pickup location"
                    required
                >

            </div>


            <!-- DATE ROW -->

            <div class="form-row">


                <!-- PICKUP DATE -->

                <div class="form-group">

                    <label for="pickup_date">
                        Pickup Date
                    </label>

                    <input
                        type="date"
                        name="pickup_date"
                        id="pickup_date"
                        value="<?php
                            echo htmlspecialchars(
                                $booking['pickup_date']
                            );
                        ?>"
                        required
                    >

                </div>


                <!-- RETURN DATE -->

                <div class="form-group">

                    <label for="return_date">
                        Return Date
                    </label>

                    <input
                        type="date"
                        name="return_date"
                        id="return_date"
                        value="<?php
                            echo htmlspecialchars(
                                $booking['return_date']
                            );
                        ?>"
                        required
                    >

                </div>

            </div>


            <!-- STATUS -->

            <div class="form-group">

                <label for="status">
                    Booking Status
                </label>

                <select
                    name="status"
                    id="status"
                    required
                >

                    <option
                        value="Pending"
                        <?php
                        if ($booking['status'] == "Pending") {
                            echo "selected";
                        }
                        ?>
                    >
                        Pending
                    </option>


                    <option
                        value="Approved"
                        <?php
                        if ($booking['status'] == "Approved") {
                            echo "selected";
                        }
                        ?>
                    >
                        Approved
                    </option>


                    <option
                        value="Completed"
                        <?php
                        if ($booking['status'] == "Completed") {
                            echo "selected";
                        }
                        ?>
                    >
                        Completed
                    </option>


                    <option
                        value="Cancelled"
                        <?php
                        if ($booking['status'] == "Cancelled") {
                            echo "selected";
                        }
                        ?>
                    >
                        Cancelled
                    </option>

                </select>

            </div>


            <!-- BUTTONS -->

            <div class="form-actions">

                <a
                    href="2.4.bookingSlidebar.php"
                    class="cancel-btn"
                >
                    Cancel
                </a>


                <button
                    type="submit"
                    name="update_booking"
                    class="update-btn"
                >

                    <span class="material-symbols-outlined">
                        save
                    </span>

                    Update Booking

                </button>

            </div>


        </form>

    </div>

</div>


</body>

</html>
