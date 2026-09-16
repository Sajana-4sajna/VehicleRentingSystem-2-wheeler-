<?php
include("db.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST"){
        // Get form data
        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['email']);
        $phone_number = trim($_POST['phone_number']);
        $address = trim($_POST['address']);
        $citizenship_number = trim($_POST['citizenship_number']);
        $license_number = trim($_POST['license_number']);
        // $pickup_location = trim($_POST['pickup_location']);
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        // Check password
        if ($password !== $confirm_password) {
                $message = "Passwords do not match!";
        } 
        else{
                // Check license photo
                if (!isset($_FILES['license_photo']) ||
                    $_FILES['license_photo']['error'] != 0) {
                        $message = "Please upload your license photo.";

                } 
                else {
                    // Get uploaded file information
                    $file_name = $_FILES['license_photo']['name'];
                    $file_tmp = $_FILES['license_photo']['tmp_name'];
                    $file_size = $_FILES['license_photo']['size'];
                    // Allowed file extensions
                    $allowed_extensions = ['jpg', 'jpeg', 'png'];
                    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    // Check file type
                    if (!in_array($file_extension, $allowed_extensions)) {
                        $message ="Only JPG, JPEG and PNG files are allowed.";
                    } 
                    elseif ($file_size > 5 * 1024 * 1024) {
                        $message ="License photo must be less than 5MB.";
                    } 
                    else {
                        // Check username
                        $check = "SELECT Cid FROM customer WHERE username = ?";
                        $stmt =mysqli_prepare($conn, $check);
                        mysqli_stmt_bind_param($stmt, "s", $username);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        if (mysqli_num_rows($result) > 0) {
                            $message ="Username already exists!";
                        } 
                        else {
                            // Create upload folder
                            $upload_folder = "uploads/licenses/";
                            if (!is_dir($upload_folder)) {
                                mkdir($upload_folder, 0777, true);
                            }
                            // Create unique file name
                            $new_file_name = uniqid("license_", true). ".". $file_extension;
                            $license_photo = $new_file_name;
                            $upload_path = $upload_folder. $new_file_name;
                            // Move uploaded photo
                            if (move_uploaded_file($file_tmp, $upload_path)) {
                                // Hash password
                                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                                // Insert user
                                $sql =
                                "INSERT INTO customer
                                    (
                                        C_name,
                                        email,
                                        phone,
                                        address,
                                        citizenship,
                                        liscense_no,
                                        liscense,
                            
                                        username,
                                        password
                                    )
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)";
                                    $insert_stmt = mysqli_prepare($conn, $sql);

                                    if (!$insert_stmt) {
                                        die("Prepare Failed: " . mysqli_error($conn));
                                    }
                                    mysqli_stmt_bind_param(
                                        $insert_stmt,
                                        "sssssssss",
                                        $fullname,
                                        $email,
                                        $phone_number,
                                        $address,
                                        $citizenship_number,
                                        $license_number,
                                        $license_photo,
                                       
                                        $username,
                                        $hashedPassword
                                    );


                                if (
                                    mysqli_stmt_execute(
                                        $insert_stmt
                                    )
                                ) {
                                    echo "
                                    <script>
                                        alert(
                                            'Registration Successful!'
                                        );
                                        window.location =
                                            'login.php';
                                    </script>
                                    ";
                                    exit();
                                } 
                                else {
                                    $message ="Registration Failed: " . mysqli_error($conn);
                                }
                                mysqli_stmt_close($insert_stmt);
                            } 
                            else {
                                $message = "Failed to upload license photo.";
                            }
                        }
                        mysqli_stmt_close($stmt);
                    }
                }
        }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <link rel="stylesheet" href="../Assests/style1.css">
</head>
<body>
    <div class="form-container">
        <h2>Create Account</h2>
        <!-- Display error message -->
        <?php if ($message != "") { ?>
            <div class="error-message">
                <?php
                echo htmlspecialchars($message);
                ?>
            </div>
        <?php } ?>
        <form method="POST" action="register.php" enctype="multipart/form-data" onsubmit="return validateRegistration();">
            <label>Full Name</label>
            <input type="text" id="fullname" name="fullname" placeholder="Enter your fullname">

            <label>Email</label>
            <input type="email" id="email" name="email" placeholder="Enter your email">

            <label>Phone Number</label>
            <input type="text" id="phone_number" name="phone_number" placeholder="Enter 10 digit phone number" placeholder="Enter your phone number">

            <label>Address</label>
            <input type="text" id="address" name="address" placeholder="Enter your address">

            <label>Citizenship Number</label>
            <input type="text" id="citizenship_number" name="citizenship_number" placeholder="Enter your citizenship number">

            <label>License Number</label>
            <input type="text" id="license_number" name="license_number" placeholder="Enter your driving license number">

            <label>License Photo</label>
            <input type="file" id="license_photo" name="license_photo" accept=".jpg,.jpeg,.png" required>
            <small>
                Upload JPG, JPEG or PNG image (Maximum 5MB)
            </small>

            <!-- <label>PickUp Location</label>
            <input type="text" id="pickup_location" name="pickup_location" placeholder="Enter your pickup location">  pickup location not here-->

            <label>Username</label>
            <input type="text" id="username" name="username" placeholder="Enter your username">

            <label>Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password">

            <label>Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Enter your confirm password">
            <input type="submit" value="Register">
        </form>

        <p>
            Already have an account?
            <a href="login.php">
                Login
            </a>
        </p>
    </div>

    <script>
        function validateRegistration() {
            const fullname = document.getElementById("fullname").value.trim();
            const email = document.getElementById("email").value.trim();
            const phone = document.getElementById("phone_number").value.trim();
            const address =document.getElementById("address").value.trim();
            const citizenship = document.getElementById("citizenship_number").value.trim();
            const licenseNumber = document.getElementById("license_number").value.trim();
            const licensePhoto = document.getElementById("license_photo").files[0];
            const username = document.getElementById("username").value.trim();
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirm_password").value;

            // Check empty fields
            if (
                fullname === "" ||
                email === "" ||
                phone === "" ||
                address === "" ||
                citizenship === "" ||
                licenseNumber === "" ||
                username === "" ||
                password === "" ||
                confirmPassword === ""
            ) {
                alert(
                    "Please fill in all fields."
                );
                return false;
            }

            // Check phone number
            const phonePattern = /^[0-9]{10}$/;
            if (
                !phonePattern.test(phone)
            ) {
                alert(
                    "Please enter a valid 10-digit phone number."
                );
                return false;
            }

            // Check password length
            if (password.length < 6) {
                alert(
                    "Password must contain at least 6 characters."
                );
                return false;
            }

            // Check password match
            if (
                password !== confirmPassword
            ) {
                alert(
                    "Password and Confirm Password do not match!"
                );
                return false;
            }

            // Check license photo
            if (!licensePhoto) {
                alert(
                    "Please upload your license photo."
                );
                return false;
            }

            // Check file size
            if (
                licensePhoto.size >
                5 * 1024 * 1024
            ) {
                alert(
                    "License photo must be less than 5MB."
                );
                return false;
            }

            // Check file extension
            const allowedTypes = [
                "image/jpeg",
                "image/jpg",
                "image/png"
            ];

            if (
                !allowedTypes.includes(
                    licensePhoto.type
                )
            ) {
                alert(
                    "Only JPG, JPEG and PNG images are allowed."
                );
                return false;
            }
            return true;
        }
    </script>
</body>
</html>