
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

/* GET EXISTING CUSTOMER DATA */
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

/* MESSAGE */
$message = "";
$message_type = "";

/* UPDATE CUSTOMER */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['C_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $citizenship = trim($_POST['citizenship']);
    $license_no = trim($_POST['liscense_no']);
    $username = trim($_POST['username']);
    $status = $_POST['status'];

    /* BASIC VALIDATION */
    if (
        $name == "" ||
        $email == "" ||
        $phone == "" ||
        $address == "" ||
        $citizenship == "" ||
        $license_no == "" ||
        $username == ""
    ) {

        $message = "Please fill in all required fields.";
        $message_type = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    } else {

        /* CHECK DUPLICATE USERNAME */
        $check_sql = "SELECT Cid FROM customer
                      WHERE username = ? AND Cid != ?";

        $check_stmt = mysqli_prepare($conn, $check_sql);

        mysqli_stmt_bind_param(
            $check_stmt,
            "si",
            $username,
            $id
        );

        mysqli_stmt_execute($check_stmt);

        $check_result = mysqli_stmt_get_result($check_stmt);

        if (mysqli_num_rows($check_result) > 0) {

            $message = "Username already exists.";
            $message_type = "error";

        } else {

            /* UPDATE WITHOUT NEW LICENSE PHOTO */
            $update_sql = "UPDATE customer SET
                            C_name = ?,
                            email = ?,
                            phone = ?,
                            address = ?,
                            citizenship = ?,
                            liscense_no = ?,
                            username = ?,
                            status = ?
                           WHERE Cid = ?";

            $update_stmt = mysqli_prepare($conn, $update_sql);

            if (!$update_stmt) {
                die("Prepare failed: " . mysqli_error($conn));
            }

            mysqli_stmt_bind_param(
                $update_stmt,
                "ssssssssi",
                $name,
                $email,
                $phone,
                $address,
                $citizenship,
                $license_no,
                $username,
                $status,
                $id
            );

            if (mysqli_stmt_execute($update_stmt)) {
                

                /*
                 * CHECK WHETHER NEW LICENSE PHOTO WAS UPLOADED
                 */
                if (
                    isset($_FILES['license_photo']) &&
                    $_FILES['license_photo']['error'] == 0
                ) {

                    $file_name = $_FILES['license_photo']['name'];
                    $file_tmp = $_FILES['license_photo']['tmp_name'];
                    $file_size = $_FILES['license_photo']['size'];

                    $allowed_extensions = [
                        "jpg",
                        "jpeg",
                        "png"
                    ];

                    $file_extension = strtolower(
                        pathinfo(
                            $file_name,
                            PATHINFO_EXTENSION
                        )
                    );

                    if (!in_array($file_extension, $allowed_extensions)) {

                        $message = "Only JPG, JPEG and PNG files are allowed.";
                        $message_type = "error";

                    } elseif ($file_size > 5 * 1024 * 1024) {

                        $message = "License photo must be less than 5MB.";
                        $message_type = "error";

                    } else {

                        $upload_folder = "../User/uploads/licenses/";

                        if (!is_dir($upload_folder)) {
                            mkdir($upload_folder, 0777, true);
                        }

                        $new_file_name =
                            uniqid("license_", true) .
                            "." .
                            $file_extension;

                        $upload_path =
                            $upload_folder .
                            $new_file_name;

                        if (move_uploaded_file(
                            $file_tmp,
                            $upload_path
                        )) {

                            /* DELETE OLD PHOTO */
                            if (!empty($customer['liscense'])) {

                                $old_photo =
                                    $upload_folder .
                                    $customer['liscense'];

                                if (file_exists($old_photo)) {
                                    unlink($old_photo);
                                }
                            }

                            /* UPDATE NEW PHOTO NAME */
                            $photo_sql = "UPDATE customer
                                          SET liscense = ?
                                          WHERE Cid = ?";

                            $photo_stmt =
                                mysqli_prepare(
                                    $conn,
                                    $photo_sql
                                );

                            mysqli_stmt_bind_param(
                                $photo_stmt,
                                "si",
                                $new_file_name,
                                $id
                            );

                            mysqli_stmt_execute(
                                $photo_stmt
                            );

                            mysqli_stmt_close(
                                $photo_stmt
                            );
                        }
                    }
                }

                if ($message_type != "error") {

                    $message =
                        "Customer updated successfully.";

                    $message_type = "success";

                    /*
                     * GET UPDATED DATA
                     */
                    $sql = "SELECT Cid, C_name, email, phone,
                                   address, citizenship,
                                   liscense_no, liscense,
                                   username, status
                            FROM customer
                            WHERE Cid = ?";

                    $stmt = mysqli_prepare(
                        $conn,
                        $sql
                    );

                    mysqli_stmt_bind_param(
                        $stmt,
                        "i",
                        $id
                    );

                    mysqli_stmt_execute(
                        $stmt
                    );

                    $result =
                        mysqli_stmt_get_result($stmt);

                    $customer =
                        mysqli_fetch_assoc($result);

                    mysqli_stmt_close($stmt);
                }
            } else {

                $message =
                    "Update failed: " .
                    mysqli_error($conn);

                $message_type = "error";
            }

            mysqli_stmt_close($update_stmt);
        }

        mysqli_stmt_close($check_stmt);
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

    <title>Edit Customer</title>

    <link rel="stylesheet" href="../Assests/2.2.2.editCustomer.css">

</head>

<body>

<div class="edit-page">

    <!-- HEADER -->

    <div class="page-header">

        <h1>Edit Customer</h1>

        <a
            href="2.2.customer.php"
            class="back-btn"
        >
            ← Back
        </a>

    </div>

    <!-- CARD -->

    <div class="edit-card">

        <?php if ($message != "") { ?>

            <div
                class="message <?php echo $message_type; ?>"
            >
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php } ?>

        <form
            action="2.2.2.editCustomer.php?id=<?php echo $customer['Cid']; ?>"
            method="POST"
            enctype="multipart/form-data"
        >

            <div class="form-grid">

                <!-- FULL NAME -->

                <div class="form-group">

                    <label>Full Name</label>

                    <input
                        type="text"
                        name="C_name"
                        value="<?php echo htmlspecialchars($customer['C_name']); ?>"
                        required
                    >

                </div>

                <!-- EMAIL -->

                <div class="form-group">

                    <label>Email</label>

                    <input
                        type="email"
                        name="email"
                        value="<?php echo htmlspecialchars($customer['email']); ?>"
                        required
                    >

                </div>

                <!-- PHONE -->

                <div class="form-group">

                    <label>Phone</label>

                    <input
                        type="text"
                        name="phone"
                        value="<?php echo htmlspecialchars($customer['phone']); ?>"
                        required
                    >

                </div>

                <!-- CITIZENSHIP -->

                <div class="form-group">

                    <label>Citizenship Number</label>

                    <input
                        type="text"
                        name="citizenship"
                        value="<?php echo htmlspecialchars($customer['citizenship']); ?>"
                        required
                    >

                </div>

                <!-- LICENSE NUMBER -->

                <div class="form-group">

                    <label>License Number</label>

                    <input
                        type="text"
                        name="liscense_no"
                        value="<?php echo htmlspecialchars($customer['liscense_no']); ?>"
                        required
                    >

                </div>

                <!-- USERNAME -->

                <div class="form-group">

                    <label>Username</label>

                    <input
                        type="text"
                        name="username"
                        value="<?php echo htmlspecialchars($customer['username']); ?>"
                        required
                    >

                </div>

                <!-- ADDRESS -->

                <div class="form-group full">

                    <label>Address</label>

                    <textarea
                        name="address"
                        required
                    ><?php echo htmlspecialchars($customer['address']); ?></textarea>

                </div>

                <!-- STATUS -->

                <div class="form-group">

                    <label>Status</label>

                    <select name="status">

                        <option
                            value="Active"
                            <?php echo ($customer['status'] == "Active") ? "selected" : ""; ?>
                        >
                            Active
                        </option>

                        <option
                            value="Blocked"
                            <?php echo ($customer['status'] == "Blocked") ? "selected" : ""; ?>
                        >
                            Blocked
                        </option>

                    </select>

                </div>

                <!-- LICENSE PHOTO -->

                <div class="form-group">

                    <label>License Photo</label>

                    <input
                        type="file"
                        name="license_photo"
                        accept=".jpg,.jpeg,.png"
                    >

                    <?php if (!empty($customer['liscense'])) { ?>

                        <div class="current-photo">

                            <img
                                src="../User/uploads/licenses/<?php echo htmlspecialchars($customer['liscense']); ?>"
                                alt="Current License"
                            >

                        </div>

                    <?php } ?>

                </div>

            </div>

            <!-- BUTTONS -->

            <div class="form-actions">

                <button
                    type="submit"
                    class="update-btn"
                >
                    Update Customer
                </button>

                <a
                    href="2.2.customer.php"
                    class="cancel-btn"
                >
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

</body>

</html>
