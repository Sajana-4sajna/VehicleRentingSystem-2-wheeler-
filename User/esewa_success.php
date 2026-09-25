<?php
session_start();
include("db.php");


/* =====================================================
   1. CHECK CUSTOMER LOGIN
===================================================== */

if (!isset($_SESSION['Cid'])) {

    header("Location: login.php");
    exit();
}

$Cid = (int) $_SESSION['Cid'];


/* =====================================================
   2. GET ESEWA RESPONSE

   IMPORTANT:
   We DO NOT depend on $_GET['booking_id'].

   eSewa sends:
   esewa_success.php?data=BASE64_DATA
===================================================== */

$encoded_response = $_GET['data'] ?? '';

if (empty($encoded_response)) {

    die("No payment response received from eSewa.");
}


/* =====================================================
   3. BASE64 DECODE RESPONSE
===================================================== */

$decoded_response = base64_decode(
    $encoded_response,
    true
);

if ($decoded_response === false) {

    die("Invalid payment response received from eSewa.");
}


/* =====================================================
   4. CONVERT JSON INTO PHP ARRAY
===================================================== */

$response = json_decode(
    $decoded_response,
    true
);

if (!is_array($response)) {

    die("Unable to read eSewa payment response.");
}


/* =====================================================
   5. GET VALUES RETURNED BY ESEWA
===================================================== */

$status =
    $response['status'] ?? '';

$transaction_uuid =
    $response['transaction_uuid'] ?? '';

$transaction_code =
    $response['transaction_code'] ?? '';

$response_total_amount =
    $response['total_amount'] ?? '';

$product_code =
    $response['product_code'] ?? '';

$received_signature =
    $response['signature'] ?? '';

$signed_field_names =
    $response['signed_field_names'] ?? '';


/* =====================================================
   6. GET BOOKING ID FROM TRANSACTION UUID

   We created UUID like:

   BOOK-14-1790350000

   BOOK = prefix
   14   = Booking ID
   last = timestamp
===================================================== */

if (empty($transaction_uuid)) {

    die("Transaction UUID missing from eSewa response.");
}


$uuid_parts = explode(
    "-",
    $transaction_uuid
);


if (
    count($uuid_parts) < 3 ||
    $uuid_parts[0] !== "BOOK" ||
    !is_numeric($uuid_parts[1])
) {

    die(
        "Invalid transaction UUID received from eSewa: "
        . htmlspecialchars($transaction_uuid)
    );
}


$Bid = (int) $uuid_parts[1];


if ($Bid <= 0) {

    die("Invalid booking ID extracted from transaction.");
}


/* =====================================================
   7. GET BOOKING + VEHICLE
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


$stmt = mysqli_prepare(
    $conn,
    $sql
);


if (!$stmt) {

    die(
        "Booking query error: "
        . mysqli_error($conn)
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


if (!$booking) {

    die(
        "Booking #" .
        htmlspecialchars($Bid) .
        " was not found for this customer."
    );
}


/* =====================================================
   8. CALCULATE EXPECTED BOOKING AMOUNT
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

    die("Invalid booking duration.");
}


$price_per_day =
    (float)
    $booking['price_per_day'];


$booking_amount =
    $total_days *
    $price_per_day;


if ($booking_amount <= 0) {

    die("Invalid booking amount.");
}


/* =====================================================
   9. DEFAULT PAYMENT VARIABLES
===================================================== */

$payment_verified = false;

$error_message = "";

$paid_amount = 0;


/* =====================================================
   10. BASIC ESEWA VALIDATION
===================================================== */

if ($status !== "COMPLETE") {

    $error_message =
        "Payment status returned by eSewa is: "
        . $status;

}

elseif ($product_code !== "EPAYTEST") {

    $error_message =
        "Invalid eSewa product code.";

}

elseif (empty($transaction_code)) {

    $error_message =
        "Transaction code was not received from eSewa.";

}

elseif (empty($received_signature)) {

    $error_message =
        "Payment signature was not received.";

}

elseif (empty($signed_field_names)) {

    $error_message =
        "Signed field information was not received.";

}

else {


    /* =================================================
       11. VERIFY SESSION UUID

       If session exists, it must match.

       We do NOT fail simply because the session value
       is missing.
    ================================================= */

    if (
        isset($_SESSION['esewa_transaction_uuid']) &&
        $_SESSION['esewa_transaction_uuid']
            !== $transaction_uuid
    ) {

        $error_message =
            "Transaction UUID does not match the payment request.";

    }

    else {


        /* =================================================
           12. BUILD SIGNATURE MESSAGE FROM
               ESEWA'S signed_field_names

           Example eSewa may return:

           transaction_code,status,total_amount,
           transaction_uuid,product_code,
           signed_field_names
        ================================================= */

        $fields =
            explode(
                ",",
                $signed_field_names
            );


        $message_parts = [];

        $valid_fields = true;


        foreach ($fields as $field) {


            $field = trim($field);


            if (
                $field === '' ||
                !array_key_exists(
                    $field,
                    $response
                )
            ) {

                $valid_fields = false;

                break;
            }


            $message_parts[] =
                $field
                . "="
                . $response[$field];
        }


        if (!$valid_fields) {

            $error_message =
                "Invalid signed fields received from eSewa.";

        }

        else {


            $signature_message =
                implode(
                    ",",
                    $message_parts
                );


            /* =================================================
               13. ESEWA UAT SECRET KEY
            ================================================= */

            $secret_key =
                "8gBm/:&EnhH.1/q";


            /* =================================================
               14. GENERATE SIGNATURE
            ================================================= */

            $generated_signature =
                base64_encode(
                    hash_hmac(
                        "sha256",
                        $signature_message,
                        $secret_key,
                        true
                    )
                );


            /* =================================================
               15. VERIFY ESEWA SIGNATURE
            ================================================= */

            if (
                !hash_equals(
                    $generated_signature,
                    $received_signature
                )
            ) {

                $error_message =
                    "eSewa payment signature verification failed.";

            }

            else {


                /* =================================================
                   16. GET PAID AMOUNT

                   eSewa can return:
                   1000
                   1000.00
                   1,000.00

                   Remove comma before float conversion.
                ================================================= */

                $paid_amount =
                    (float)
                    str_replace(
                        ",",
                        "",
                        (string)
                        $response_total_amount
                    );


                if ($paid_amount <= 0) {

                    $error_message =
                        "Invalid payment amount received.";

                }

                elseif (
                    abs(
                        $paid_amount -
                        $booking_amount
                    ) > 0.01
                ) {

                    $error_message =
                        "Paid amount does not match booking amount."
                        . " Expected: Rs. "
                        . number_format(
                            $booking_amount,
                            2
                        )
                        . " | Received: Rs. "
                        . number_format(
                            $paid_amount,
                            2
                        );

                }

                else {


                    /* =============================================
                       EVERYTHING VERIFIED
                    ============================================= */

                    $payment_verified = true;

                }
            }
        }
    }
}


/* =====================================================
   17. SAVE VERIFIED PAYMENT
===================================================== */

if ($payment_verified) {


    mysqli_begin_transaction($conn);


    try {


        /* =================================================
           18. CHECK TRANSACTION UUID DUPLICATE
        ================================================= */

        $check_sql = "
            SELECT
                Pid,
                Bid,
                status

            FROM payment

            WHERE transaction_uuid = ?

            LIMIT 1
        ";


        $check_stmt =
            mysqli_prepare(
                $conn,
                $check_sql
            );


        if (!$check_stmt) {

            throw new Exception(
                "Payment check error: "
                . mysqli_error($conn)
            );
        }


        mysqli_stmt_bind_param(
            $check_stmt,
            "s",
            $transaction_uuid
        );


        mysqli_stmt_execute(
            $check_stmt
        );


        $check_result =
            mysqli_stmt_get_result(
                $check_stmt
            );


        $existing_transaction =
            mysqli_fetch_assoc(
                $check_result
            );


        mysqli_stmt_close(
            $check_stmt
        );


        /* =================================================
           19. SECURITY CHECK

           Same transaction cannot belong
           to another booking.
        ================================================= */

        if (
            $existing_transaction &&
            (int)
            $existing_transaction['Bid']
            !== $Bid
        ) {

            throw new Exception(
                "This eSewa transaction is already linked "
                . "to another booking."
            );
        }


        /* =================================================
           20. CHECK WHETHER THIS BOOKING
               ALREADY HAS PAYMENT
        ================================================= */

        $booking_payment_sql = "
            SELECT
                Pid,
                Bid,
                status

            FROM payment

            WHERE Bid = ?

            LIMIT 1
        ";


        $booking_payment_stmt =
            mysqli_prepare(
                $conn,
                $booking_payment_sql
            );


        if (!$booking_payment_stmt) {

            throw new Exception(
                "Booking payment check error: "
                . mysqli_error($conn)
            );
        }


        mysqli_stmt_bind_param(
            $booking_payment_stmt,
            "i",
            $Bid
        );


        mysqli_stmt_execute(
            $booking_payment_stmt
        );


        $booking_payment_result =
            mysqli_stmt_get_result(
                $booking_payment_stmt
            );


        $existing_booking_payment =
            mysqli_fetch_assoc(
                $booking_payment_result
            );


        mysqli_stmt_close(
            $booking_payment_stmt
        );


        /* =================================================
           21. PAYMENT VALUES
        ================================================= */

        $payment_status =
            "Paid";


        $payment_method =
            "eSewa";


        /* =================================================
           22. INSERT NEW PAYMENT
        ================================================= */

        if (!$existing_booking_payment) {


            $payment_sql = "
                INSERT INTO payment
                (
                    amount,
                    status,
                    payment_date,
                    Bid,
                    payment_method,
                    transaction_uuid,
                    transaction_code
                )

                VALUES
                (
                    ?,
                    ?,
                    NOW(),
                    ?,
                    ?,
                    ?,
                    ?
                )
            ";


            $payment_stmt =
                mysqli_prepare(
                    $conn,
                    $payment_sql
                );


            if (!$payment_stmt) {

                throw new Exception(
                    "Payment prepare error: "
                    . mysqli_error($conn)
                );
            }


            mysqli_stmt_bind_param(
                $payment_stmt,
                "dsisss",
                $paid_amount,
                $payment_status,
                $Bid,
                $payment_method,
                $transaction_uuid,
                $transaction_code
            );


            if (
                !mysqli_stmt_execute(
                    $payment_stmt
                )
            ) {

                throw new Exception(
                    "Payment insert error: "
                    . mysqli_stmt_error(
                        $payment_stmt
                    )
                );
            }


            mysqli_stmt_close(
                $payment_stmt
            );

        }

        /* =================================================
           23. UPDATE EXISTING PAYMENT
        ================================================= */

        else {


            $update_payment_sql = "
                UPDATE payment

                SET
                    amount = ?,
                    status = 'Paid',
                    payment_date = NOW(),
                    payment_method = 'eSewa',
                    transaction_uuid = ?,
                    transaction_code = ?

                WHERE Bid = ?
            ";


            $update_payment_stmt =
                mysqli_prepare(
                    $conn,
                    $update_payment_sql
                );


            if (!$update_payment_stmt) {

                throw new Exception(
                    "Payment update prepare error: "
                    . mysqli_error($conn)
                );
            }


            mysqli_stmt_bind_param(
                $update_payment_stmt,
                "dssi",
                $paid_amount,
                $transaction_uuid,
                $transaction_code,
                $Bid
            );


            if (
                !mysqli_stmt_execute(
                    $update_payment_stmt
                )
            ) {

                throw new Exception(
                    "Payment update error: "
                    . mysqli_stmt_error(
                        $update_payment_stmt
                    )
                );
            }


            mysqli_stmt_close(
                $update_payment_stmt
            );

        }


        /* =================================================
           24. UPDATE BOOKING STATUS
        ================================================= */

        $booking_update_sql = "
            UPDATE booking

            SET status = 'Approved'

            WHERE Bid = ?
            AND Cid = ?
        ";


        $booking_update_stmt =
            mysqli_prepare(
                $conn,
                $booking_update_sql
            );


        if (!$booking_update_stmt) {

            throw new Exception(
                "Booking update prepare error: "
                . mysqli_error($conn)
            );
        }


        mysqli_stmt_bind_param(
            $booking_update_stmt,
            "ii",
            $Bid,
            $Cid
        );


        if (
            !mysqli_stmt_execute(
                $booking_update_stmt
            )
        ) {

            throw new Exception(
                "Booking update error: "
                . mysqli_stmt_error(
                    $booking_update_stmt
                )
            );
        }


        mysqli_stmt_close(
            $booking_update_stmt
        );


        /* =================================================
           25. COMMIT DATABASE CHANGES
        ================================================= */

        mysqli_commit($conn);


        /* =================================================
           26. CLEAR TEMP PAYMENT SESSION
        ================================================= */

        unset(
            $_SESSION['pending_payment']
        );


        unset(
            $_SESSION['esewa_transaction_uuid']
        );


        unset(
            $_SESSION['esewa_total_amount']
        );


    }

    catch (Throwable $e) {


        mysqli_rollback($conn);


        $payment_verified = false;


        $error_message =
            $e->getMessage();

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
        <?php

        echo $payment_verified
            ? "Payment Successful"
            : "Payment Verification Failed";

        ?>
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

            background: #f4f7fb;

            display: flex;

            justify-content: center;

            align-items: center;

            min-height: 100vh;
        }


        .payment-box {

            width: 480px;

            max-width: 92%;

            background: white;

            padding: 40px;

            border-radius: 12px;

            text-align: center;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.10);
        }


        .success {

            color: #15803d;
        }


        .failed {

            color: #dc2626;
        }


        .payment-box p {

            color: #555;

            line-height: 1.6;
        }


        .booking-id {

            background: #f1f5f9;

            padding: 12px;

            border-radius: 6px;

            margin: 20px 0;
        }


        .payment-details {

            background: #f8fafc;

            padding: 18px;

            margin: 20px 0;

            border-radius: 8px;

            text-align: left;
        }


        .payment-details p {

            margin: 9px 0;
        }


        .error-box {

            background: #fee2e2;

            color: #991b1b;

            padding: 14px;

            border-radius: 6px;

            margin-top: 15px;

            word-break: break-word;
        }


        .back-btn {

            display: inline-block;

            margin-top: 15px;

            padding: 11px 22px;

            background: #2563eb;

            color: white;

            text-decoration: none;

            border-radius: 6px;
        }


        .back-btn:hover {

            background: #1d4ed8;
        }

    </style>

</head>


<body>


<div class="payment-box">


<?php if ($payment_verified) { ?>


    <!-- =================================================
         SUCCESS
    ================================================== -->

    <h2 class="success">

        Payment Successful

    </h2>


    <p>

        Your eSewa payment has been completed successfully.

    </p>


    <p>

        Your vehicle booking is now approved.

    </p>


    <div class="booking-id">

        Booking ID:

        <strong>

            #<?php

            echo htmlspecialchars(
                $Bid
            );

            ?>

        </strong>

    </div>


    <div class="payment-details">


        <p>

            <strong>
                Amount:
            </strong>

            Rs.

            <?php

            echo number_format(
                $paid_amount,
                2
            );

            ?>

        </p>


        <p>

            <strong>
                Payment Method:
            </strong>

            eSewa

        </p>


        <p>

            <strong>
                Payment Status:
            </strong>

            <span class="success">

                Paid

            </span>

        </p>


        <p>

            <strong>
                Booking Status:
            </strong>

            <span class="success">

                Approved

            </span>

        </p>


        <p>

            <strong>
                Transaction UUID:
            </strong>

            <?php

            echo htmlspecialchars(
                $transaction_uuid
            );

            ?>

        </p>


        <?php if (!empty($transaction_code)) { ?>


            <p>

                <strong>
                    Transaction Code:
                </strong>

                <?php

                echo htmlspecialchars(
                    $transaction_code
                );

                ?>

            </p>


        <?php } ?>


    </div>


<?php } else { ?>


    <!-- =================================================
         FAILED
    ================================================== -->

    <h2 class="failed">

        Payment Verification Failed

    </h2>


    <p>

        Your payment could not be verified.

    </p>


    <?php if (!empty($error_message)) { ?>


        <div class="error-box">

            <?php

            echo htmlspecialchars(
                $error_message
            );

            ?>

        </div>


    <?php } ?>


<?php } ?>


    <a
        href="my_bookings.php"
        class="back-btn"
    >

        Go to My Bookings

    </a>


</div>


</body>

</html> -->