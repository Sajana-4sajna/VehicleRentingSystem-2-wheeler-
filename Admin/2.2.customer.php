<?php

session_start();


/* DATABASE CONNECTION */

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "vehicle_renting_system"
);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}


/* SEARCH AND FILTER */

$search = $_GET["search"] ?? "";
$status = $_GET["status"] ?? "";


/* CUSTOMER QUERY */

$sql = "SELECT * FROM customer WHERE 1=1";


/* SEARCH */

if ($search != "") {

    $search = mysqli_real_escape_string($conn, $search);

    $sql .= " AND (
        C_name LIKE '%$search%'
        OR email LIKE '%$search%'
        OR phone LIKE '%$search%'
        OR liscense_no LIKE '%$search%'
    )";
}


/* FILTER BY STATUS */

if ($status != "") {

    $status = mysqli_real_escape_string($conn, $status);

    $sql .= " AND status = '$status'";
}


/* ORDER */

$sql .= " ORDER BY Cid DESC";


/* RUN CUSTOMER QUERY */

$result = mysqli_query($conn, $sql);

if (!$result) {

    die("Query failed: " . mysqli_error($conn));

}


/* STATISTICS */


/* TOTAL CUSTOMERS */

$totalQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM customer"
);

$totalCustomers = mysqli_fetch_assoc($totalQuery)["total"];


/* ACTIVE CUSTOMERS */

$activeQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total 
     FROM customer 
     WHERE status = 'Active'"
);

$activeCustomers = mysqli_fetch_assoc($activeQuery)["total"];


/* BLOCKED CUSTOMERS */

$blockedQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total 
     FROM customer 
     WHERE status = 'Blocked'"
);

$blockedCustomers = mysqli_fetch_assoc($blockedQuery)["total"];

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Customers</title>

    <link
        rel="stylesheet"
        href="../Assests/2.2.customerCSS.css"
    >

</head>


<body>


<div class="container">


    <h1>Customers</h1>


    <!-- STATISTICS -->

    <div class="customer-stats">


        <div class="stat-box">

            <h3>Total Customers</h3>

            <p>
                <?php echo $totalCustomers; ?>
            </p>

        </div>


        <div class="stat-box">

            <h3>Active Customers</h3>

            <p>
                <?php echo $activeCustomers; ?>
            </p>

        </div>


        <div class="stat-box">

            <h3>Blocked Customers</h3>

            <p>
                <?php echo $blockedCustomers; ?>
            </p>

        </div>


    </div>


    <!-- SEARCH AND FILTER -->

    <form
        method="GET"
        action="2.2.customer.php"
        class="customer-tools"
    >


        <div class="search">


            <input
                type="text"
                name="search"
                value="<?php echo htmlspecialchars($search); ?>"
                placeholder="Search customers..."
            >


        </div>


        <select
            name="status"
            onchange="this.form.submit()"
        >


            <option value="">
                All Status
            </option>


            <option
                value="Active"
                <?php
                echo ($status == "Active")
                    ? "selected"
                    : "";
                ?>
            >
                Active
            </option>


            <option
                value="Blocked"
                <?php
                echo ($status == "Blocked")
                    ? "selected"
                    : "";
                ?>
            >
                Blocked
            </option>


        </select>


        <button
            type="submit"
            class="search-button"
        >
            Search
        </button>


    </form>


    <!-- CUSTOMER TABLE -->

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


        <?php if (mysqli_num_rows($result) > 0) { ?>


            <?php while ($row = mysqli_fetch_assoc($result)) { ?>


                <tr>


                    <!-- PHOTO -->

                    <td>


                        <?php if (!empty($row["liscense"])) { ?>


                            <img
                                src="../User/uploads/licenses/<?php echo htmlspecialchars($row["liscense"]); ?>"
                                class="customer-photo"
                                alt="Customer"
                            >


                        <?php } else { ?>


                            No Photo


                        <?php } ?>


                    </td>


                    <!-- NAME -->

                    <td>

                        <?php
                        echo htmlspecialchars($row["C_name"]);
                        ?>

                    </td>


                    <!-- EMAIL -->

                    <td>

                        <?php
                        echo htmlspecialchars($row["email"]);
                        ?>

                    </td>


                    <!-- PHONE -->

                    <td>

                        <?php
                        echo htmlspecialchars($row["phone"]);
                        ?>

                    </td>


                    <!-- LICENSE -->

                    <td>

                        <?php
                        echo htmlspecialchars($row["liscense_no"]);
                        ?>

                    </td>


                    <!-- STATUS -->

                    <td>


                        <?php if ($row["status"] == "Active") { ?>


                            <span class="status active">

                                Active

                            </span>


                        <?php } else { ?>


                            <span class="status blocked">

                                Blocked

                            </span>


                        <?php } ?>


                    </td>


                    <!-- ACTION -->

                    <td class="actions">


                        <!-- VIEW -->

                        <a
                            href="2.2.1.customerView.php?id=<?php echo $row["Cid"]; ?>"
                            class="view-btn"
                        >
                            View
                        </a>


                        <!-- EDIT -->

                        <a
                            href="2.2.2.editCustomer.php?id=<?php echo $row["Cid"]; ?>"
                            class="edit-btn"
                        >
                            Edit
                        </a>


                        <?php if ($row["status"] == "Active") { ?>


                            <!-- BLOCK -->

                            <a
                                href="2.2.3.actionCustomer.php?action=block&id=<?php echo $row["Cid"]; ?>"
                                class="block-btn"
                                onclick="return confirm('Are you sure you want to block this customer?');"
                            >
                                Block
                            </a>


                        <?php } else { ?>


                            <!-- UNBLOCK -->

                            <a
                                href="2.2.3.actionCustomer.php?action=unblock&id=<?php echo $row["Cid"]; ?>"
                                class="unblock-btn"
                                onclick="return confirm('Are you sure you want to unblock this customer?');"
                            >
                                Unblock
                            </a>


                        <?php } ?>


                    </td>


                </tr>


            <?php } ?>


        <?php } else { ?>


            <tr>

                <td colspan="7">

                    No customers found.

                </td>

            </tr>


        <?php } ?>


        </tbody>


    </table>


</div>


</body>

</html>