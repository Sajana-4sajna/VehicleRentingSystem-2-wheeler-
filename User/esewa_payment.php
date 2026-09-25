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
   CHECK BOOKING ID
===================================================== */

if (
    !isset($_GET['booking_id']) ||
    !is_numeric($_GET['booking_id'])
) {

    die("Invalid booking ID.");
}

$Bid = (int) $_GET['booking_id'];


/* =====================================================
   GET BOOKING + VEHICLE INFORMATION

   IMPORTANT:
   Booking is already stored in database
   with status = Pending.
===================================================== */

$sql = "SELECT

            b.Bid,
            b.Cid,
            b.Vid,
            b.pickup_location,
            b.pickup_date,
            b.return_date,
            b.status,

            v.name AS vehicle_name,
            v.price_per_day

        FROM booking b

        INNER JOIN vehicle v
        ON b.Vid = v.Vid

        WHERE b.Bid = ?
        AND b.Cid = ?

        LIMIT 1";


$stmt = mysqli_prepare($conn, $sql);


if (!$stmt) {

    die(
        "Database Error: " .
        mysqli_error($conn)
    );
}


mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $Bid,
    $Cid
);


mysqli_stmt_execute($stmt);


$result =
    mysqli_stmt_get_result($stmt);


$booking =
    mysqli_fetch_assoc($result);


mysqli_stmt_close($stmt);


/* =====================================================
   CHECK BOOKING EXISTS
===================================================== */

if (!$booking) {

    die(
        "Booking not found or this booking does not belong to you."
    );
}


/* =====================================================
   CHECK BOOKING STATUS
===================================================== */

if ($booking['status'] !== 'Pending') {

    if ($booking['status'] === 'Approved') {

        die(
            "This booking has already been paid and approved."
        );

    } else {

        die(
            "Payment cannot be made for a booking with status: "
            . htmlspecialchars($booking['status'])
        );
    }
}


/* =====================================================
   CALCULATE TOTAL DAYS
===================================================== */

$start =
    strtotime(
        $booking['pickup_date']
    );


$end =
    strtotime(
        $booking['return_date']
    );


if (
    $start === false ||
    $end === false
) {

    die("Invalid booking dates.");
}


$total_days =
    ($end - $start)
    /
    (60 * 60 * 24);


if ($total_days <= 0) {

    die(
        "Return date must be after pickup date."
    );
}


/* =====================================================
   GET CURRENT VEHICLE PRICE
===================================================== */

$price_per_day =
    (float)
    $booking['price_per_day'];


if ($price_per_day <= 0) {

    die(
        "Invalid vehicle price."
    );
}


/* =====================================================
   CALCULATE RENTAL AMOUNT
===================================================== */

$rental_amount =
    $total_days *
    $price_per_day;


/* =====================================================
   ESEWA CHARGES

   We are keeping all additional charges = 0.

   Therefore:
   final payment = actual rental amount
===================================================== */

$tax_amount = 0;

$product_service_charge = 0;

$product_delivery_charge = 0;


/* =====================================================
   FINAL PAYMENT AMOUNT
===================================================== */

$total_amount =
    $rental_amount
    +
    $tax_amount
    +
    $product_service_charge
    +
    $product_delivery_charge;


/* =====================================================
   FORMAT AMOUNTS FOR ESEWA
===================================================== */

$amount =
    number_format(
        $rental_amount,
        2,
        '.',
        ''
    );


$tax_amount =
    number_format(
        $tax_amount,
        2,
        '.',
        ''
    );


$product_service_charge =
    number_format(
        $product_service_charge,
        2,
        '.',
        ''
    );


$product_delivery_charge =
    number_format(
        $product_delivery_charge,
        2,
        '.',
        ''
    );


$total_amount =
    number_format(
        $total_amount,
        2,
        '.',
        ''
    );


/* =====================================================
   ESEWA TEST PRODUCT CODE
===================================================== */

$product_code =
    "EPAYTEST";


/* =====================================================
   CREATE TRANSACTION UUID

   VERY IMPORTANT:

   esewa_success.php expects:

   BOOK-Bid-time

   Example:

   BOOK-25-1790350000
===================================================== */

$transaction_uuid =
    "BOOK-" .
    $Bid .
    "-" .
    time();


/* =====================================================
   STORE TRANSACTION INFORMATION IN SESSION
===================================================== */

$_SESSION['pending_payment'] = [

    "Bid" =>
        $Bid,

    "Cid" =>
        $Cid,

    "Vid" =>
        (int) $booking['Vid'],

    "amount" =>
        (float) $total_amount,

    "payment_method" =>
        "eSewa",

    "transaction_uuid" =>
        $transaction_uuid

];


$_SESSION['esewa_transaction_uuid'] =
    $transaction_uuid;


$_SESSION['esewa_total_amount'] =
    $total_amount;


/* =====================================================
   SIGNED FIELD NAMES
===================================================== */

$signed_field_names =
    "total_amount,transaction_uuid,product_code";


/* =====================================================
   CREATE SIGNATURE MESSAGE
===================================================== */

$message =
    "total_amount=" .
    $total_amount .
    ",transaction_uuid=" .
    $transaction_uuid .
    ",product_code=" .
    $product_code;


/* =====================================================
   ESEWA UAT SECRET KEY
===================================================== */

$secret_key =
    "8gBm/:&EnhH.1/q";


/* =====================================================
   GENERATE SIGNATURE
===================================================== */

$hash =
    hash_hmac(
        "sha256",
        $message,
        $secret_key,
        true
    );


$signature =
    base64_encode($hash);


/* =====================================================
   SUCCESS / FAILURE URLs

   booking_id MUST be sent to success page.
===================================================== */

$success_url =
    "http://localhost/vehicle_renting_system/User/esewa_success.php";


$failure_url =
    "http://localhost/vehicle_renting_system/User/esewa_failure.php?booking_id="
    . $Bid;

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
        Redirecting to eSewa
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

            background: #f5f5f5;

            display: flex;

            justify-content: center;

            align-items: center;

            min-height: 100vh;
        }


        .payment-box {

            width: 430px;

            max-width: 92%;

            background: white;

            padding: 30px;

            border-radius: 10px;

            text-align: center;

            box-shadow:
                0 4px 15px
                rgba(0, 0, 0, 0.1);
        }


        .payment-box h2 {

            color: #60bb46;

            margin-top: 0;

            margin-bottom: 20px;
        }


        .details {

            background: #f8f9fa;

            padding: 15px;

            border-radius: 8px;

            text-align: left;

            margin-bottom: 20px;
        }


        .details p {

            margin: 10px 0;

            color: #444;
        }


        .total {

            color: #60bb46;

            font-size: 18px;

            font-weight: bold;
        }


        .loader {

            width: 38px;

            height: 38px;

            margin: 25px auto;

            border: 4px solid #ddd;

            border-top-color: #60bb46;

            border-radius: 50%;

            animation:
                spin 0.8s linear infinite;
        }


        @keyframes spin {

            100% {

                transform:
                    rotate(360deg);
            }
        }


        .redirect-text {

            color: #666;

            font-size: 14px;
        }

    </style>

</head>


<body>


<div class="payment-box">


    <h2>
        eSewa Payment
    </h2>


    <div class="details">


        <p>

            <strong>
                Booking ID:
            </strong>

            #<?php
            echo htmlspecialchars(
                $Bid
            );
            ?>

        </p>


        <p>

            <strong>
                Vehicle:
            </strong>

            <?php

            echo htmlspecialchars(
                $booking[
                    'vehicle_name'
                ]
            );

            ?>

        </p>


        <p>

            <strong>
                Pickup Location:
            </strong>

            <?php

            echo htmlspecialchars(
                $booking[
                    'pickup_location'
                ]
            );

            ?>

        </p>


        <p>

            <strong>
                Pickup Date:
            </strong>

            <?php

            echo htmlspecialchars(
                $booking[
                    'pickup_date'
                ]
            );

            ?>

        </p>


        <p>

            <strong>
                Return Date:
            </strong>

            <?php

            echo htmlspecialchars(
                $booking[
                    'return_date'
                ]
            );

            ?>

        </p>


        <p>

            <strong>
                Total Days:
            </strong>

            <?php
            echo $total_days;
            ?>

        </p>


        <p>

            <strong>
                Price Per Day:
            </strong>

            Rs.

            <?php

            echo number_format(
                $price_per_day,
                2
            );

            ?>

        </p>


        <p>

            <strong>
                Rental Amount:
            </strong>

            Rs.

            <?php
            echo htmlspecialchars(
                $amount
            );
            ?>

        </p>


        <p class="total">

            Total Payment:

            Rs.

            <?php
            echo htmlspecialchars(
                $total_amount
            );
            ?>

        </p>


    </div>


    <div class="loader"></div>


    <p class="redirect-text">
        Redirecting to eSewa...
    </p>


</div>


<!-- =====================================================
     ESEWA PAYMENT FORM
===================================================== -->

<form
    id="esewaForm"
    action="https://rc-epay.esewa.com.np/api/epay/main/v2/form"
    method="POST"
>


    <!-- RENTAL AMOUNT -->

    <input
        type="hidden"
        name="amount"
        value="<?php
        echo htmlspecialchars(
            $amount
        );
        ?>"
    >


    <!-- TAX -->

    <input
        type="hidden"
        name="tax_amount"
        value="<?php
        echo htmlspecialchars(
            $tax_amount
        );
        ?>"
    >


    <!-- TOTAL -->

    <input
        type="hidden"
        name="total_amount"
        value="<?php
        echo htmlspecialchars(
            $total_amount
        );
        ?>"
    >


    <!-- TRANSACTION UUID -->

    <input
        type="hidden"
        name="transaction_uuid"
        value="<?php
        echo htmlspecialchars(
            $transaction_uuid
        );
        ?>"
    >


    <!-- PRODUCT CODE -->

    <input
        type="hidden"
        name="product_code"
        value="<?php
        echo htmlspecialchars(
            $product_code
        );
        ?>"
    >


    <!-- SERVICE CHARGE -->

    <input
        type="hidden"
        name="product_service_charge"
        value="<?php
        echo htmlspecialchars(
            $product_service_charge
        );
        ?>"
    >


    <!-- DELIVERY CHARGE -->

    <input
        type="hidden"
        name="product_delivery_charge"
        value="<?php
        echo htmlspecialchars(
            $product_delivery_charge
        );
        ?>"
    >


    <!-- SUCCESS URL -->

    <input
        type="hidden"
        name="success_url"
        value="<?php
        echo htmlspecialchars(
            $success_url
        );
        ?>"
    >


    <!-- FAILURE URL -->

    <input
        type="hidden"
        name="failure_url"
        value="<?php
        echo htmlspecialchars(
            $failure_url
        );
        ?>"
    >


    <!-- SIGNED FIELD NAMES -->

    <input
        type="hidden"
        name="signed_field_names"
        value="<?php
        echo htmlspecialchars(
            $signed_field_names
        );
        ?>"
    >


    <!-- SIGNATURE -->

    <input
        type="hidden"
        name="signature"
        value="<?php
        echo htmlspecialchars(
            $signature
        );
        ?>"
    >


</form>


<script>

    /*
       Automatically redirect customer
       to eSewa.
    */

    document
        .getElementById(
            "esewaForm"
        )
        .submit();

</script>


</body>

</html>