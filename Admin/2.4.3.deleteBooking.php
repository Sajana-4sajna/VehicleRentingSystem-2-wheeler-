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

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {

    header("Location: 2.4.bookingSlidebar.php");
    exit();
}


$Bid = (int) $_GET['id'];


/* =====================================================
   CHECK BOOKING EXISTS
===================================================== */

$sql = "SELECT Bid FROM booking WHERE Bid = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $Bid
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


if (mysqli_num_rows($result) == 0) {

    mysqli_stmt_close($stmt);

    header(
        "Location: 2.4.bookingSlidebar.php?error=Booking not found"
    );

    exit();
}

mysqli_stmt_close($stmt);


/* =====================================================
   START TRANSACTION
===================================================== */

mysqli_begin_transaction($conn);


try {


    /* =================================================
       DELETE PAYMENT FIRST

       payment.Bid is connected with booking.Bid,
       so payment should be deleted first.
    ================================================= */

    $payment_sql =
        "DELETE FROM payment WHERE Bid = ?";


    $payment_stmt =
        mysqli_prepare(
            $conn,
            $payment_sql
        );


    if (!$payment_stmt) {

        throw new Exception(
            mysqli_error($conn)
        );
    }


    mysqli_stmt_bind_param(
        $payment_stmt,
        "i",
        $Bid
    );


    if (!mysqli_stmt_execute($payment_stmt)) {

        throw new Exception(
            mysqli_stmt_error($payment_stmt)
        );
    }


    mysqli_stmt_close($payment_stmt);



    /* =================================================
       DELETE BOOKING
    ================================================= */

    $booking_sql =
        "DELETE FROM booking WHERE Bid = ?";


    $booking_stmt =
        mysqli_prepare(
            $conn,
            $booking_sql
        );


    if (!$booking_stmt) {

        throw new Exception(
            mysqli_error($conn)
        );
    }


    mysqli_stmt_bind_param(
        $booking_stmt,
        "i",
        $Bid
    );


    if (!mysqli_stmt_execute($booking_stmt)) {

        throw new Exception(
            mysqli_stmt_error($booking_stmt)
        );
    }


    mysqli_stmt_close($booking_stmt);



    /* =================================================
       SAVE CHANGES
    ================================================= */

    mysqli_commit($conn);


    header(
        "Location: 2.4.bookingSlidebar.php?deleted=1"
    );

    exit();


} catch (Throwable $e) {


    /* =================================================
       IF ERROR, CANCEL ALL DELETE OPERATIONS
    ================================================= */

    mysqli_rollback($conn);


    die(
        "Booking could not be deleted: "
        . htmlspecialchars($e->getMessage())
    );
}
?>