<?php

$conn = mysqli_connect("localhost", "root", "", "vehicle_renting_system");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}


/* =========================================
   CHECK VEHICLE ID
========================================= */

if (!isset($_GET['id'])) {
    header("Location: 2.3.vehicleSlidebar.php");
    exit();
}

$id = (int) $_GET['id'];


/* =========================================
   GET EXISTING VEHICLE
========================================= */

$sql = "SELECT * FROM vehicle WHERE Vid = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


/* Check vehicle exists */

if (mysqli_num_rows($result) == 0) {
    echo "Vehicle not found.";
    exit();
}

$row = mysqli_fetch_assoc($result);


/* =========================================
   UPDATE VEHICLE
========================================= */

if (isset($_POST['update'])) {

    $name = $_POST['name'];
    $brand = $_POST['brand'];
    $Vno = $_POST['Vno'];
    $type = $_POST['type'];
    $category = $_POST['category'];
    $model = $_POST['model'];
    $price_per_day = $_POST['price_per_day'];
    $status = $_POST['status'];

    /* Existing image */

    $image = $row['image'];


    /* =====================================
       IMAGE UPLOAD
    ===================================== */

    if (!empty($_FILES['image']['name'])) {

        $image_name = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];

        $image_ext = strtolower(
            pathinfo($image_name, PATHINFO_EXTENSION)
        );

        $allowed_extensions = array("jpg", "jpeg", "png", "webp");

        if (!in_array($image_ext, $allowed_extensions)) {

            echo "<script>
                    alert('Only JPG, JPEG, PNG and WEBP images are allowed.');
                  </script>";

        } else {

            /* Create unique image name */

            $new_image_name = time() . "_" . $image_name;

            $upload_path = "../User/uploads/vehicles/" . $new_image_name;


            /* Move image */

            if (move_uploaded_file($image_tmp, $upload_path)) {

                $image = $new_image_name;

            } else {

                echo "<script>
                        alert('Image upload failed.');
                      </script>";
            }
        }
    }


    /* =====================================
       UPDATE DATABASE
    ===================================== */

    $update_sql = "UPDATE vehicle SET
                    name = ?,
                    brand = ?,
                    Vno = ?,
                    type = ?,
                    category = ?,
                    model = ?,
                    price_per_day = ?,
                    status = ?,
                    image = ?
                   WHERE Vid = ?";

    $update_stmt = mysqli_prepare($conn, $update_sql);


    mysqli_stmt_bind_param(
        $update_stmt,
        "sssssisssi",
        $name,
        $brand,
        $Vno,
        $type,
        $category,
        $model,
        $price_per_day,
        $status,
        $image,
        $id
    );


    if (mysqli_stmt_execute($update_stmt)) {

        header("Location: 2.3.2.viewVehicle.php?id=" . $id);
        exit();

    } else {

        echo "Error updating vehicle: " . mysqli_error($conn);
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Vehicle</title>

    <link rel="stylesheet" href="../Assests/2.3.3.editVehicle.css">

</head>


<body>


<div class="container">


    <!-- ==============================
         PAGE HEADER
    =============================== -->

    <div class="page-header">

        <h1>Edit Vehicle</h1>

        <a href="2.3.vehicleSlidebar.php" class="back-btn">
            ← Back to Vehicles
        </a>

    </div>



    <!-- ==============================
         EDIT FORM
    =============================== -->

    <div class="form-card">


        <form method="POST" enctype="multipart/form-data">


            <!-- Vehicle Name -->

            <div class="form-group">

                <label>Vehicle Name</label>

                <input
                    type="text"
                    name="name"
                    value="<?php echo htmlspecialchars($row['name']); ?>"
                    required
                >

            </div>



            <!-- Brand -->

            <div class="form-group">

                <label>Brand</label>

                <input
                    type="text"
                    name="brand"
                    value="<?php echo htmlspecialchars($row['brand']); ?>"
                    required
                >

            </div>



            <!-- Vehicle Number -->

            <div class="form-group">

                <label>Vehicle Number</label>

                <input
                    type="text"
                    name="Vno"
                    value="<?php echo htmlspecialchars($row['Vno']); ?>"
                    required
                >

            </div>



            <!-- Type -->

            <div class="form-group">

                <label>Type</label>

                <select name="type" required>

                    <option value="Bike"
                        <?php if ($row['type'] == 'Bike') echo 'selected'; ?>>
                        Bike
                    </option>

                    <option value="Scooter"
                        <?php if ($row['type'] == 'Scooter') echo 'selected'; ?>>
                        Scooter
                    </option>

                 
                </select>

            </div>



            <!-- Category -->

            <div class="form-group">

                <label>Category</label>

                <select name="category" required>

                    <option value="Sports"
                        <?php if ($row['category'] == 'Sports') echo 'selected'; ?>>
                        Sports
                    </option>

                    <option value="Cruiser-Classic"
                        <?php if ($row['category'] == 'Cruiser-Classic') echo 'selected'; ?>>
                        Cruiser / Classic
                    </option>

                    <option value="Naked-Street"
                        <?php if ($row['category'] == 'Naked-Street') echo 'selected'; ?>>
                        Naked / Street
                    </option>

                  
                </select>

            </div>



            <!-- Model -->

            <div class="form-group">

                <label>Model Year</label>

                <input
                    type="number"
                    name="model"
                    value="<?php echo htmlspecialchars($row['model']); ?>"
                    required
                >

            </div>



            <!-- Price -->

            <div class="form-group">

                <label>Price Per Day</label>

                <input
                    type="number"
                    name="price_per_day"
                    value="<?php echo htmlspecialchars($row['price_per_day']); ?>"
                    required
                >

            </div>



            <!-- Status -->

            <div class="form-group">

                <label>Status</label>

                <select name="status" required>

                    <option value="Available"
                        <?php if ($row['status'] == 'Available') echo 'selected'; ?>>
                        Available
                    </option>

                    <option value="Rented"
                        <?php if ($row['status'] == 'Rented') echo 'selected'; ?>>
                        Rented
                    </option>

                    <option value="Unavailable"
                        <?php if ($row['status'] == 'Unavailable') echo 'selected'; ?>>
                        Unavailable
                    </option>

                </select>

            </div>



            <!-- Existing Image -->

            <div class="form-group">

                <label>Current Image</label>

                <?php if (!empty($row['image'])) { ?>

                    <div class="current-image">

                        <img
                            src="../User/uploads/vehicles/<?php echo htmlspecialchars($row['image']); ?>"
                            alt="Vehicle Image"
                        >

                    </div>

                <?php } else { ?>

                    <p>No image uploaded.</p>

                <?php } ?>

            </div>



            <!-- New Image -->

            <div class="form-group">

                <label>Change Image</label>

                <input
                    type="file"
                    name="image"
                    accept=".jpg,.jpeg,.png,.webp"
                >

                <small>
                    Leave empty if you don't want to change the image.
                </small>

            </div>



            <!-- Buttons -->

            <div class="buttons">

                <a
                    href="2.3.2.viewVehicle.php?id=<?php echo $id; ?>"
                    class="cancel-btn"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    name="update"
                    class="update-btn"
                >
                    Update Vehicle
                </button>

            </div>


        </form>

    </div>

</div>


</body>

</html>