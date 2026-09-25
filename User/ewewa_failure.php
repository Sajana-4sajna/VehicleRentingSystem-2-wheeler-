<?php
session_start();

if (!isset($_SESSION['Cid'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payment Failed</title>
</head>
<body>
    <div style=" width:450px; margin:100px auto; text-align:center; padding:30px; border:1px solid #ccc; border-radius:10px;">
        <h2>Payment Failed</h2>
        <p>
            Your eSewa payment was not completed.
        </p>
        <br>
        <a href="my_bookings.php">
            Try Again
        </a>
    </div>
</body>
</html>
