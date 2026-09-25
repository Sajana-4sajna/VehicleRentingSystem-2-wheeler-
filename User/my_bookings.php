
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

$user_id = $_SESSION['Cid'];


/* =========================================
   GET USER'S BOOKINGS
========================================= */

$sql = "SELECT
        booking.Bid,
        vehicle.name,
        vehicle.price_per_day,
        booking.pickup_location,
        booking.pickup_date,
        booking.return_date,
        booking.created_at,
        booking.status

        FROM booking

        INNER JOIN vehicle
        ON booking.Vid = vehicle.Vid

        WHERE booking.Cid = ?

        ORDER BY booking.Bid DESC";


$stmt = mysqli_prepare($conn, $sql);


if (!$stmt) {

    die("Database Error: " . mysqli_error($conn));

}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);


mysqli_stmt_execute($stmt);


$result = mysqli_stmt_get_result($stmt);

?>



<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Bookings</title>


    <style>

        body {

            font-family: Arial, sans-serif;

            background: #f2f2f2;

            margin: 0;

            padding: 0;

        }


        /* =========================================
           HEADER
        ========================================= */

        header {

            background: #333;

            color: white;

            padding: 15px 30px;

            display: flex;

            justify-content: space-between;

            align-items: center;

        }


        header h2 {

            margin: 0;

        }


        nav a {

            color: white;

            text-decoration: none;

            margin-left: 20px;

        }


        nav a:hover {

            text-decoration: underline;

        }


        /* =========================================
           PAGE TITLE
        ========================================= */

        h1 {

            text-align: center;

            margin-top: 30px;

        }


        /* =========================================
           TABLE
        ========================================= */

        .table-container {

            width: 95%;

            margin: 40px auto;

            overflow-x: auto;

        }


        table {

            width: 100%;

            border-collapse: collapse;

            background: white;

        }


        table,
        th,
        td {

            border: 1px solid gray;

        }


        th,
        td {

            padding: 12px;

            text-align: center;

        }


        th {

            background: #28a745;

            color: white;

        }


        tr:nth-child(even) {

            background: #f9f9f9;

        }


        /* =========================================
           STATUS
        ========================================= */

        .status {

            font-weight: bold;

        }


        /* =========================================
           NO BOOKING
        ========================================= */

        .no-booking {

            text-align: center;

            padding: 20px;

        }

    </style>

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


        <a href="vehicles.php">
            Vehicles
        </a>


        <a
            href="logout.php"
            onclick="return confirmLogout();"
        >
            Logout
        </a>

    </nav>

</header>



<!-- =========================================
     PAGE TITLE
========================================= -->

<h1>
    My Bookings
</h1>



<!-- =========================================
     BOOKING TABLE
========================================= -->

<div class="table-container">

    <table>

        <tr>

            <th>
                Vehicle
            </th>


            <th>
                Pickup Location
            </th>


            <th>
                Pickup Date
            </th>


            <th>
                Return Date
            </th>


            <th>
                Total Days
            </th>


            <th>
                Total Amount
            </th>


            <th>
                Status
            </th>


            <th>
                Booking Created
            </th>

        </tr>



        <?php

        /* =========================================
           CHECK BOOKINGS
        ========================================= */

        if (mysqli_num_rows($result) > 0) {


            /* =========================================
               LOOP THROUGH BOOKINGS
            ========================================= */

            while ($row = mysqli_fetch_assoc($result)) {


                /* =========================================
                   CALCULATE TOTAL DAYS
                ========================================= */

                $start = strtotime($row['pickup_date']);

                $end = strtotime($row['return_date']);


                $total_days =
                    ($end - $start) / (60 * 60 * 24);


                /* =========================================
                   CALCULATE TOTAL AMOUNT
                ========================================= */

                $total_amount =
                    $total_days * $row['price_per_day'];

        ?>


                <tr>


                    <!-- Vehicle -->

                    <td>

                        <?php
                        echo htmlspecialchars(
                            $row['name']
                        );
                        ?>

                    </td>



                    <!-- Pickup Location -->

                    <td>

                        <?php
                        echo htmlspecialchars(
                            $row['pickup_location']
                        );
                        ?>

                    </td>



                    <!-- Pickup Date -->

                    <td>

                        <?php
                        echo htmlspecialchars(
                            $row['pickup_date']
                        );
                        ?>

                    </td>



                    <!-- Return Date -->

                    <td>

                        <?php
                        echo htmlspecialchars(
                            $row['return_date']
                        );
                        ?>

                    </td>



                    <!-- Total Days -->

                    <td>

                        <?php
                        echo $total_days;
                        ?>

                        Day(s)

                    </td>



                    <!-- Total Amount -->

                    <td>

                        Rs.

                        <?php
                        echo number_format(
                            $total_amount,
                            2
                        );
                        ?>

                    </td>



                    <!-- Status -->

                    <td class="status">

                        <?php
                        echo htmlspecialchars(
                            $row['status']
                        );
                        ?>

                    </td>



                    <!-- Created At -->

                    <td>

                        <?php
                        echo htmlspecialchars(
                            $row['created_at']
                        );
                        ?>

                    </td>


                </tr>


        <?php

            }

        } else {

        ?>


            <tr>

                <td
                    colspan="8"
                    class="no-booking"
                >

                    You have no bookings yet.

                </td>

            </tr>


        <?php

        }

        ?>

    </table>

</div>



<!-- =========================================
     JAVASCRIPT
========================================= -->

<script src="../Assests/js/project.js"></script>


<script>

function confirmLogout() {

    return confirm(
        "Are you sure you want to logout?"
    );

}

</script>


</body>

</html>

