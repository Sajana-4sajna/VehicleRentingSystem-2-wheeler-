
<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "vehicle_renting_system");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

/* CHECK CUSTOMER ID */
if (!isset($_GET['id'])) {
    die("Customer ID not found.");
}

$id = (int) $_GET['id'];

/* GET CUSTOMER DATA */
$sql = "SELECT Cid, C_name, email, phone, address, citizenship,
               liscense_no, liscense, username, status
        FROM customer
        WHERE Cid = ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Prepare failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) != 1) {
    die("Customer not found.");
}

$customer = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>View Customer</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            background: #f4f7fe;
            color: #333;
        }

        .customer-page {
            padding: 30px;
        }

        /* PAGE HEADER */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .page-header h1 {
            font-size: 28px;
        }

        .back-btn {
            text-decoration: none;
            background: #333;
            color: white;
            padding: 10px 18px;
            border-radius: 7px;
            font-size: 14px;
        }

        .back-btn:hover {
            opacity: 0.85;
        }

        /* CUSTOMER CARD */
        .customer-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        /* TOP SECTION */
        .customer-top {
            display: flex;
            align-items: center;
            gap: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid #eee;
        }

        .license-photo {
            width: 110px;
            height: 110px;
            border-radius: 10px;
            object-fit: cover;
            border: 1px solid #ddd;
        }

        .customer-name h2 {
            font-size: 23px;
            margin-bottom: 8px;
        }

        .customer-name p {
            color: #777;
            font-size: 14px;
        }

        /* STATUS */
        .status {
            display: inline-block;
            margin-top: 10px;
            padding: 6px 13px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .status.active {
            background: #e7f8ee;
            color: #1b8a4b;
        }

        .status.blocked {
            background: #fdeaea;
            color: #d93030;
        }

        /* DETAILS */
        .details-section {
            margin-top: 25px;
        }

        .details-section h3 {
            font-size: 18px;
            margin-bottom: 18px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px 30px;
        }

        .detail-box {
            padding: 14px;
            background: #f8f9fc;
            border-radius: 8px;
        }

        .detail-box label {
            display: block;
            color: #777;
            font-size: 12px;
            margin-bottom: 6px;
        }

        .detail-box p {
            font-size: 14px;
            font-weight: 500;
            word-break: break-word;
        }

        /* LICENSE SECTION */
        .license-section {
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #eee;
        }

        .license-section h3 {
            margin-bottom: 15px;
            font-size: 18px;
        }

        .license-image {
            max-width: 400px;
            max-height: 250px;
            border-radius: 8px;
            border: 1px solid #ddd;
            object-fit: contain;
        }

        .no-photo {
            color: #888;
            font-size: 14px;
        }

        /* BOTTOM BUTTONS */
        .bottom-actions {
            margin-top: 30px;
            display: flex;
            gap: 10px;
        }

        .edit-btn {
            text-decoration: none;
            background: #fff4df;
            color: #c47a00;
            padding: 10px 18px;
            border-radius: 7px;
            font-size: 14px;
        }

        .bottom-back-btn {
            text-decoration: none;
            background: #333;
            color: white;
            padding: 10px 18px;
            border-radius: 7px;
            font-size: 14px;
        }

        .edit-btn:hover,
        .bottom-back-btn:hover {
            opacity: 0.8;
        }

        /* MOBILE */
        @media (max-width: 700px) {

            .customer-page {
                padding: 20px;
            }

            .page-header {
                align-items: flex-start;
                gap: 15px;
            }

            .details-grid {
                grid-template-columns: 1fr;
            }

            .customer-top {
                flex-direction: column;
                align-items: flex-start;
            }

            .license-photo {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>

<body>

<div class="customer-page">

    <!-- HEADER -->
    <div class="page-header">

        <h1>Customer Details</h1>

        <a href="2.2.customer.php" class="back-btn">
            ← Back
        </a>

    </div>

    <!-- CUSTOMER CARD -->
    <div class="customer-card">

        <!-- TOP INFORMATION -->
        <div class="customer-top">

            <?php if (!empty($customer['liscense'])) { ?>

                <img
                    src="../User/uploads/licenses/<?php echo htmlspecialchars($customer['liscense']); ?>"
                    class="license-photo"
                    alt="Customer License"
                >

            <?php } else { ?>

                <div class="license-photo">
                    No Photo
                </div>

            <?php } ?>

            <div class="customer-name">

                <h2>
                    <?php echo htmlspecialchars($customer['C_name']); ?>
                </h2>

                <p>
                    Customer ID:
                    <?php echo htmlspecialchars($customer['Cid']); ?>
                </p>

                <?php if ($customer['status'] == "Active") { ?>

                    <span class="status active">
                        Active
                    </span>

                <?php } else { ?>

                    <span class="status blocked">
                        Blocked
                    </span>

                <?php } ?>

            </div>

        </div>

        <!-- PERSONAL INFORMATION -->
        <div class="details-section">

            <h3>Personal Information</h3>

            <div class="details-grid">

                <div class="detail-box">
                    <label>Full Name</label>
                    <p>
                        <?php echo htmlspecialchars($customer['C_name']); ?>
                    </p>
                </div>

                <div class="detail-box">
                    <label>Email</label>
                    <p>
                        <?php echo htmlspecialchars($customer['email']); ?>
                    </p>
                </div>

                <div class="detail-box">
                    <label>Phone</label>
                    <p>
                        <?php echo htmlspecialchars($customer['phone']); ?>
                    </p>
                </div>

                <div class="detail-box">
                    <label>Address</label>
                    <p>
                        <?php echo htmlspecialchars($customer['address']); ?>
                    </p>
                </div>

                <div class="detail-box">
                    <label>Citizenship Number</label>
                    <p>
                        <?php echo htmlspecialchars($customer['citizenship']); ?>
                    </p>
                </div>

                <div class="detail-box">
                    <label>Username</label>
                    <p>
                        <?php echo htmlspecialchars($customer['username']); ?>
                    </p>
                </div>

            </div>

        </div>

        <!-- LICENSE INFORMATION -->
        <div class="license-section">

            <h3>Driving License Information</h3>

            <div class="details-grid">

                <div class="detail-box">

                    <label>License Number</label>

                    <p>
                        <?php echo htmlspecialchars($customer['liscense_no']); ?>
                    </p>

                </div>

                <div class="detail-box">

                    <label>Account Status</label>

                    <p>
                        <?php echo htmlspecialchars($customer['status']); ?>
                    </p>

                </div>

            </div>

            <br>

            <label>License Photo</label>

            <br><br>

            <?php if (!empty($customer['liscense'])) { ?>

                <img
                    src="../User/uploads/licenses/<?php echo htmlspecialchars($customer['liscense']); ?>"
                    class="license-image"
                    alt="Driving License"
                >

            <?php } else { ?>

                <p class="no-photo">
                    License photo not available.
                </p>

            <?php } ?>

        </div>

        <!-- BUTTONS -->
        <div class="bottom-actions">

            <a
                href="2.2.2.editCustomer.php?id=<?php echo $customer['Cid']; ?>"
                class="edit-btn"
            >
                Edit Customer
            </a>

            <a
                href="2.2.customer.php"
                class="bottom-back-btn"
            >
                Back to Customers
            </a>

        </div>

    </div>

</div>

</body>
</html>

