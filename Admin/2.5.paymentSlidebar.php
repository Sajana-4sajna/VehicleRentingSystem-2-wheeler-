
<?php

session_start();

$conn = mysqli_connect("localhost","root", "","vehicle_renting_system");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
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
   STATUS FILTER
========================================= */

$status = "";

if (isset($_GET['status'])) {

    $status = trim($_GET['status']);
}


/* =========================================
   GET PAYMENT INFORMATION

   payment
      ↓
   booking
      ↓
   customer + vehicle
========================================= */

$sql = "SELECT

            p.Pid,
            p.amount,
            p.status AS payment_status,
            p.payment_date,
            p.Bid,
            p.payment_method,
            p.transaction_uuid,
            p.transaction_code,

            b.Cid,
            b.Vid,
            b.pickup_location,
            b.pickup_date,
            b.return_date,
            b.status AS booking_status,

            c.C_name,
            c.phone,

            v.name AS vehicle_name,
            v.Vno

        FROM payment p

        INNER JOIN booking b
        ON p.Bid = b.Bid

        INNER JOIN customer c
        ON b.Cid = c.Cid

        INNER JOIN vehicle v
        ON b.Vid = v.Vid

        WHERE 1=1";


/* =========================================
   SEARCH CONDITION
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

                OR p.transaction_uuid LIKE '%$safe_search%'

                OR p.transaction_code LIKE '%$safe_search%'

                OR p.Bid LIKE '%$safe_search%'

                OR p.Pid LIKE '%$safe_search%'

              )";
}


/* =========================================
   STATUS FILTER
========================================= */

if ($status != "") {

    $safe_status =
        mysqli_real_escape_string(
            $conn,
            $status
        );


    $sql .= "
        AND p.status = '$safe_status'
    ";
}


/* =========================================
   ORDER BY LATEST PAYMENT
========================================= */

$sql .= "
    ORDER BY p.Pid DESC
";


$result =
    mysqli_query(
        $conn,
        $sql
    );


if (!$result) {

    die(
        "Payment Query Error: " .
        mysqli_error($conn)
    );
}


/* =========================================
   TOTAL PAYMENTS
========================================= */

$total_payment_query =
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM payment"
    );


$total_payment_row =
    mysqli_fetch_assoc(
        $total_payment_query
    );


$total_payments =
    $total_payment_row['total'] ?? 0;


/* =========================================
   PAID PAYMENTS
========================================= */

$paid_query =
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM payment
         WHERE status = 'Paid'"
    );


$paid_row =
    mysqli_fetch_assoc(
        $paid_query
    );


$paid_payments =
    $paid_row['total'] ?? 0;


/* =========================================
   PENDING PAYMENTS
========================================= */

$pending_query =
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM payment
         WHERE status = 'Pending'"
    );


$pending_row =
    mysqli_fetch_assoc(
        $pending_query
    );


$pending_payments =
    $pending_row['total'] ?? 0;


/* =========================================
   TOTAL REVENUE

   Only successful/paid payments
   are included.
========================================= */

$revenue_query =
    mysqli_query(
        $conn,
        "SELECT SUM(amount) AS revenue
         FROM payment
         WHERE status = 'Paid'"
    );


$revenue_row =
    mysqli_fetch_assoc(
        $revenue_query
    );


$total_revenue =
    $revenue_row['revenue'] ?? 0;

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
        Payment Management
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

            background: #f4f6f9;

            color: #333;
        }


        /* =========================================
           NAVBAR
        ========================================= */

        .navbar {

            height: 65px;

            background: white;

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 0 25px;

            border-bottom: 1px solid #ddd;

            position: fixed;

            top: 0;

            left: 0;

            right: 0;

            z-index: 1000;
        }


        .navbar h2 {

            margin: 0;
        }


        .admin-name {

            font-weight: bold;

            color: #444;
        }


        /* =========================================
           SIDEBAR
        ========================================= */

        .sidebar {

            position: fixed;

            top: 65px;

            left: 0;

            width: 230px;

            height: calc(100vh - 65px);

            background: #1f2937;

            padding-top: 20px;

            overflow-y: auto;
        }


        .sidebar a {

            display: block;

            color: #e5e7eb;

            text-decoration: none;

            padding: 14px 22px;

            font-size: 15px;
        }


        .sidebar a:hover {

            background: #374151;

            color: white;
        }


        .sidebar .active {

            background: #2563eb;

            color: white;
        }


        /* =========================================
           MAIN CONTENT
        ========================================= */

        .main-content {

            margin-left: 230px;

            padding: 95px 30px 40px;
        }


        .page-header {

            margin-bottom: 25px;
        }


        .page-header h1 {

            margin: 0 0 8px;

            font-size: 28px;
        }


        .page-header p {

            margin: 0;

            color: #666;
        }


        /* =========================================
           STATISTIC CARDS
        ========================================= */

        .stats-container {

            display: grid;

            grid-template-columns:
                repeat(
                    4,
                    minmax(180px, 1fr)
                );

            gap: 20px;

            margin-bottom: 30px;
        }


        .stat-card {

            background: white;

            padding: 22px;

            border-radius: 10px;

            box-shadow:
                0 2px 8px
                rgba(0, 0, 0, 0.08);
        }


        .stat-card h3 {

            margin: 0 0 10px;

            color: #666;

            font-size: 14px;
        }


        .stat-card .number {

            font-size: 26px;

            font-weight: bold;

            color: #111827;
        }


        /* =========================================
           SEARCH / FILTER
        ========================================= */

        .filter-box {

            background: white;

            padding: 20px;

            margin-bottom: 25px;

            border-radius: 10px;

            box-shadow:
                0 2px 8px
                rgba(0, 0, 0, 0.06);
        }


        .filter-form {

            display: flex;

            gap: 12px;

            flex-wrap: wrap;
        }


        .filter-form input,
        .filter-form select {

            padding: 10px 12px;

            border: 1px solid #ccc;

            border-radius: 6px;

            font-size: 14px;
        }


        .filter-form input {

            width: 300px;

            max-width: 100%;
        }


        .search-btn {

            padding: 10px 18px;

            border: none;

            border-radius: 6px;

            background: #2563eb;

            color: white;

            cursor: pointer;
        }


        .search-btn:hover {

            background: #1d4ed8;
        }


        .reset-btn {

            display: inline-block;

            padding: 10px 18px;

            background: #6b7280;

            color: white;

            text-decoration: none;

            border-radius: 6px;
        }


        /* =========================================
           TABLE
        ========================================= */

        .table-container {

            background: white;

            border-radius: 10px;

            overflow-x: auto;

            box-shadow:
                0 2px 8px
                rgba(0, 0, 0, 0.06);
        }


        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 1350px;
        }


        th {

            background: #111827;

            color: white;

            padding: 13px 10px;

            font-size: 13px;

            text-align: left;

            white-space: nowrap;
        }


        td {

            padding: 13px 10px;

            border-bottom:
                1px solid #e5e7eb;

            font-size: 13px;
        }


        tr:hover {

            background: #f9fafb;
        }


        /* =========================================
           PAYMENT STATUS
        ========================================= */

        .status {

            display: inline-block;

            padding: 5px 10px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: bold;
        }


        .paid {

            background: #dcfce7;

            color: #166534;
        }


        .pending {

            background: #fef3c7;

            color: #92400e;
        }


        .failed {

            background: #fee2e2;

            color: #991b1b;
        }


        .booking-status {

            font-weight: bold;
        }


        /* =========================================
           NO DATA
        ========================================= */

        .no-data {

            text-align: center;

            padding: 30px;

            color: #777;
        }


        /* =========================================
           RESPONSIVE
        ========================================= */

        @media(max-width: 1000px) {

            .stats-container {

                grid-template-columns:
                    repeat(2, 1fr);
            }
        }


        @media(max-width: 700px) {

            .sidebar {

                width: 180px;
            }


            .main-content {

                margin-left: 180px;

                padding-left: 15px;

                padding-right: 15px;
            }


            .stats-container {

                grid-template-columns: 1fr;
            }
        }

    </style>

</head>


<body>


<!-- =========================================
     NAVBAR
========================================= -->

<div class="navbar">

    <h2>
        Admin Dashboard
    </h2>


    <div class="admin-name">

        Admin

    </div>

</div>


<!-- =========================================
     SIDEBAR
========================================= -->

<div class="sidebar">


    <a href="2.dashboard.php">

        Dashboard

    </a>


    <a href="2.2.customer.php">

        Customer

    </a>


    <a href="2.3.vehicle.php">

        Vehicle

    </a>


    <a href="2.4.bookingSlidebar.php">

        Bookings

    </a>


    <a
        href="2.5.paymentSlidebar.php"
        class="active"
    >

        Payments

    </a>


</div>


<!-- =========================================
     MAIN CONTENT
========================================= -->

<div class="main-content">


    <!-- =====================================
         HEADER
    ====================================== -->

    <div class="page-header">

        <h1>
            Payment Management
        </h1>


        <p>
            View customer payments,
            transactions and revenue.
        </p>

    </div>


    <!-- =====================================
         STATISTICS
    ====================================== -->

    <div class="stats-container">


        <!-- TOTAL PAYMENTS -->

        <div class="stat-card">

            <h3>
                Total Payments
            </h3>

            <div class="number">

                <?php
                echo $total_payments;
                ?>

            </div>

        </div>


        <!-- PAID -->

        <div class="stat-card">

            <h3>
                Paid Payments
            </h3>

            <div class="number">

                <?php
                echo $paid_payments;
                ?>

            </div>

        </div>


        <!-- PENDING -->

        <div class="stat-card">

            <h3>
                Pending Payments
            </h3>

            <div class="number">

                <?php
                echo $pending_payments;
                ?>

            </div>

        </div>


        <!-- REVENUE -->

        <div class="stat-card">

            <h3>
                Total Revenue
            </h3>

            <div class="number">

                Rs.

                <?php

                echo number_format(
                    (float) $total_revenue,
                    2
                );

                ?>

            </div>

        </div>


    </div>


    <!-- =====================================
         SEARCH + FILTER
    ====================================== -->

    <div class="filter-box">


        <form
            method="GET"
            class="filter-form"
        >


            <input
                type="text"
                name="search"
                placeholder="Customer, vehicle, transaction..."
                value="<?php
                    echo htmlspecialchars(
                        $search
                    );
                ?>"
            >


            <select name="status">


                <option value="">

                    All Payment Status

                </option>


                <option
                    value="Paid"
                    <?php
                    if ($status == "Paid") {
                        echo "selected";
                    }
                    ?>
                >

                    Paid

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
                    value="Failed"
                    <?php
                    if ($status == "Failed") {
                        echo "selected";
                    }
                    ?>
                >

                    Failed

                </option>


            </select>


            <button
                type="submit"
                class="search-btn"
            >

                Search

            </button>


            <a
                href="2.5.paymentSlidebar.php"
                class="reset-btn"
            >

                Reset

            </a>


        </form>


    </div>


    <!-- =====================================
         PAYMENT TABLE
    ====================================== -->

    <div class="table-container">


        <table>


            <thead>

                <tr>

                    <th>
                        Payment ID
                    </th>

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
                        Amount
                    </th>

                    <th>
                        Payment Method
                    </th>

                    <th>
                        Payment Status
                    </th>

                    <th>
                        Booking Status
                    </th>

                    <th>
                        Transaction UUID
                    </th>

                    <th>
                        Transaction Code
                    </th>

                    <th>
                        Payment Date
                    </th>

                </tr>

            </thead>


            <tbody>


            <?php

            if (
                mysqli_num_rows($result) > 0
            ) {


                while (
                    $row =
                        mysqli_fetch_assoc(
                            $result
                        )
                ) {


                    $status_class =
                        strtolower(
                            $row[
                                'payment_status'
                            ]
                        );

            ?>


                <tr>


                    <!-- PAYMENT ID -->

                    <td>

                        #<?php
                        echo htmlspecialchars(
                            $row['Pid']
                        );
                        ?>

                    </td>


                    <!-- BOOKING ID -->

                    <td>

                        #<?php
                        echo htmlspecialchars(
                            $row['Bid']
                        );
                        ?>

                    </td>


                    <!-- CUSTOMER -->

                    <td>

                        <?php
                        echo htmlspecialchars(
                            $row['C_name']
                        );
                        ?>

                    </td>


                    <!-- PHONE -->

                    <td>

                        <?php
                        echo htmlspecialchars(
                            $row['phone']
                        );
                        ?>

                    </td>


                    <!-- VEHICLE -->

                    <td>

                        <?php
                        echo htmlspecialchars(
                            $row['vehicle_name']
                        );
                        ?>

                    </td>


                    <!-- VEHICLE NUMBER -->

                    <td>

                        <?php
                        echo htmlspecialchars(
                            $row['Vno']
                        );
                        ?>

                    </td>


                    <!-- AMOUNT -->

                    <td>

                        Rs.

                        <?php

                        echo number_format(
                            (float) $row['amount'],
                            2
                        );

                        ?>

                    </td>


                    <!-- PAYMENT METHOD -->

                    <td>

                        <?php
                        echo htmlspecialchars(
                            $row[
                                'payment_method'
                            ]
                        );
                        ?>

                    </td>


                    <!-- PAYMENT STATUS -->

                    <td>

                        <span
                            class="status <?php
                            echo htmlspecialchars(
                                $status_class
                            );
                            ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                $row[
                                    'payment_status'
                                ]
                            );
                            ?>

                        </span>

                    </td>


                    <!-- BOOKING STATUS -->

                    <td class="booking-status">

                        <?php
                        echo htmlspecialchars(
                            $row[
                                'booking_status'
                            ]
                        );
                        ?>

                    </td>


                    <!-- TRANSACTION UUID -->

                    <td>

                        <?php

                        if (
                            !empty(
                                $row[
                                    'transaction_uuid'
                                ]
                            )
                        ) {

                            echo htmlspecialchars(
                                $row[
                                    'transaction_uuid'
                                ]
                            );

                        } else {

                            echo "-";
                        }

                        ?>

                    </td>


                    <!-- TRANSACTION CODE -->

                    <td>

                        <?php

                        if (
                            !empty(
                                $row[
                                    'transaction_code'
                                ]
                            )
                        ) {

                            echo htmlspecialchars(
                                $row[
                                    'transaction_code'
                                ]
                            );

                        } else {

                            echo "-";
                        }

                        ?>

                    </td>


                    <!-- PAYMENT DATE -->

                    <td>

                        <?php

                        if (
                            !empty(
                                $row['payment_date']
                            )
                        ) {

                            echo htmlspecialchars(
                                $row['payment_date']
                            );

                        } else {

                            echo "-";
                        }

                        ?>

                    </td>


                </tr>


            <?php

                }

            } else {

            ?>


                <tr>

                    <td
                        colspan="13"
                        class="no-data"
                    >

                        No payment records found.

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

