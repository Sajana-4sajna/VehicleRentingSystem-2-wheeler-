<?php

$conn = mysqli_connect("localhost", "root", "", "vehicle_renting_system");

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

?>