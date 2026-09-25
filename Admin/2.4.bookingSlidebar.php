<?php

session_start();


/* =========================================
   DATABASE CONNECTION
========================================= */

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "vehicle_renting_system"
);

if (!$conn) {

    die(
        "Database connection failed: "
        . mysqli_connect_error()
    );
}


/* =========================================
   CHECK ADMIN LOGIN
========================================= */

if (!isset($_SESSION['Aid'])) {

    header("Location: 1.login.php");
    exit();
}


/* =========================================
   SEARCH
========================================= */

$search = "";

if (isset($_GET['search'])) {

    $search = trim($_GET['search']);
}


/* =========================================
   BOOKING STATUS FILTER
========================================= */

$status = "";

if (isset($_GET['status'])) {

    $status = trim($_GET['status']);
}


/* =========================================
   PAYMENT STATUS FILTER
========================================= */

$payment_status = "";

if (isset($_GET['payment_status'])) {

    $payment_status =
        trim($_GET['payment_status']);
}


/* =========================================
   MAIN BOOKING QUERY

   booking
      ↓
   customer
      ↓
   vehicle
      ↓
   payment
========================================= */

$sql = "SELECT

            b.Bid,
            b.Cid,
            b.Vid,
            b.pickup_location,
            b.pickup_date,
            b.return_date,
            b.status AS booking_status,
            b.created_at,

            c.C_name,
            c.phone,

            v.name AS vehicle_name,
            v.Vno,
            v.price_per_day,

            p.Pid,
            p.amount AS paid_amount,
            p.status AS payment_status,
            p.payment_method,
            p.transaction_uuid,
            p.transaction_code,
            p.payment_date

        FROM booking b

        LEFT JOIN customer c
        ON b.Cid = c.Cid

        LEFT JOIN vehicle v
        ON b.Vid = v.Vid

        LEFT JOIN payment p
        ON b.Bid = p.Bid

        WHERE 1=1";


/* =========================================
   SEARCH FILTER
========================================= */

if ($search != "") {

    $safe_search =
        mysqli_real_escape_string(
            $conn,
            $search
        );


    $sql .= " AND (

        c.C_name LIKE '%$safe_search%'

        OR c.phone LIKE '%$safe_search%'

        OR v.name LIKE '%$safe_search%'

        OR v.Vno LIKE '%$safe_search%'

        OR CAST(b.Bid AS CHAR)
            LIKE '%$safe_search%'

        OR p.transaction_uuid
            LIKE '%$safe_search%'

        OR p.transaction_code
            LIKE '%$safe_search%'

    )";
}


/* =========================================
   BOOKING STATUS FILTER
========================================= */

if ($status != "") {

    $safe_status =
        mysqli_real_escape_string(
            $conn,
            $status
        );


    $sql .= "
        AND b.status = '$safe_status'
    ";
}


/* =========================================
   PAYMENT STATUS FILTER
========================================= */

if ($payment_status != "") {

    $safe_payment_status =
        mysqli_real_escape_string(
            $conn,
            $payment_status
        );


    /*
        NOT PAID means there is no
        payment row for the booking.
    */

    if ($safe_payment_status == "Not Paid") {

        $sql .= "
            AND p.Pid IS NULL
        ";

    } else {

        /*
            Paid / Pending / Failed
            must come from payment.status
        */

        $sql .= "
            AND p.status =
            '$safe_payment_status'
        ";
    }
}


/* =========================================
   ORDER
========================================= */

$sql .= "
    ORDER BY b.Bid DESC
";


$result =
    mysqli_query(
        $conn,
        $sql
    );


if (!$result) {

    die(
        "Query Failed: "
        . mysqli_error($conn)
    );
}


/* =========================================
   BOOKING STATISTICS
========================================= */


/* TOTAL BOOKINGS */

$total_query =
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM booking"
    );


$total_row =
    mysqli_fetch_assoc(
        $total_query
    );


$total =
    $total_row['total'];


/* PENDING BOOKINGS */

$pending_query =
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM booking
         WHERE status = 'Pending'"
    );


$pending_row =
    mysqli_fetch_assoc(
        $pending_query
    );


$pending =
    $pending_row['total'];


/* APPROVED BOOKINGS */

$approved_query =
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM booking
         WHERE status = 'Approved'"
    );


$approved_row =
    mysqli_fetch_assoc(
        $approved_query
    );


$approved =
    $approved_row['total'];


/* COMPLETED BOOKINGS */

$completed_query =
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM booking
         WHERE status = 'Completed'"
    );


$completed_row =
    mysqli_fetch_assoc(
        $completed_query
    );


$completed =
    $completed_row['total'];


/* =========================================
   PAYMENT STATISTICS
========================================= */


/* PAID PAYMENTS */

$paid_payment_query =
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM payment
         WHERE status = 'Paid'"
    );


$paid_payment_row =
    mysqli_fetch_assoc(
        $paid_payment_query
    );


$paid_payments =
    $paid_payment_row['total'];


/* PENDING PAYMENT ROWS */

$pending_payment_query =
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM payment
         WHERE status = 'Pending'"
    );


$pending_payment_row =
    mysqli_fetch_assoc(
        $pending_payment_query
    );


$pending_payments =
    $pending_payment_row['total'];


/* FAILED PAYMENTS */

$failed_payment_query =
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM payment
         WHERE status = 'Failed'"
    );


$failed_payment_row =
    mysqli_fetch_assoc(
        $failed_payment_query
    );


$failed_payments =
    $failed_payment_row['total'];


/* =========================================
   NOT PAID BOOKINGS

   Booking exists but there is no payment row.
========================================= */

$not_paid_query =
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total

         FROM booking b

         LEFT JOIN payment p
         ON b.Bid = p.Bid

         WHERE p.Pid IS NULL"
    );


$not_paid_row =
    mysqli_fetch_assoc(
        $not_paid_query
    );


$not_paid =
    $not_paid_row['total'];

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
        Bookings - Admin
    </title>


    <!-- MATERIAL SYMBOLS -->

    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined"
    >


    <!-- EXISTING BOOKING CSS -->

    <link
        rel="stylesheet"
        href="../Assests/2.4.booking.css"
    >


    <!-- EXTRA PAYMENT STATUS CSS -->

    <style>

        /* =========================================
           PAYMENT STATUS BADGE
        ========================================= */

        .payment-status {

            display: inline-block;

            padding: 6px 12px;

            border-radius: 20px;

            font-size: 13px;

            font-weight: bold;

            white-space: nowrap;
        }


        /* PAID */

        .payment-paid {

            background: #d4edda;

            color: #155724;
        }


        /* PENDING */

        .payment-pending {

            background: #fff3cd;

            color: #856404;
        }


        /* FAILED */

        .payment-failed {

            background: #f8d7da;

            color: #721c24;
        }


        /* NOT PAID */

        .payment-not-paid {

            background: #e5e7eb;

            color: #374151;
        }


        /* =========================================
           PAYMENT METHOD
        ========================================= */

        .payment-method {

            font-weight: 600;
        }


        /* =========================================
           FILTER SELECT
        ========================================= */

        .filter-box select {

            padding: 10px;

            border: 1px solid #ccc;

            border-radius: 5px;

            margin-right: 8px;
        }


        /* =========================================
           PAYMENT SUMMARY
        ========================================= */

        .payment-summary {

            display: flex;

            gap: 15px;

            margin-top: 15px;

            margin-bottom: 20px;

            flex-wrap: wrap;
        }


        .payment-summary-box {

            background: white;

            padding: 12px 18px;

            border-radius: 7px;

            box-shadow:
                0 2px 6px
                rgba(0, 0, 0, 0.08);
        }


        .payment-summary-box strong {

            margin-left: 5px;
        }


        /* =========================================
           TABLE
        ========================================= */

        .table-container {

            overflow-x: auto;
        }


        table {

            min-width: 1500px;
        }

    </style>

</head>


<body>


<!-- =========================================
     SIDEBAR
========================================= -->

<div class="sidebar">


    <h2>
        Admin Panel
    </h2>


    <a href="2.dashboard.php">

        <span class="material-symbols-outlined">
            dashboard
        </span>

        Dashboard

    </a>


    <a href="2.2.customer.php">

        <span class="material-symbols-outlined">
            group
        </span>

        Customers

    </a>


    <a href="2.3.vehicleSlidebar.php">

        <span class="material-symbols-outlined">
            directions_car
        </span>

        Vehicles

    </a>


    <a
        href="2.4.bookingSlidebar.php"
        class="active"
    >

        <span class="material-symbols-outlined">
            book_online
        </span>

        Bookings

    </a>


    <a href="2.5.paymentSlidebar.php">

        <span class="material-symbols-outlined">
            payments
        </span>

        Payments

    </a>


    <a href="2.6.analytics.php">

        <span class="material-symbols-outlined">
            analytics
        </span>

        Reports / Analytics

    </a>


    <a href="Profile.php">

        <span class="material-symbols-outlined">
            account_circle
        </span>

        Profile

    </a>


    <a href="logout.php">

        <span class="material-symbols-outlined">
            logout
        </span>

        Logout

    </a>


</div>


<!-- =========================================
     MAIN CONTENT
========================================= -->

<div class="main-content">


    <h1>
        Bookings
    </h1>


    <!-- =====================================
         BOOKING STATISTICS
    ====================================== -->

    <div class="stats-container">


        <div class="stat-card">

            <h3>
                Total Bookings
            </h3>

            <p>
                <?php echo $total; ?>
            </p>

        </div>


        <div class="stat-card">

            <h3>
                Pending
            </h3>

            <p>
                <?php echo $pending; ?>
            </p>

        </div>


        <div class="stat-card">

            <h3>
                Approved
            </h3>

            <p>
                <?php echo $approved; ?>
            </p>

        </div>


        <div class="stat-card">

            <h3>
                Completed
            </h3>

            <p>
                <?php echo $completed; ?>
            </p>

        </div>


    </div>


    <!-- =====================================
         PAYMENT SUMMARY
    ====================================== -->

    <div class="payment-summary">


        <div class="payment-summary-box">

            Paid Payments:

            <strong>
                <?php echo $paid_payments; ?>
            </strong>

        </div>


        <div class="payment-summary-box">

            Pending Payments:

            <strong>
                <?php echo $pending_payments; ?>
            </strong>

        </div>


        <div class="payment-summary-box">

            Failed Payments:

            <strong>
                <?php echo $failed_payments; ?>
            </strong>

        </div>


        <div class="payment-summary-box">

            Not Paid:

            <strong>
                <?php echo $not_paid; ?>
            </strong>

        </div>


    </div>


    <!-- =====================================
         SEARCH + FILTER
    ====================================== -->

    <div class="filter-box">


        <form method="GET">


            <!-- SEARCH -->

            <input
                type="text"
                name="search"
                placeholder="Search customer, phone, vehicle, transaction..."
                value="<?php
                    echo htmlspecialchars(
                        $search
                    );
                ?>"
            >


            <!-- BOOKING STATUS FILTER -->

            <select name="status">


                <option value="">
                    All Booking Status
                </option>


                <option
                    value="Pending"
                    <?php
                    if ($status == "Pending") {
                        echo "selected";
                    }
                    ?>
                >
                    Pending
                </option>


                <option
                    value="Approved"
                    <?php
                    if ($status == "Approved") {
                        echo "selected";
                    }
                    ?>
                >
                    Approved
                </option>


                <option
                    value="Completed"
                    <?php
                    if ($status == "Completed") {
                        echo "selected";
                    }
                    ?>
                >
                    Completed
                </option>


                <option
                    value="Cancelled"
                    <?php
                    if ($status == "Cancelled") {
                        echo "selected";
                    }
                    ?>
                >
                    Cancelled
                </option>


            </select>


            <!-- PAYMENT STATUS FILTER -->

            <select name="payment_status">


                <option value="">
                    All Payment Status
                </option>


                <option
                    value="Paid"
                    <?php
                    if ($payment_status == "Paid") {
                        echo "selected";
                    }
                    ?>
                >
                    Paid
                </option>


                <option
                    value="Pending"
                    <?php
                    if ($payment_status == "Pending") {
                        echo "selected";
                    }
                    ?>
                >
                    Pending
                </option>


                <option
                    value="Failed"
                    <?php
                    if ($payment_status == "Failed") {
                        echo "selected";
                    }
                    ?>
                >
                    Failed
                </option>


                <option
                    value="Not Paid"
                    <?php
                    if ($payment_status == "Not Paid") {
                        echo "selected";
                    }
                    ?>
                >
                    Not Paid
                </option>


            </select>


            <button type="submit">
                Search
            </button>


            <a href="2.4.bookingSlidebar.php">
                Clear
            </a>


        </form>


    </div>


    <!-- =====================================
         BOOKING TABLE
    ====================================== -->

    <div class="table-container">


        <table>


            <thead>


                <tr>


                    <th>
                        Booking ID
                    </th>


                    <th>
                        Customer
                    </th>


                    <th>
                        Phone
                    </th>


                    <th>
                        Vehicle
                    </th>


                    <th>
                        Vehicle No.
                    </th>


                    <th>
                        Pickup Location
                    </th>


                    <th>
                        Start Date
                    </th>


                    <th>
                        End Date
                    </th>


                    <th>
                        Total Days
                    </th>


                    <th>
                        Total Amount
                    </th>


                    <th>
                        Booking Status
                    </th>


                    <th>
                        Payment Status
                    </th>


                    <th>
                        Payment Method
                    </th>


                    <th>
                        Action
                    </th>


                </tr>


            </thead>


            <tbody>


<?php


if (mysqli_num_rows($result) > 0) {


    while (
        $row =
        mysqli_fetch_assoc($result)
    ) {


        /* =====================================
           CALCULATE TOTAL DAYS
        ====================================== */

        $start =
            strtotime(
                $row['pickup_date']
            );


        $end =
            strtotime(
                $row['return_date']
            );


        $total_days =
            ($end - $start)
            /
            (60 * 60 * 24);


        /* =====================================
           CALCULATE BOOKING AMOUNT
        ====================================== */

        $total_amount =
            $total_days
            *
            (float)
            $row['price_per_day'];


        /* =====================================
           PAYMENT STATUS

           IMPORTANT:

           No payment row = Not Paid

           Payment row exists =
           use actual payment.status
        ====================================== */

        if ($row['Pid'] === null) {


            $display_payment_status =
                "Not Paid";


            $payment_class =
                "not-paid";


        } else {


            $display_payment_status =
                $row['payment_status'];


            $payment_class =
                strtolower(
                    $row['payment_status']
                );

        }


?>


                <tr>


                    <!-- =========================
                         BOOKING ID
                    ========================== -->

                    <td>

                        #<?php

                        echo htmlspecialchars(
                            $row['Bid']
                        );

                        ?>

                    </td>


                    <!-- =========================
                         CUSTOMER
                    ========================== -->

                    <td>

                        <?php


                        if (
                            !empty(
                                $row['C_name']
                            )
                        ) {


                            echo htmlspecialchars(
                                $row['C_name']
                            );


                        } else {


                            echo "Unknown Customer";

                        }


                        ?>

                    </td>


                    <!-- =========================
                         PHONE
                    ========================== -->

                    <td>

                        <?php


                        if (
                            !empty(
                                $row['phone']
                            )
                        ) {


                            echo htmlspecialchars(
                                $row['phone']
                            );


                        } else {


                            echo "N/A";

                        }


                        ?>

                    </td>


                    <!-- =========================
                         VEHICLE
                    ========================== -->

                    <td>

                        <?php


                        if (
                            !empty(
                                $row['vehicle_name']
                            )
                        ) {


                            echo htmlspecialchars(
                                $row['vehicle_name']
                            );


                        } else {


                            echo "Vehicle not found";

                        }


                        ?>

                    </td>


                    <!-- =========================
                         VEHICLE NUMBER
                    ========================== -->

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $row['Vno']
                            ?? "N/A"
                        );

                        ?>

                    </td>


                    <!-- =========================
                         PICKUP LOCATION
                    ========================== -->

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $row['pickup_location']
                        );

                        ?>

                    </td>


                    <!-- =========================
                         START DATE
                    ========================== -->

                    <td>

                        <?php

                        echo date(
                            "Y-m-d",
                            strtotime(
                                $row['pickup_date']
                            )
                        );

                        ?>

                    </td>


                    <!-- =========================
                         END DATE
                    ========================== -->

                    <td>

                        <?php

                        echo date(
                            "Y-m-d",
                            strtotime(
                                $row['return_date']
                            )
                        );

                        ?>

                    </td>


                    <!-- =========================
                         TOTAL DAYS
                    ========================== -->

                    <td>

                        <?php

                        echo $total_days;

                        ?>

                    </td>


                    <!-- =========================
                         TOTAL AMOUNT
                    ========================== -->

                    <td>

                        Rs.

                        <?php

                        echo number_format(
                            $total_amount,
                            2
                        );

                        ?>

                    </td>


                    <!-- =========================
                         BOOKING STATUS
                    ========================== -->

                    <td>

                        <span
                            class="status <?php
                                echo strtolower(
                                    htmlspecialchars(
                                        $row[
                                            'booking_status'
                                        ]
                                    )
                                );
                            ?>"
                        >

                            <?php

                            echo htmlspecialchars(
                                $row[
                                    'booking_status'
                                ]
                            );

                            ?>

                        </span>

                    </td>


                    <!-- =========================
                         PAYMENT STATUS
                    ========================== -->

                    <td>

                        <span
                            class="payment-status payment-<?php
                                echo htmlspecialchars(
                                    $payment_class
                                );
                            ?>"
                        >

                            <?php

                            echo htmlspecialchars(
                                $display_payment_status
                            );

                            ?>

                        </span>

                    </td>


                    <!-- =========================
                         PAYMENT METHOD
                    ========================== -->

                    <td class="payment-method">

                        <?php


                        if (
                            $row['Pid'] !== null &&
                            !empty(
                                $row['payment_method']
                            )
                        ) {


                            echo htmlspecialchars(
                                $row[
                                    'payment_method'
                                ]
                            );


                        } else {


                            echo "-";

                        }


                        ?>

                    </td>


                    <!-- =========================
                         ACTION
                    ========================== -->

                    <td>


                        <a
                            href="2.4.1.viewBooking.php?id=<?php
                                echo $row['Bid'];
                            ?>"
                            class="view-btn"
                        >
                            View
                        </a>


                        <a
                            href="2.4.2.editBooking.php?id=<?php
                                echo $row['Bid'];
                            ?>"
                            class="edit-btn"
                        >
                            Edit
                        </a>


                        <a
                            href="2.4.3.deleteBooking.php?id=<?php
                                echo $row['Bid'];
                            ?>"
                            class="delete-btn"
                            onclick="
                                return confirm(
                                    'Are you sure you want to delete this booking?'
                                );
                            "
                        >
                            Delete
                        </a>


                    </td>


                </tr>


<?php


    }


} else {


?>


                <tr>


                    <td
                        colspan="14"
                        class="no-booking"
                    >

                        No bookings found.

                    </td>


                </tr>


<?php


}


?>


            </tbody>


        </table>


    </div>


</div>


</body>

</html>