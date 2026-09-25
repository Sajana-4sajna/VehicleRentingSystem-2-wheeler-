
<?php

session_start();

$conn = mysqli_connect("localhost", "root", "", "vehicle_renting_system");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}


/* =========================================
   CHECK ADMIN LOGIN
========================================= */

if (!isset($_SESSION['Aid'])) {
    header("Location: 1.login.php");
    exit();
}

$Aid = (int) $_SESSION['Aid'];


/* =========================================
   GET CURRENT ADMIN INFORMATION
========================================= */

$sql = "SELECT * FROM admin WHERE Aid = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $Aid);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo "Admin not found.";
    exit();
}

$admin = mysqli_fetch_assoc($result);


/* =========================================
   UPDATE PROFILE
========================================= */

if (isset($_POST['update_profile'])) {

    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    /* -----------------------------------------
       BASIC VALIDATION
    ----------------------------------------- */

    if (empty($name) || empty($username) || empty($email) || empty($phone)) {

        $error = "Please fill in all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        /* -----------------------------------------
           CHECK USERNAME
           Make sure another admin is not using it
        ----------------------------------------- */

        $check_sql = "SELECT Aid FROM admin
                      WHERE username = ? AND Aid != ?";

        $check_stmt = mysqli_prepare($conn, $check_sql);

        mysqli_stmt_bind_param(
            $check_stmt,
            "si",
            $username,
            $Aid
        );

        mysqli_stmt_execute($check_stmt);

        $check_result = mysqli_stmt_get_result($check_stmt);

        if (mysqli_num_rows($check_result) > 0) {

            $error = "This username is already being used by another admin.";

        } else {

            /* -----------------------------------------
               CHECK EMAIL
            ----------------------------------------- */

            $email_sql = "SELECT Aid FROM admin
                          WHERE email = ? AND Aid != ?";

            $email_stmt = mysqli_prepare($conn, $email_sql);

            mysqli_stmt_bind_param(
                $email_stmt,
                "si",
                $email,
                $Aid
            );

            mysqli_stmt_execute($email_stmt);

            $email_result = mysqli_stmt_get_result($email_stmt);

            if (mysqli_num_rows($email_result) > 0) {

                $error = "This email is already being used by another admin.";

            } else {

                /* -----------------------------------------
                   PROFILE IMAGE
                ----------------------------------------- */

                $profile_image = $admin['profile_image'];

                if (isset($_FILES['profile_image']) &&
                    $_FILES['profile_image']['error'] == 0) {

                    $file_name = $_FILES['profile_image']['name'];
                    $file_tmp = $_FILES['profile_image']['tmp_name'];
                    $file_size = $_FILES['profile_image']['size'];

                    $file_ext = strtolower(
                        pathinfo($file_name, PATHINFO_EXTENSION)
                    );

                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];

                    /* Check file type */

                    if (!in_array($file_ext, $allowed_extensions)) {

                        $error = "Only JPG, JPEG, PNG and WEBP images are allowed.";

                    /* Check file size - 2 MB */

                    } elseif ($file_size > 2 * 1024 * 1024) {

                        $error = "Profile image must be less than 2 MB.";

                    } else {

                        /* -----------------------------------------
                           CREATE UPLOAD FOLDER
                        ----------------------------------------- */

                        $upload_folder = "../Assests/";

                        /* -----------------------------------------
                           CREATE UNIQUE FILE NAME
                        ----------------------------------------- */

                        $new_file_name = "admin_" . $Aid . "_" . time() . "." . $file_ext;

                        $upload_path = $upload_folder . $new_file_name;

                        /* -----------------------------------------
                           MOVE IMAGE
                        ----------------------------------------- */

                        if (move_uploaded_file($file_tmp, $upload_path)) {

                            /* Delete old image if it exists */

                            if (!empty($admin['profile_image'])) {

                                $old_image = $upload_folder . $admin['profile_image'];

                                if (file_exists($old_image)) {
                                    unlink($old_image);
                                }
                            }

                            $profile_image = $new_file_name;

                        } else {

                            $error = "Failed to upload profile image.";
                        }
                    }
                }


                /* -----------------------------------------
                   UPDATE DATABASE
                ----------------------------------------- */

                if (!isset($error)) {

                    $update_sql = "UPDATE admin
                                   SET name = ?,
                                       username = ?,
                                       email = ?,
                                       phone = ?,
                                       profile_image = ?
                                   WHERE Aid = ?";

                    $update_stmt = mysqli_prepare($conn, $update_sql);

                    mysqli_stmt_bind_param(
                        $update_stmt,
                        "sssssi",
                        $name,
                        $username,
                        $email,
                        $phone,
                        $profile_image,
                        $Aid
                    );

                    if (mysqli_stmt_execute($update_stmt)) {

                        /* Update session username */

                        $_SESSION['username'] = $username;

                        header("Location: profile.php?updated=1");
                        exit();

                    } else {

                        $error = "Failed to update profile.";
                    }
                }
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

    <title>Edit Profile</title>

    <link rel="stylesheet" href="../Assests/editProfile.css">

</head>


<body>


<div class="container">


    <!-- =====================================
         PAGE HEADER
    ====================================== -->

    <div class="page-header">

        <div>
            <h1>Edit Profile</h1>
            <p>Update your admin account information</p>
        </div>

        <a href="profile.php" class="back-btn">
            ← Back to Profile
        </a>

    </div>


    <!-- =====================================
         ERROR MESSAGE
    ====================================== -->

    <?php if (isset($error)) { ?>

        <div class="error-message">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php } ?>


    <!-- =====================================
         EDIT PROFILE FORM
    ====================================== -->

    <div class="edit-card">


        <form method="POST" enctype="multipart/form-data">


            <!-- PROFILE IMAGE -->

            <div class="image-section">

                <?php if (!empty($admin['profile_image'])) { ?>

                    <img
                        src="../Assests/<?php echo htmlspecialchars($admin['profile_image']); ?>"
                        alt="Profile Image"
                        class="profile-image"
                    >

                <?php } else { ?>

                    <div class="profile-placeholder">
                        <?php echo strtoupper(substr($admin['name'], 0, 1)); ?>
                    </div>

                <?php } ?>


                <label for="profile_image">
                    Change Profile Photo
                </label>

                <input
                    type="file"
                    name="profile_image"
                    id="profile_image"
                    accept=".jpg,.jpeg,.png,.webp"
                >

                <small>
                    JPG, JPEG, PNG or WEBP. Maximum size: 2 MB.
                </small>

            </div>


            <!-- FULL NAME -->

            <div class="form-group">

                <label for="name">
                    Full Name
                </label>

                <input
                    type="text"
                    name="name"
                    id="name"
                    value="<?php echo htmlspecialchars($admin['name']); ?>"
                    required
                >

            </div>


            <!-- USERNAME -->

            <div class="form-group">

                <label for="username">
                    Username
                </label>

                <input
                    type="text"
                    name="username"
                    id="username"
                    value="<?php echo htmlspecialchars($admin['username']); ?>"
                    required
                >

            </div>


            <!-- EMAIL -->

            <div class="form-group">

                <label for="email">
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    id="email"
                    value="<?php echo htmlspecialchars($admin['email']); ?>"
                    required
                >

            </div>


            <!-- PHONE -->

            <div class="form-group">

                <label for="phone">
                    Phone
                </label>

                <input
                    type="text"
                    name="phone"
                    id="phone"
                    value="<?php echo htmlspecialchars($admin['phone']); ?>"
                    required
                >

            </div>


            <!-- ADMIN ID -->

            <div class="form-group">

                <label>
                    Admin ID
                </label>

                <input
                    type="text"
                    value="<?php echo htmlspecialchars($admin['Aid']); ?>"
                    readonly
                >

            </div>


            <!-- BUTTONS -->

            <div class="form-actions">

                <a href="profile.php" class="cancel-btn">
                    Cancel
                </a>

                <button
                    type="submit"
                    name="update_profile"
                    class="save-btn"
                >
                    Save Changes
                </button>

            </div>


        </form>


    </div>


</div>


</body>

</html>


