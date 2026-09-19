<?php

$conn = mysqli_connect("localhost","root", "","vehicle_renting_system");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}


/* =========================
   SEARCH
========================= */

$search = "";

if (isset($_GET["search"])) {
    $search = mysqli_real_escape_string(
        $conn,
        $_GET["search"]
    );
}


/* =========================
   TYPE FILTER
========================= */

$type = "";

if (isset($_GET["type"])) {
    $type = mysqli_real_escape_string(
        $conn,
        $_GET["type"]
    );
}


/* =========================
   CATEGORY FILTER
========================= */

$category = "";

if (isset($_GET["category"])) {
    $category = mysqli_real_escape_string(
        $conn,
        $_GET["category"]
    );
}


/* 
   STATUS FILTER
 */

$status = "";

if (isset($_GET["status"])) {
    $status = mysqli_real_escape_string(
        $conn,
        $_GET["status"]
    );
}


/* =========================
   VEHICLE QUERY
========================= */

$sql = "SELECT * FROM vehicle WHERE 1=1";


if ($search != "") {

    $sql .= " AND (
        vehicle_name LIKE '%$search%'
        OR brand LIKE '%$search%'
        OR vehicle_number LIKE '%$search%'
    )";

}


if ($type != "") {

    $sql .= " AND type = '$type'";

}


if ($category != "") {

    $sql .= " AND category = '$category'";

}


if ($status != "") {

    $sql .= " AND status = '$status'";

}


$sql .= " ORDER BY Vid DESC";


$result = mysqli_query($conn, $sql);


/* =========================
   VEHICLE STATISTICS
========================= */


/* TOTAL */

$total_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM vehicle"
);

$total = mysqli_fetch_assoc(
    $total_query
)["total"];


/* AVAILABLE */

$available_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS available
     FROM vehicle
     WHERE status = 'Available'"
);

$available = mysqli_fetch_assoc(
    $available_query
)["available"];


/* RENTED */

$rented_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS rented
     FROM vehicle
     WHERE status = 'Rented'"
);

$rented = mysqli_fetch_assoc( $rented_query)["rented"];


/* UNAVAILABLE */

$unavailable_query = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS unavailable
     FROM vehicle
     WHERE status = 'Unavailable'"
);

$unavailable = mysqli_fetch_assoc(
    $unavailable_query
)["unavailable"];

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Vehicles - Admin</title>

    <link rel="stylesheet"
          href="../Assests/2.3.vehicleSlidebar.css">

</head>


<body>


<div class="container">


    <!-- =========================
         PAGE HEADER
    ========================== -->

    <div class="page-header">

        <div>

            <h1>Vehicles</h1>

            <p>
                Manage all vehicles in the rental system.
            </p>

        </div>


        <a href="2.3.1.addVehicle.php"
           class="add-vehicle-btn">

            + Add Vehicle

        </a>

    </div>



    <!-- =========================
         STATISTICS
    ========================== -->

    <div class="vehicle-stats">


        <!-- TOTAL -->

        <div class="stat-box">

            <h3>Total Vehicles</h3>

            <p>
                <?php echo $total; ?>
            </p>

        </div>


        <!-- AVAILABLE -->

        <div class="stat-box">

            <h3>Available</h3>

            <p>
                <?php echo $available; ?>
            </p>

        </div>


        <!-- RENTED -->

        <div class="stat-box">

            <h3>Rented</h3>

            <p>
                <?php echo $rented; ?>
            </p>

        </div>


        <!-- UNAVAILABLE -->

        <div class="stat-box">

            <h3>Unavailable</h3>

            <p>
                <?php echo $unavailable; ?>
            </p>

        </div>


    </div>



    <!-- =========================
         SEARCH & FILTER
    ========================== -->

    <form method="GET"
          action="2.3.vehicleSliderbar.php"
          class="vehicle-tools">


        <!-- SEARCH -->

        <div class="search">

            <input
                type="text"
                name="search"
                placeholder="Search vehicle..."
                value="<?php echo htmlspecialchars($search); ?>"
            >

        </div>


        <!-- TYPE -->

        <select name="type">

            <option value="">
                All Types
            </option>

            <option value="Bike"
                <?php
                if ($type == "Bike")
                    echo "selected";
                ?>>
                Bike
            </option>

            <option value="Scooter"
                <?php
                if ($type == "Scooter")
                    echo "selected";
                ?>>
                Scooter
            </option>

            

        </select>



        <!-- CATEGORY -->

        <select name="category">

            <option value="">
                All Categories
            </option>

            <option value="Sports"
                <?php
                if ($category == "Sports")
                    echo "selected";
                ?>>
                Sports
            </option>

            <option value="Cruiser-Classic"
                <?php
                if ($category == "Cruiser-Classic")
                    echo "selected";
                ?>>
                Cruiser / Classic
            </option>

            <option value="Naked-Street"
                <?php
                if ($category == "Naked-Street")
                    echo "selected";
                ?>>
                Naked / Street
            </option>
            </option>


        </select>



        <!-- STATUS -->

        <select name="status">

            <option value="">
                All Status
            </option>

            <option value="Available"
                <?php
                if ($status == "Available")
                    echo "selected";
                ?>>
                Available
            </option>

            <option value="Rented"
                <?php
                if ($status == "Rented")
                    echo "selected";
                ?>>
                Rented
            </option>

            <option value="Unavailable"
                <?php
                if ($status == "Unavailable")
                    echo "selected";
                ?>>
                Unavailable
            </option>

        </select>



        <!-- SEARCH BUTTON -->

        <button type="submit"
                class="search-button">

            Search

        </button>


    </form>



    <!-- =========================
         VEHICLE TABLE
    ========================== -->

    <div class="vehicle-table-container">


        <table>


            <thead>

                <tr>

                    <th>Image</th>

                    <th>Name</th>

                    <th>Brand</th>

                    <th>Type</th>

                    <th>Category</th>

                    <th>Price / Day</th>

                    <th>Status</th>

                    <th>Action</th>

                </tr>

            </thead>



            <tbody>


            <?php

            if (mysqli_num_rows($result) > 0) {

                while ($row = mysqli_fetch_assoc($result)) {

            ?>


                <tr>


                    <!-- IMAGE -->

                    <td>

                        <?php

                        if (!empty($row["image"])) {

                        ?>

                            <img
                                src="../User/uploads/vehicles/<?php
                                echo htmlspecialchars(
                                    $row["image"]
                                );
                                ?>"
                                class="vehicle-image"
                            >

                        <?php

                        } else {

                        ?>

                            <span class="no-image">
                                No Image
                            </span>

                        <?php

                        }

                        ?>

                    </td>



                    <!-- VEHICLE NAME -->

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $row["name"]
                        );

                        ?>

                    </td>



                    <!-- BRAND -->

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $row["brand"]
                        );

                        ?>

                    </td>



                    <!-- TYPE -->

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $row["type"]
                        );

                        ?>

                    </td>



                    <!-- CATEGORY -->

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $row["category"]
                        );

                        ?>

                    </td>



                    <!-- PRICE -->

                    <td>

                        Rs.

                        <?php

                        echo number_format(
                            $row["price_per_day"]
                        );

                        ?>

                    </td>



                    <!-- STATUS -->

                    <td>


                        <?php

                        if ($row["status"] == "Available") {

                        ?>

                            <span class="status available">

                                Available

                            </span>


                        <?php

                        } elseif (
                            $row["status"] == "Rented"
                        ) {

                        ?>

                            <span class="status rented">

                                Rented

                            </span>


                        <?php

                        } else {

                        ?>

                            <span class="status unavailable">

                                Unavailable

                            </span>


                        <?php

                        }

                        ?>

                    </td>



                    <!-- ACTION -->

                    <td>

                        <div class="actions">


                            <!-- VIEW -->

                            <a
                                href="2.3.2.viewVehicle.php?id=<?php
                                echo $row["Vid"];
                                ?>"
                                class="view-btn"
                            >

                                View

                            </a>


                            <!-- EDIT -->

                            <a
                                href="2.3.3.editVehicle.php?id=<?php
                                echo $row["Vid"];
                                ?>"
                                class="edit-btn"
                            >

                                Edit

                            </a>


                            <!-- DELETE -->

                            <a
                                href="2.3.4.deleteVehicle.php?id=<?php
                                echo $row["Vid"];
                                ?>"
                                class="delete-btn"
                                onclick="return confirm(
                                    'Are you sure you want to delete this vehicle?'
                                );"
                            >

                                Delete

                            </a>


                        </div>

                    </td>


                </tr>


            <?php

                }

            } else {

            ?>


                <tr>

                    <td colspan="8"
                        class="no-data">

                        No vehicles found.

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