<?php
session_start();
$Aid = $_SESSION['Aid'] ?? null;
//database connection
$connect = mysqli_connect('localhost','root','','vehicle_renting_system');
if($Aid){
    //remove remember token from database
    $sql = "update admin
            set remember_token = NULL
            where Aid = $Aid";
    mysqli_query($connect,$sql);
}
//delete cookie
setcookie('remember_token','',time()-3600,'/');

//destroy session
session_unset();
session_destroy();
//go back to login form
header("Location: 1.login.php");
exit();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>logout</title>
</head>
<body>
    
</body>
</html>