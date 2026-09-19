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


/* CHECK ACTION AND ID */

if (
    isset($_GET["action"]) &&
    isset($_GET["id"])
) {


    $action = $_GET["action"];

    $id = (int) $_GET["id"];


    /* BLOCK */

    if ($action == "block") {

        $sql = "UPDATE customer
                SET status = 'Blocked'
                WHERE Cid = ?";

    }


    /* UNBLOCK */

    elseif ($action == "unblock") {

        $sql = "UPDATE customer
                SET status = 'Active'
                WHERE Cid = ?";

    }


    /* INVALID ACTION */

    else {

        die("Invalid action.");

    }


    /* PREPARE QUERY */

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {

        die(
            "Prepare failed: "
            . mysqli_error($conn)
        );

    }


    /* BIND CUSTOMER ID */

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $id
    );


    /* EXECUTE */

    if (mysqli_stmt_execute($stmt)) {


        /* GO BACK TO CUSTOMER PAGE */

        header(
            "Location: 2.2.customer.php"
        );

        exit();


    } else {


        die(
            "Status update failed: "
            . mysqli_stmt_error($stmt)
        );

    }


} else {


    die("Invalid request.");

}

?>