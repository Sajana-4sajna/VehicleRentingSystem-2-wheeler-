<?php
session_start();
$connect = mysqli_connect("localhost","root","","vehicle_renting_system");
if(!$connect){
    die ("Database connection failed");
}
if(!isset($_SESSION['Aid'])) {
    //check remember me cookie
    if (isset($_COOKIE['remember_token'])){
        $token = $_COOKIE['remember_token'];
        $sql = "select * from admin
                where remember_token= '$token'";
            $result = mysqli_query($connect,$sql);
            if (mysqli_num_rows($result) == 1){
                $admin = mysqli_fetch_assoc($result);
                //session creation



                $_SESSION['Aid'] = $admin['Aid'];
                $_SESSION['name'] = $admin['name'];
            }else{

                header("Location: 1.login.php");
                exit();
            }
    }else{
        // no session and no cookie
        header("Location: 1.login.php");
                exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Renting Admin Dashboard</title>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"/>
    <link rel="stylesheet" href="../Assests/2.dashboard.css">
</head>
<body>
<div class="container">
    <aside class="sidebar">
        <div class="logo-section">
            <div class="logo">
                <img src="../Assests/logo.avif" alt="Logo">
                <div>
                    <h2>Vehicle</h2>
                    <h2>Renting</h2>
                    <h2>System</h2>
                </div>
            </div>
            <!-- <button class="close-btn">
                <span class="material-symbols-outlined">close</span>
            </button> -->
        </div>

        <nav>
            <a href="2.1.dashboardSlidebar.php" class="active">
                <span class="material-symbols-outlined">dashboard</span>
                <span>Dashboard</span>
            </a>
            <a href="2.2.customerSlidebar.php ">
                <span class="material-symbols-outlined">person</span>
                <span>Customers</span>
            </a>
            <a href="2.3.vehicleSlidebar.php">
                <span class="material-symbols-outlined">bike_scooter</span>
                <span>Vehicles</span>
            </a>
            <a href="2.4.bookingSlidebar.php">
                <span class="material-symbols-outlined">event_available</span>
                <span>Bookings</span>
            </a>
            <a href="2.5.paymentSlidebar.php">
                <span class="material-symbols-outlined">payments</span>
                <span>Payments</span>
            </a>
            <a href="2.6.revieSlidebar.php">
                <span class="material-symbols-outlined">reviews</span>
                <span>Reviews</span>
                <small class="badge">10</small>
            </a>
            <a href="2.7.settingSlidebar.php">
                <span class="material-symbols-outlined">settings</span>
                <span>Settings</span>
            </a>
        </nav>

        <div class="logout">
            <a href="3.logout.php">
                <span class="material-symbols-outlined">logout</span>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <div class="main-content">
        <header class="navbar">
            <div class="nav-left">
                <button class="menu-btn">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <div class="search-box">
                    <span class="material-symbols-outlined">
                        search
                    </span>
                    <input type="text" placeholder="Search customers, vehicles, bookings...">
                </div>
            </div>
            <div class="nav-right">
                <button class="notification-btn">
                    <span class="material-symbols-outlined"> notifications </span>
                </button>

            
                <div class="profile-wrapper">

                 <!-- Admin Profile -->
                    <div class="admin-profile" onclick="toggleProfileMenu()">

                     <img src="../Assests/cat.jpg" alt="Admin">

                        <div class="adminInfo">
                            <h4><?php echo $_SESSION['name']; ?></h4>
                            <small>Vehicle Manager</small>

                              <!-- Profile Dropdown -->
                     <div class="profileDropdown" id="profileDropdown">

                        <a href="profile.php">
                        <span class="material-symbols-outlined">
                        person
                        </span>
                        <span>My Profile</span>
                        </a>

                        <a href="changePassword.php">
                            <span class="material-symbols-outlined">
                                lock
                            </span>
                            <span>Change Password</span>
                        </a>

                        <a href="settings.php">
                            <span class="material-symbols-outlined">
                                settings
                            </span>
                            <span>Settings</span>
                        </a>

                        <a href="3.logout.php" class="profile-logout">
                            <span class="material-symbols-outlined">
                                logout
                            </span>
                            <span>Logout</span>
                        </a>

                      </div>
                        </div>

                        <!-- <span class="material-symbols-outlined">
                            expand_more
                        </span> -->

                    </div>


                  

                </div>
                

                
            </div>
        </header>

        <main class="dashboard">
            <div class="dashboard-header">
                <div>
                    <h1>Dashboard</h1>
                    <p>Welcome back, <?php echo $_SESSION['name'];?> 👋</p>
                </div>
            </div>

            <section class="cards">
                <div class="card">
                    <span class="material-symbols-outlined">bike_scooter </span>
                    <h3>Total Vehicles</h3>
                    <h2>120</h2>
                </div>
                <div class="card">
                    <span class="material-symbols-outlined">calendar_month</span>
                    <h3>Bookings</h3>
                    <h2>85</h2>
                </div>
                <div class="card">
                    <span class="material-symbols-outlined"> group</span>
                    <h3>Customers</h3>
                    <h2>350</h2>
                </div>
                <div class="card">
                    <span class="material-symbols-outlined">payments </span>
                    <h3>Revenue</h3>
                    <h2>15,450</h2>
                </div>
            </section>

            <section class="dashboard-grid">
                <div class="chart">
                    <div class="section-title">
                        <h3>Vehicle Booking Statistics</h3> </div>
                    <div class="chart-placeholder">  Chart.js will be added here </div>
                </div>
                <!-- Recent Bookings -->
                <div class="bookings">
                    <div class="section-title">
                        <h3>Recent Bookings</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Vehicle</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>John</td>
                                <td>Honda Bike</td>
                                <td>Active</td>
                            </tr>
                            <tr>
                                <td>Sarah</td>
                                <td>Scooter</td>
                                <td>Completed</td>
                            </tr>
                            <tr>
                                <td>David</td>
                                <td>Car</td>
                                <td>Pending</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Recent Customers -->
                <div class="customers">
                    <div class="section-title">
                        <h3>Recent Customers</h3>
                    </div>
                    <ul>
                        <li>Sajan</li>
                        <li>Ram</li>
                        <li>Hari</li>
                        <li>Shyam</li>
                    </ul>
                </div>
                <!-- Vehicle Status -->
                <div class="vehicle-status">
                    <div class="section-title">
                        <h3>Vehicle Status</h3>
                    </div>
                    <ul>
                        <li>Available : 70</li>
                        <li>Booked : 40</li>
                    </ul>
                </div>
            </section>
        </main>
    </div>
</div>
<script src="./2.dashboard.js"></script>
</body>
<footer class="footer">
    <p>© 2026 Vehicle Rental System | Developed by BCA 4th Semester studentw</p>
</footer>
</html>