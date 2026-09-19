<?php

session_start();
include("db.php");

if (!isset($_SESSION['Cid'])) {

    header("Location: login.php");
    exit();

}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    header("Location: vehicles.php");
    exit();

}

$vehicle_id = intval($_GET['id']);

$sql = "SELECT * FROM vehicles WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $vehicle_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$vehicle = mysqli_fetch_assoc($result);

if (!$vehicle) {

    die("Vehicle not found.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $booking_date = $_POST['booking_date'];
    $return_date = $_POST['return_date'];
    $payment_method = $_POST['payment_method'];

    $start = strtotime($booking_date);
    $end = strtotime($return_date);

    $days = ($end - $start) / (60 * 60 * 24);

    if ($days <= 0) {

        echo "<script>
        alert('Return date must be after booking date.');
        </script>";

    } else {

        $total_amount = $days * $vehicle['price'];

        $user_id = $_SESSION['user_id'];

        $insert = "INSERT INTO booking
        (user_id, vehicle_id, booking_date, return_date, total_amount, payment_method, status)
        VALUES (?, ?, ?, ?, ?,?, 'Pending')";

        $insert_stmt = mysqli_prepare($conn, $insert);

        mysqli_stmt_bind_param(
            $insert_stmt,
            "iissds",
            $user_id,
            $vehicle_id,
            $booking_date,
            $return_date,
            $total_amount,
            $payment_method
        );

        if (mysqli_stmt_execute($insert_stmt)) {
            echo "<script>
            alert('Booking Successful!');
            window.location='my_bookings.php';
            </script>";

            exit();

        } else {

            echo "Booking Error: " .
                 mysqli_error($conn);

        }

    }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Vehicle</title>
    <style>
        body{
            font-family:Arial;
            background:#f2f2f2;
        }

        .container{
            width:450px;
            margin:40px auto;
            background:white;
            padding:20px;
            border-radius:10px;
            box-shadow:0px 0px 10px gray;
        }

        img{
            width:100%;
            height:220px;
            object-fit:cover;
            border-radius:10px;
        }

        input{
            width:100%;
            padding:10px;
            margin:10px 0;
        }

        button{
            width:100%;
            padding:12px;
            background:green;
            color:white;
            border:none;
            cursor:pointer;
        }
    </style>
</head>

<body>
    <div class="booking-container">

        <h2>Book Vehicle</h2>

        <img src="../assets/images/<?php echo htmlspecialchars($vehicle['image']); ?>">

        <h3>
            <?php echo htmlspecialchars($vehicle['vehicle_name']); ?>
        </h3>

        <p>
            <strong>Brand:</strong>
            <?php echo htmlspecialchars($vehicle['brand']); ?>
        </p>

        <p>
            <strong>Model:</strong>
            <?php echo htmlspecialchars($vehicle['model']); ?>
        </p>

        <p>
            <strong>Price:</strong>
            Rs. <?php echo htmlspecialchars($vehicle['price']); ?> / Day
        </p>


        <form method="POST" onsubmit="return validateBooking();">
            <label>Booking Date</label>
            <input type="date" id="booking_date" name="booking_date" placeholder="Enter your booking date">

            <label>Return Date</label>
            <input type="date" id="return_date" name="return_date" placeholder="Enter your return date">

            <label>Payment Method</label>
            <select id="payment_method" name="payment_method" required>
                <option value="">Select Payment Method</option>
                <option value="eSewa">eSewa</option>
                <option value="Bank Transfer">Bank Transfer</option>
            </select>

            <p>
                <strong>Total Days:</strong>
                <span id="total_days">0</span>
            </p>

            <p>
                <strong>Total Amount:</strong>
                Rs. <span id="total_amount">0</span>
            </p>

            <button type="submit" name="book">
                Confirm Booking
            </button>
        </form>
    </div>

    <script>
        const price = <?php echo $vehicle['price']; ?>;
        const bookingDate = document.getElementById("booking_date");
        const returnDate = document.getElementById("return_date");
        const totalDays = document.getElementById("total_days");
        const totalAmount = document.getElementById("total_amount");
        const paymentMethod = document.getElementById("payment_method");

        function calculateTotal() {
            if (bookingDate.value && returnDate.value) {
                const start = new Date(bookingDate.value);
                const end = new Date(returnDate.value);
                const difference = end - start;
                const days = difference / (1000 * 60 * 60 * 24);

                if (days > 0) {
                    totalDays.textContent = days;
                    totalAmount.textContent = (days * price).toFixed(2);
                } else {
                    totalDays.textContent = "0";
                    totalAmount.textContent = "0";
                }
            }
        }

        bookingDate.addEventListener("change", calculateTotal);

        returnDate.addEventListener("change", calculateTotal);

        function validateBooking() {
            if (!bookingDate.value || !returnDate.value) {
                alert("Please select both dates.");
                return false;
            }

            const today = new Date();
            today.setHours(0,0,0,0);
            const start = new Date(bookingDate.value);
            const end = new Date(returnDate.value);

            if (start < today) {
                alert(
                    "Booking date cannot be in the past."
                );
                return false;
            }

            if (end <= start) {
                alert(
                    "Return date must be after booking date."
                );
                return false;
            }
            return true;
        }
    </script>

</body>
</html>