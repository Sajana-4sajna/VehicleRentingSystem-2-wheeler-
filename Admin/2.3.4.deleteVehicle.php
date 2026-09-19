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
   GET VEHICLE IMAGE
========================================= */

$sql = "SELECT image FROM vehicle WHERE Vid = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


/* =========================================
   CHECK VEHICLE EXISTS
========================================= */

if (mysqli_num_rows($result) == 0) {
    header("Location: 2.3.vehicleSlidebar.php");
    exit();
}

$row = mysqli_fetch_assoc($result);

$image = $row['image'];


/* =========================================
   DELETE VEHICLE
========================================= */

$delete_sql = "DELETE FROM vehicle WHERE Vid = ?";

$delete_stmt = mysqli_prepare($conn, $delete_sql);

mysqli_stmt_bind_param($delete_stmt, "i", $id);


if (mysqli_stmt_execute($delete_stmt)) {

    /* =====================================
       DELETE VEHICLE IMAGE
    ===================================== */

    if (!empty($image)) {

        $image_path = "../User/uploads/vehicles/" . $image;

        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }


    /* =====================================
       REDIRECT
    ===================================== */

    header("Location: 2.3.vehicleSlidebar.php");
    exit();

} else {

    echo "Error deleting vehicle: " . mysqli_error($conn);
}

?>