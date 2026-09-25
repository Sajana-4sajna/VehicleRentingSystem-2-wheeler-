<?php
$error= "";
session_start();
if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];
    //DATABASE CONNECTION
    $connect = mysqli_connect('localhost','root','','vehicle_renting_system');
    if(!$connect){
        die ("Could not connect database !!");
    }
    $sql = "SELECT * FROM admin 
    WHERE username = '$username' and password = '$password'";
    $result = mysqli_query($connect,$sql);
    if(!$result) {
        die('query failed!'.mysqli_error($connect));
    }
   
    if(mysqli_num_rows($result) == 1){
        $row = mysqli_fetch_assoc($result);
        if($password == $row['password']){
            //session created
            $_SESSION['Aid'] =$row['Aid'];
            $_SESSION['username'] = $row['username'];
            if(isset($_POST['remember'])){
                //generate random token
                $token = bin2hex(random_bytes(32));
             

                //store token in database
                $sql = "Update admin 
                SET remember_token = '$token'
                where Aid =".$row['Aid'];

               
                if (!mysqli_query($connect, $sql)) {
               die("Token update failed: " . mysqli_error($connect));
}
                

                //store token in cookie for 20 days
                setcookie("remember_token",$token,time()+(20*24*60*60),"/",false,true);
            }

            // echo "Login sucess!";
            header("Location: 2.dashboard.php");
            exit();
        }
    }else{
    
        $error= 'Invalid username or password!';
    }
    
    
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin login </title>
    <link rel="stylesheet" href="../Assests/1.login.css">
    <style>
        .error{
            color:red;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            
            <h1>Admin Login</h2>
            <p>Vehicle Renting System</p>
        </div>
        <div class="login-box">

            <form action="" method="POST" onsubmit="return validateLogin()>

                <label for="">Username:</label>
                <input type="text" name="username" id="username" placeholder="Enter your username">
                <label for="">Password:</label>
                <input type="password" name="password" id="password" placeholder="Enter your password">
                <div class="login-options">
                    <div class="remember">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">
                            Remember Me
                        </label>
                    </div>
                    <a href="1.1forgot_password.php">Forgot Password?</a>

                    </div>
                    <input type="submit" name="login" value="Login">
                    <?php
             if ($error != "") {
                 echo "<p class='error'>$error</p>";
                 }
                 ?>
                </form>
        </div>
                   
                        
                        
    </div>
    <script src="./1.login.js"></script>
</body>
</html>
 
