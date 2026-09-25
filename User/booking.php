<?php

session_start();
include("db.php");


/* =====================================================
   CHECK CUSTOMER LOGIN
===================================================== */

if (!isset($_SESSION['Cid'])) {
    header("Location: login.php");
    exit();
}

$Cid = (int) $_SESSION['Cid'];


/* =====================================================
   CHECK VEHICLE ID
===================================================== */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: vehicles.php");
    exit();
}

$Vid = (int) $_GET['id'];


/* =====================================================
   GET VEHICLE INFORMATION
===================================================== */

$sql = "SELECT * FROM vehicle WHERE Vid = ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database Error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $Vid);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$vehicle = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$vehicle) {
    die("Vehicle not found.");
}


/* =====================================================
   FORM SUBMISSION
===================================================== */

$error = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {


    /* GET FORM VALUES */

    $pickup_location =
        trim($_POST['pickup_location'] ?? '');

    $pickup_date =
        $_POST['pickup_date'] ?? '';

    $return_date =
        $_POST['return_date'] ?? '';

    $payment_method =
        $_POST['payment_method'] ?? '';


    /* =================================================
       VALIDATION
    ================================================= */

    if (
        empty($pickup_location) ||
        empty($pickup_date) ||
        empty($return_date)
    ) {

        $error = "Please fill all booking details.";
    }


    elseif ($payment_method !== "esewa") {

        $error = "Please select eSewa payment.";
    }


    else {


        $start = strtotime($pickup_date);

        $end = strtotime($return_date);

        $today = strtotime(date("Y-m-d"));


        if ($start === false || $end === false) {

            $error = "Invalid booking date.";
        }


        elseif ($start < $today) {

            $error = "Pickup date cannot be in the past.";
        }


        elseif ($end <= $start) {

            $error =
                "Return date must be after pickup date.";
        }


        else {


            /* =========================================
               CALCULATE TOTAL DAYS
            ========================================= */

            $total_days =
                ($end - $start) /
                (60 * 60 * 24);


            /* =========================================
               CALCULATE TOTAL AMOUNT
            ========================================= */

            $price_per_day =
                (float) $vehicle['price_per_day'];

            $total_amount =
                $total_days * $price_per_day;


            /* =========================================
               CHECK VEHICLE AVAILABILITY
            ========================================= */

            $availability_sql = "
                SELECT Bid
                FROM booking

                WHERE Vid = ?

                AND status IN (
                    'Pending',
                    'Approved'
                )

                AND pickup_date < ?
                AND return_date > ?

                LIMIT 1
            ";


            $availability_stmt =
                mysqli_prepare(
                    $conn,
                    $availability_sql
                );


            if (!$availability_stmt) {

                die(
                    "Availability Check Error: " .
                    mysqli_error($conn)
                );
            }


            mysqli_stmt_bind_param(
                $availability_stmt,
                "iss",
                $Vid,
                $return_date,
                $pickup_date
            );


            mysqli_stmt_execute(
                $availability_stmt
            );


            $availability_result =
                mysqli_stmt_get_result(
                    $availability_stmt
                );


            $existing_booking =
                mysqli_fetch_assoc(
                    $availability_result
                );


            mysqli_stmt_close(
                $availability_stmt
            );


            /* =========================================
               VEHICLE ALREADY BOOKED
            ========================================= */

            if ($existing_booking) {

                $error =
                    "This vehicle is already booked for the selected dates.";
            }


            else {


                /* =====================================
                   INSERT BOOKING

                   Payment has not completed yet,
                   therefore status = Pending
                ===================================== */

                $insert_sql = "
                    INSERT INTO booking
                    (
                        Cid,
                        Vid,
                        pickup_location,
                        pickup_date,
                        return_date,
                        status
                    )

                    VALUES
                    (
                        ?,
                        ?,
                        ?,
                        ?,
                        ?,
                        'Pending'
                    )
                ";


                $insert_stmt =
                    mysqli_prepare(
                        $conn,
                        $insert_sql
                    );


                if (!$insert_stmt) {

                    die(
                        "Booking Prepare Error: " .
                        mysqli_error($conn)
                    );
                }


                mysqli_stmt_bind_param(
                    $insert_stmt,
                    "iisss",
                    $Cid,
                    $Vid,
                    $pickup_location,
                    $pickup_date,
                    $return_date
                );


                if (
                    !mysqli_stmt_execute(
                        $insert_stmt
                    )
                ) {

                    die(
                        "Booking Insert Error: " .
                        mysqli_stmt_error(
                            $insert_stmt
                        )
                    );
                }


                /* =====================================
                   GET GENERATED BOOKING ID
                ===================================== */

                $Bid =
                    mysqli_insert_id($conn);


                mysqli_stmt_close(
                    $insert_stmt
                );


                if ($Bid <= 0) {

                    die(
                        "Booking could not be created."
                    );
                }


                /* =====================================
                   STORE PAYMENT INFO TEMPORARILY
                ===================================== */

                $_SESSION['pending_payment'] = [

                    "Bid" => $Bid,

                    "Cid" => $Cid,

                    "Vid" => $Vid,

                    "amount" =>
                        $total_amount,

                    "payment_method" =>
                        "eSewa"
                ];


                /* =====================================
                   GO TO ESEWA PAYMENT
                ===================================== */

                header(
                    "Location: esewa_payment.php?booking_id="
                    . $Bid
                );

                exit();
            }
        }
    }
}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Book Vehicle
    </title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #f4f6f8;

            color: #333;
        }


        /* =========================================
           BOOKING CONTAINER
        ========================================= */

        .booking-container {

            width: 500px;

            max-width: 92%;

            margin: 40px auto;

            background: white;

            padding: 25px;

            border-radius: 12px;

            box-shadow:
                0 4px 15px
                rgba(0, 0, 0, 0.12);
        }


        .booking-container h2 {

            margin-top: 0;

            margin-bottom: 20px;

            text-align: center;
        }


        /* =========================================
           VEHICLE IMAGE
        ========================================= */

        .vehicle-image {

            width: 100%;

            height: 230px;

            object-fit: cover;

            border-radius: 10px;

            margin-bottom: 15px;
        }


        /* =========================================
           VEHICLE INFORMATION
        ========================================= */

        .vehicle-name {

            margin-bottom: 15px;

            font-size: 22px;
        }


        .vehicle-info {

            margin: 8px 0;

            color: #555;

            font-size: 14px;
        }


        /* =========================================
           ERROR
        ========================================= */

        .error-message {

            margin: 15px 0;

            padding: 12px;

            background: #fee2e2;

            color: #991b1b;

            border-radius: 6px;

            font-size: 14px;
        }


        /* =========================================
           FORM
        ========================================= */

        .form-group {

            margin-top: 17px;
        }


        .form-group label {

            display: block;

            margin-bottom: 7px;

            font-size: 14px;

            font-weight: bold;
        }


        .form-group input[type="text"],
        .form-group input[type="date"] {

            width: 100%;

            padding: 11px;

            border: 1px solid #ccc;

            border-radius: 6px;

            outline: none;

            font-size: 14px;
        }


        .form-group input:focus {

            border-color: #60bb46;
        }


        /* =========================================
           TOTAL BOX
        ========================================= */

        .total-box {

            margin-top: 20px;

            padding: 15px;

            background: #f7f7f7;

            border: 1px solid #e5e5e5;

            border-radius: 7px;
        }


        .total-box p {

            margin: 7px 0;
        }


        #total_amount {

            color: #15803d;

            font-size: 18px;

            font-weight: bold;
        }


        /* =========================================
           PAYMENT
        ========================================= */

        .payment-section {

            margin-top: 20px;
        }


        .payment-title {

            display: block;

            margin-bottom: 8px;

            font-weight: bold;

            font-size: 14px;
        }


        .payment-option {

            display: flex;

            align-items: center;

            gap: 12px;

            padding: 15px;

            border: 2px solid #e5e7eb;

            border-radius: 8px;

            cursor: pointer;

            background: white;
        }


        .payment-option:hover {

            border-color: #60bb46;

            background: #f8fff6;
        }


        .payment-option input {

            cursor: pointer;
        }


        .esewa {

            color: #60bb46;

            font-size: 18px;

            font-weight: bold;
        }


        /* =========================================
           BUTTON
        ========================================= */

        .book-btn {

            width: 100%;

            margin-top: 22px;

            padding: 13px;

            border: none;

            border-radius: 7px;

            background: #60bb46;

            color: white;

            font-size: 16px;

            font-weight: bold;

            cursor: pointer;
        }


        .book-btn:hover {

            background: #4cae36;
        }


        .payment-note {

            text-align: center;

            margin-top: 12px;

            font-size: 12px;

            color: #777;
        }


        /* =========================================
           RESPONSIVE
        ========================================= */

        @media(max-width: 550px) {

            .booking-container {

                margin: 20px auto;

                padding: 18px;
            }


            .vehicle-image {

                height: 190px;
            }
        }

    </style>

</head>


<body>


<div class="booking-container">


    <h2>
        Book Vehicle
    </h2>


    <!-- =====================================
         VEHICLE IMAGE
    ====================================== -->

    <?php if (!empty($vehicle['image'])) { ?>

        <img
            src="uploads/vehicles/<?php
            echo htmlspecialchars(
                $vehicle['image']
            );
            ?>"
            class="vehicle-image"
            alt="Vehicle"
        >

    <?php } ?>


    <!-- =====================================
         VEHICLE DETAILS
    ====================================== -->

    <h3 class="vehicle-name">

        <?php
        echo htmlspecialchars(
            $vehicle['name']
        );
        ?>

    </h3>


    <?php if (isset($vehicle['brand'])) { ?>

        <p class="vehicle-info">

            <strong>Brand:</strong>

            <?php
            echo htmlspecialchars(
                $vehicle['brand']
            );
            ?>

        </p>

    <?php } ?>


    <?php if (isset($vehicle['model'])) { ?>

        <p class="vehicle-info">

            <strong>Model:</strong>

            <?php
            echo htmlspecialchars(
                $vehicle['model']
            );
            ?>

        </p>

    <?php } ?>


    <?php if (isset($vehicle['Vno'])) { ?>

        <p class="vehicle-info">

            <strong>Vehicle Number:</strong>

            <?php
            echo htmlspecialchars(
                $vehicle['Vno']
            );
            ?>

        </p>

    <?php } ?>


    <p class="vehicle-info">

        <strong>Price Per Day:</strong>

        Rs.

        <?php
        echo number_format(
            (float)
            $vehicle['price_per_day'],
            2
        );
        ?>

    </p>


    <!-- =====================================
         ERROR MESSAGE
    ====================================== -->

    <?php if (!empty($error)) { ?>

        <div class="error-message">

            <?php
            echo htmlspecialchars($error);
            ?>

        </div>

    <?php } ?>


    <!-- =====================================
         BOOKING FORM
    ====================================== -->

    <form
        method="POST"
        id="bookingForm"
        onsubmit="return validateBooking();"
    >


        <!-- PICKUP LOCATION -->

        <div class="form-group">

            <label for="pickup_location">

                Pickup Location

            </label>


            <input
                type="text"
                id="pickup_location"
                name="pickup_location"
                placeholder="Enter pickup location"
                value="<?php
                echo htmlspecialchars(
                    $_POST['pickup_location'] ?? ''
                );
                ?>"
                required
            >

        </div>


        <!-- PICKUP DATE -->

        <div class="form-group">

            <label for="pickup_date">

                Pickup Date

            </label>


            <input
                type="date"
                id="pickup_date"
                name="pickup_date"
                value="<?php
                echo htmlspecialchars(
                    $_POST['pickup_date'] ?? ''
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
                id="return_date"
                name="return_date"
                value="<?php
                echo htmlspecialchars(
                    $_POST['return_date'] ?? ''
                );
                ?>"
                required
            >

        </div>


        <!-- =====================================
             TOTAL
        ====================================== -->

        <div class="total-box">


            <p>

                <strong>Total Days:</strong>

                <span id="total_days">
                    0
                </span>

            </p>


            <p>

                <strong>Total Amount:</strong>

                Rs.

                <span id="total_amount">
                    0.00
                </span>

            </p>


        </div>


        <!-- =====================================
             PAYMENT METHOD
        ====================================== -->

        <div class="payment-section">


            <span class="payment-title">

                Payment Method

            </span>


            <label
                class="payment-option"
                for="esewa"
            >


                <input
                    type="radio"
                    id="esewa"
                    name="payment_method"
                    value="esewa"
                    required
                    <?php
                    if (
                        ($_POST['payment_method'] ?? '')
                        === 'esewa'
                    ) {
                        echo "checked";
                    }
                    ?>
                >


                <span>

                    Pay with

                    <span class="esewa">
                        eSewa
                    </span>

                </span>


            </label>


        </div>


        <!-- =====================================
             SUBMIT
        ====================================== -->

        <button
            type="submit"
            class="book-btn"
        >

            Pay & Book Vehicle

        </button>


        <p class="payment-note">

            Booking will remain Pending until
            your eSewa payment is successfully verified.

        </p>


    </form>


</div>


<script>


/* =====================================================
   VEHICLE PRICE
===================================================== */

const pricePerDay =
    <?php
    echo (float)
        $vehicle['price_per_day'];
    ?>;


/* =====================================================
   ELEMENTS
===================================================== */

const pickupDate =
    document.getElementById(
        "pickup_date"
    );

const returnDate =
    document.getElementById(
        "return_date"
    );

const pickupLocation =
    document.getElementById(
        "pickup_location"
    );

const totalDays =
    document.getElementById(
        "total_days"
    );

const totalAmount =
    document.getElementById(
        "total_amount"
    );

const esewa =
    document.getElementById(
        "esewa"
    );


/* =====================================================
   GET TODAY IN YYYY-MM-DD
===================================================== */

function getLocalDateString(date) {

    const year =
        date.getFullYear();

    const month =
        String(
            date.getMonth() + 1
        ).padStart(2, "0");

    const day =
        String(
            date.getDate()
        ).padStart(2, "0");


    return `${year}-${month}-${day}`;
}


const today =
    new Date();

pickupDate.min =
    getLocalDateString(today);


/* =====================================================
   CALCULATE TOTAL
===================================================== */

function calculateTotal() {


    if (
        !pickupDate.value ||
        !returnDate.value
    ) {

        totalDays.textContent = "0";

        totalAmount.textContent =
            "0.00";

        return;
    }


    const start =
        new Date(
            pickupDate.value +
            "T00:00:00"
        );


    const end =
        new Date(
            returnDate.value +
            "T00:00:00"
        );


    const difference =
        end.getTime() -
        start.getTime();


    const days =
        difference /
        (1000 * 60 * 60 * 24);


    if (days > 0) {


        totalDays.textContent =
            days;


        totalAmount.textContent =
            (
                days *
                pricePerDay
            ).toFixed(2);


    } else {


        totalDays.textContent =
            "0";

        totalAmount.textContent =
            "0.00";
    }
}


/* =====================================================
   PICKUP DATE CHANGE
===================================================== */

pickupDate.addEventListener(
    "change",
    function () {


        if (pickupDate.value) {


            const selectedPickup =
                new Date(
                    pickupDate.value +
                    "T00:00:00"
                );


            selectedPickup.setDate(
                selectedPickup.getDate()
                + 1
            );


            returnDate.min =
                getLocalDateString(
                    selectedPickup
                );


            if (
                returnDate.value &&
                returnDate.value <=
                pickupDate.value
            ) {

                returnDate.value = "";
            }
        }


        calculateTotal();
    }
);


/* =====================================================
   RETURN DATE CHANGE
===================================================== */

returnDate.addEventListener(
    "change",
    calculateTotal
);


/* =====================================================
   FORM VALIDATION
===================================================== */

function validateBooking() {


    if (
        pickupLocation.value.trim()
        === ""
    ) {

        alert(
            "Please enter pickup location."
        );

        pickupLocation.focus();

        return false;
    }


    if (
        !pickupDate.value ||
        !returnDate.value
    ) {

        alert(
            "Please select pickup and return date."
        );

        return false;
    }


    const start =
        new Date(
            pickupDate.value +
            "T00:00:00"
        );


    const end =
        new Date(
            returnDate.value +
            "T00:00:00"
        );


    const current =
        new Date();


    current.setHours(
        0,
        0,
        0,
        0
    );


    if (start < current) {

        alert(
            "Pickup date cannot be in the past."
        );

        return false;
    }


    if (end <= start) {

        alert(
            "Return date must be after pickup date."
        );

        return false;
    }


    if (!esewa.checked) {

        alert(
            "Please select eSewa payment."
        );

        return false;
    }


    return true;
}


/* =====================================================
   CALCULATE IF VALUES EXIST AFTER VALIDATION ERROR
===================================================== */

calculateTotal();


</script>


</body>

</html>