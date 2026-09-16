<?php

$conn = mysqli_connect("localhost", "root", "", "vehicle_renting_system");

// include "../connection.php";

/* =========================
   SEARCH AND FILTER
========================= */

$search = $_GET["search"] ?? "";
$status = $_GET["status"] ?? "";


/* =========================
   CUSTOMER QUERY
========================= */

$sql = "SELECT * FROM customer WHERE 1=1";


/* Search by name, email, phone or license */

if ($search != "") {

    $search = mysqli_real_escape_string($conn, $search);

    $sql .= " AND (
        name LIKE '%$search%'
        OR email LIKE '%$search%'
        OR phone LIKE '%$search%'
        OR license_no LIKE '%$search%'
    )";
}


/* Filter by status */

if ($status != "") {

    $status = mysqli_real_escape_string($conn, $status);

    $sql .= " AND status = '$status'";
}


$sql .= " ORDER BY Cid DESC";


$result = mysqli_query($conn, $sql);


/* =========================
   STATISTICS
========================= */

$totalQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM customer"
);

$totalCustomers = mysqli_fetch_assoc($totalQuery)["total"];


$activeQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM customer WHERE status = 'Active'"
);

$activeCustomers = mysqli_fetch_assoc($activeQuery)["total"];


$blockedQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM customer WHERE status = 'Blocked'"
);

$blockedCustomers = mysqli_fetch_assoc($blockedQuery)["total"];


// $newQuery = mysqli_query(
//     $conn,
//     "SELECT COUNT(*) AS total
//      FROM customer
//      WHERE MONTH(created_at) = MONTH(CURRENT_DATE())
//      AND YEAR(created_at) = YEAR(CURRENT_DATE())"
// );

// $newCustomers = mysqli_fetch_assoc($newQuery)["total"];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Customers</title>

    <link rel="stylesheet"
        href="../Assests/2.2.customerSlidebar.css">

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,400,0,0">

</head>


<body>


<div class="main-content">


    <!-- =========================
         PAGE HEADER
    ========================== -->

    <div class="page-header">

        <div>

            <h1>Customers</h1>

            <p>
                Manage all registered customers
            </p>

        </div>


        <div class="admin-info">

            <span class="material-symbols-outlined">
                notifications
            </span>

            <img
                src="../Assests/cat.jpg"
                alt="Admin">

            <span>Admin</span>

            <span class="material-symbols-outlined">
                expand_more
            </span>

        </div>

    </div>



    <!-- =========================
         STATISTICS
    ========================== -->

    <div class="statistics">


        <!-- Total -->

        <div class="stat-card">

            <div class="icon">

                <span class="material-symbols-outlined">
                    group
                </span>

            </div>

            <div>

                <h2>
                    <?php echo $totalCustomers; ?>
                </h2>

                <p>Total Customers</p>

            </div>

        </div>



        <!-- Active -->

        <div class="stat-card">

            <div class="icon">

                <span class="material-symbols-outlined">
                    person_check
                </span>

            </div>

            <div>

                <h2>
                    <?php echo $activeCustomers; ?>
                </h2>

                <p>Active Customers</p>

            </div>

        </div>



        <!-- Blocked -->

        <div class="stat-card">

            <div class="icon">

                <span class="material-symbols-outlined">
                    person_off
                </span>

            </div>

            <div>

                <h2>
                    <?php echo $blockedCustomers; ?>
                </h2>

                <p>Blocked Customers</p>

            </div>

        </div>



        <!-- New This Month -->

        <div class="stat-card">

            <div class="icon">

                <span class="material-symbols-outlined">
                    person_add
                </span>

            </div>

            <div>

                <h2>
                    <!-- <?php echo $newCustomers; ?> -->
                </h2>

                <p>New This Month</p>

            </div>

        </div>


    </div>



    <!-- =========================
         CUSTOMER CONTAINER
    ========================== -->

    <div class="customer-container">


        <div class="customer-top">


            <h2>Customer List</h2>


            <!-- SEARCH + FILTER -->

            <form
                method="GET"
                action="3.customers.php"
                class="customer-tools">


                <!-- Search -->

                <div class="search">

                    <span class="material-symbols-outlined">
                        search
                    </span>

                    <input
                        type="text"
                        name="search"
                        value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="Search customers...">

                </div>


                <!-- Status -->

                <select
                    name="status"
                    onchange="this.form.submit()">

                    <option value="">
                        All Status
                    </option>

                    <option
                        value="Active"
                        <?php
                        if ($status == "Active")
                            echo "selected";
                        ?>>
                        Active
                    </option>

                    <option
                        value="Blocked"
                        <?php
                        if ($status == "Blocked")
                            echo "selected";
                        ?>>
                        Blocked
                    </option>

                </select>


                <button
                    type="submit"
                    class="search-button">

                    Search

                </button>


            </form>

        </div>



        <!-- =========================
             CUSTOMER TABLE
        ========================== -->

        <div class="table-container">

            <table>


                <thead>

                    <tr>

                        <th>Photo</th>

                        <th>Customer Name</th>

                        <th>Email</th>

                        <th>Phone</th>

                        <th>License No.</th>

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


                        <!-- Photo -->

                        <td>

                            <img
                                src="../uploads/<?php echo htmlspecialchars($row["image"]); ?>"
                                class="customer-photo"
                                alt="Customer">

                        </td>



                        <!-- Name -->

                        <td>

                            <?php
                            echo htmlspecialchars($row["name"]);
                            ?>

                        </td>



                        <!-- Email -->

                        <td>

                            <?php
                            echo htmlspecialchars($row["email"]);
                            ?>

                        </td>



                        <!-- Phone -->

                        <td>

                            <?php
                            echo htmlspecialchars($row["phone"]);
                            ?>

                        </td>



                        <!-- License -->

                        <td>

                            <?php
                            echo htmlspecialchars($row["license_no"]);
                            ?>

                        </td>



                        <!-- Status -->

                        <td>

                            <?php

                            if ($row["status"] == "Active") {

                            ?>

                                <span class="status active">
                                    Active
                                </span>

                            <?php

                            } else {

                            ?>

                                <span class="status blocked">
                                    Blocked
                                </span>

                            <?php

                            }

                            ?>

                        </td>



                        <!-- Actions -->

                        <td class="actions">


                            <!-- View -->

                            <a
                                href="3.1view_customer.php?id=<?php echo $row["Cid"]; ?>"
                                class="view-btn">

                                <span class="material-symbols-outlined">
                                    visibility
                                </span>

                            </a>



                            <!-- Edit -->

                            <a
                                href="3.2edit_customer.php?id=<?php echo $row["Cid"]; ?>"
                                class="edit-btn">

                                <span class="material-symbols-outlined">
                                    edit
                                </span>

                            </a>


                        </td>


                    </tr>


                <?php

                    }

                } else {

                ?>


                    <tr>

                        <td
                            colspan="7"
                            class="no-data">

                            No customers found.

                        </td>

                    </tr>


                <?php

                }

                ?>


                </tbody>

            </table>

        </div>



        <!-- =========================
             PAGINATION
        ========================== -->

        <div class="pagination">

            <button>‹</button>

            <button class="selected">
                1
            </button>

            <button>2</button>

            <button>3</button>

            <button>4</button>

            <button>5</button>

            <button>›</button>

        </div>


    </div>


</div>


</body>

</html>