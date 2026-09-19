<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "vehicle_renting_system"
);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}


/* =========================
   VARIABLES
========================= */

$vehicle_name = "";
$brand = "";
$vehicle_number = "";
$type = "";
$category = "";
$model_year = "";
$price_per_day = "";
$status = "Available";

$error = "";


/* =========================
   FORM SUBMITTED
========================= */

if (isset($_POST["add_vehicle"])) {

    $vehicle_name = trim($_POST["vehicle_name"]);
    $brand = trim($_POST["brand"]);
    $vehicle_number = trim($_POST["vehicle_number"]);
    $type = trim($_POST["type"]);
    $category = trim($_POST["category"]);
    $model_year = trim($_POST["model_year"]);
    $price_per_day = trim($_POST["price_per_day"]);
    $status = trim($_POST["status"]);


    /* =========================
       VALIDATION
    ========================= */

    if (
        $vehicle_name == "" ||
        $brand == "" ||
        $vehicle_number == "" ||
        $type == "" ||
        $category == "" ||
        $model_year == "" ||
        $price_per_day == "" ||
        $status == ""
    ) {

        $error = "Please fill in all fields.";

    } elseif (!is_numeric($model_year)) {

        $error = "Model year must be a number.";

    } elseif (!is_numeric($price_per_day)) {

        $error = "Price per day must be a number.";

    } else {


        /* =========================
           IMAGE UPLOAD
        ========================= */

        $image_name = "";

        if (isset($_FILES["image"]) &&
            $_FILES["image"]["error"] == 0) {


            $image_name = $_FILES["image"]["name"];

            $image_tmp = $_FILES["image"]["tmp_name"];

            $image_size = $_FILES["image"]["size"];


            /* Allowed extensions */

            $allowed_extensions = [
                "jpg",
                "jpeg",
                "png",
                "webp"
            ];


            $image_extension = strtolower(
                pathinfo(
                    $image_name,
                    PATHINFO_EXTENSION
                )
            );


            /* Check extension */

            if (!in_array(
                $image_extension,
                $allowed_extensions
            )) {

                $error =
                    "Only JPG, JPEG, PNG and WEBP images are allowed.";

            }


            /* Check image size */

            elseif ($image_size > 5 * 1024 * 1024) {

                $error =
                    "Image size must be less than 5 MB.";

            }


            else {

                /* Create unique image name */

                $new_image_name =
                    time() . "_" . basename($image_name);


                $upload_folder =
                    "../User/uploads/vehicles/";


                /* Create folder if it doesn't exist */

                if (!is_dir($upload_folder)) {

                    mkdir(
                        $upload_folder,
                        0777,
                        true
                    );

                }


                $image_path =
                    $upload_folder . $new_image_name;


                if (!move_uploaded_file(
                    $image_tmp,
                    $image_path
                )) {

                    $error =
                        "Failed to upload image.";

                } else {

                    $image_name =
                        $new_image_name;

                }

            }

        }


        /* =========================
           INSERT DATA
        ========================= */

        if ($error == "") {


            $sql = "INSERT INTO vehicle
                    (
                        name,
                        brand,
                        Vno,
                        type,
                        category,
                        model,
                        price_per_day,
                        status,
                        image
                    )
                    VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?)";


            $stmt = mysqli_prepare(
                $conn,
                $sql
            );


            if (!$stmt) {

                die(
                    "Prepare failed: "
                    . mysqli_error($conn)
                );

            }


            mysqli_stmt_bind_param(
                $stmt,
                "sssssisss",
                $vehicle_name,
                $brand,
                $vehicle_number,
                $type,
                $category,
                $model_year,
                $price_per_day,
                $status,
                $image_name
            );


            if (mysqli_stmt_execute($stmt)) {

                header(
                    "Location: 2.3.vehicleSlidebar.php"
                );

                exit();

            } else {

                $error =
                    "Vehicle could not be added: "
                    . mysqli_stmt_error($stmt);

            }


            mysqli_stmt_close($stmt);

        }

    }

}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Add Vehicle</title>


    <link rel="stylesheet"
          href="../Assests/2.3.1.addVehicle.css">

</head>


<body>


<div class="container">


    <!-- =========================
         PAGE HEADER
    ========================== -->

    <div class="page-header">

        <div>

            <h1>Add Vehicle</h1>

            <p>
                Add a new vehicle to the rental system.
            </p>

        </div>


        <a href="2.3.vehicleSlidebar.php"
           class="back-btn">

            ← Back to Vehicles

        </a>

    </div>



    <!-- =========================
         FORM CONTAINER
    ========================== -->

    <div class="form-container">


        <?php if ($error != "") { ?>

            <div class="error-message">

                <?php echo htmlspecialchars($error); ?>

            </div>

        <?php } ?>


        <form
            method="POST"  action="" enctype="multipart/form-data"
        >


            <!-- VEHICLE NAME -->

            <div class="form-group">

                <label>
                    Vehicle Name
                </label>

                <input
                    type="text"
                    name="vehicle_name"
                    placeholder="Enter vehicle name"
                    value="<?php
                    echo htmlspecialchars($vehicle_name);
                    ?>"
                    required
                >

            </div>



            <!-- BRAND -->

            <div class="form-group">

                <label>
                    Brand
                </label>

                <input
                    type="text"
                    name="brand"
                    placeholder="Enter brand"
                    value="<?php
                    echo htmlspecialchars($brand);
                    ?>"
                    required
                >

            </div>



            <!-- VEHICLE NUMBER -->

            <div class="form-group">

                <label>
                    Vehicle Number
                </label>

                <input
                    type="text"
                    name="vehicle_number"
                    placeholder="e.g. BA 12 PA 3456"
                    value="<?php
                    echo htmlspecialchars($vehicle_number);
                    ?>"
                    required
                >

            </div>



            <!-- TYPE -->

            <div class="form-group">

                <label>
                    Vehicle Type
                </label>

                <select
                    name="type"
                    required
                >

                    <option value="">
                        Select Type
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

            </div>



            <!-- CATEGORY -->

            <div class="form-group">

                <label>
                    Category
                </label>

                <select
                    name="category"
                    required
                >

                    <option value="">
                        Select Category
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

                 
                   
                </select>

            </div>



            <!-- MODEL YEAR -->

            <div class="form-group">

                <label>
                    Model Year
                </label>

                <input
                    type="number"
                    name="model_year"
                    placeholder="e.g. 2024"
                    value="<?php
                    echo htmlspecialchars($model_year);
                    ?>"
                    min="1900"
                    max="2100"
                    required
                >

            </div>



            <!-- PRICE -->

            <div class="form-group">

                <label>
                    Price Per Day
                </label>

                <input
                    type="number"
                    name="price_per_day"
                    placeholder="e.g. 2000"
                    value="<?php
                    echo htmlspecialchars($price_per_day);
                    ?>"
                    min="0"
                    step="0.01"
                    required
                >

            </div>



            <!-- STATUS -->

            <div class="form-group">

                <label>
                    Status
                </label>

                <select
                    name="status"
                    required
                >

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

            </div>



            <!-- IMAGE -->

            <div class="form-group">

                <label>
                    Vehicle Image
                </label>

                <input
                    type="file"
                    name="image"
                    accept=".jpg,.jpeg,.png,.webp"
                >

                <small>
                    JPG, JPEG, PNG or WEBP. Maximum 5 MB.
                </small>

            </div>



            <!-- BUTTONS -->

            <div class="form-buttons">

                <a
                    href="2.3.vehicleSlidebar.php"
                    class="cancel-btn"
                >
                    Cancel
                </a>


                <button
                    type="submit"
                    name="add_vehicle"
                    class="submit-btn"
                >
                    Add Vehicle
                </button>

            </div>


        </form>


    </div>


</div>


</body>

</html>