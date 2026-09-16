<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Renting System</title>
    <style>
        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body{
            background: #d4a4a4;
            color: #222;
        }

        /* Header */

        nav{
            background: #d4a4a4;
            padding: 0px 30px;
            display: flex;
            align-items: center;
            height: 90px;
        }

        .logo{
            width: 70px;
            height: 70px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid #000;
            flex-shrink: 0;
        }

        .logo img{
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        nav ul{
            list-style: none;
            display: flex;
            align-items: center;
            gap: 25px;
            margin-left: 40px;
            width:100%;
        }

        nav ul li:nth-child(5){
            margin-left: auto;
        }

        nav ul li{
            margin: 0;
        }

        nav ul li a{
            color: #101110;
            text-decoration: none;
            font-size: 18px;
            font-weight: bold;
        }

        nav ul li a:hover{
            color: #fef9f8;
        }

        /* Hero */

        .hero{
            height:500px;
            background: linear-gradient(rgba(0,0,0,.5),rgba(0,0,0,.5)),
            url("../images/bike.jpg");

            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
        }

        .hero h1{
            font-size: 55px;
            margin-bottom: 15px;
        }

        .hero p{
            margin: 20px;
            font-size: 22px;
            line-height: 1.5;
        }

        .btn{
            display: inline-block;
            background: #00cc66;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 18px;
        }

        .btn:hover{
            background: green;
        }

        /* Sections */

        section{
            padding: 60px;
            text-align: center;
        }

        section h2{
            font-size: 30px;
            margin-bottom: 15px;
        }

        section p{
            font-size: 17px;
            line-height: 1.6;
        }

        .bike-container{
            display: flex;
            justify-content: center;
            align-items: stretch;
            gap: 30px;
            flex-wrap: wrap;
        }

        .bike-card{
            width: 250px;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 0px 10px gray;
            padding: 20px;
        }

        .bike-card:hover{
            transform: translateY(-5px);
            box-shadow: 0px 5px 15px gray;
        }

        .bike-card img{
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 5px;
        }

        .bike-card h3{
            margin: 15px 0 10px;
            font-size: 20px;
        }

        .bike-card p{
            font-size: 17px;
            font-weight: bold;
        }

        .service-box{
            display: flex;
            justify-content: center;
            align-items: stretch;
            gap: 30px;
            flex-wrap: wrap;
        }

        .service-box div{
            width: 250px; 
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px gray;
        }

        .service-box div:hover{
            box-shadow: 0px 5px 15px gray;
        }

        .service-box h3{
            margin-bottom: 15px;
            font-size: 20px;
        }

        #contact p{
            margin: 8px 0;
        }

        footer{
            background: #222;
            color: white;
            text-align: center;
            padding: 20px;
        }

        footer p{
            font-size: 15px;
        }
    </style>
</head>
<body>
    <nav>
        <div class="logo">
            <img src="../Assests/logo.avif" alt="Vehicle Renting Logo">
        </div>
        <ul>
            <li>
                <a href="home_page.php">Home</a>
            </li>

            <li>
                <?php if (isset($_SESSION['Cid'])) { ?>
                    <a href="vehicles.php">Vehicles</a>
                <?php } else { ?>
                    <a href="login.php"
                    onclick="return requireLogin('Please login first to view vehicles.');">
                        Vehicles
                    </a>
                <?php } ?>
            </li>

            <li>
                <a href="#about">About</a>
            </li>

            <li>
                <a href="#contact">Contact</a>
            </li>

            <?php if (isset($_SESSION['Cid'])) { ?>
                <li>
                    <a href="dashboard.php">Dashboard</a>
                </li>

                <li>
                    <a href="logout.php"
                    onclick="return confirmLogout();">
                        Logout
                    </a>
                </li>
            <?php } else { ?>
                <li>
                    <a href="login.php">Login</a>
                </li>

                <li>
                    <a href="register.php">Register</a>
                </li>

            <?php } ?>
        </ul>
    </nav>

    <section class="hero">
        <div class="hero-text">
            <h1>Rent Your Favourite Vehicle Anytime</h1>
            <p>
                Fast, Affordable and Reliable 2-Wheeler Rental Service.
                Book your dream vehicle in just a few clicks.
            </p>
            <?php if (isset($_SESSION['Cid'])) { ?>
                <a href="vehicles.php" class="btn">
                    Book Now
                </a>
            <?php } else { ?><br><br>
                <a href="register.php" class="btn">
                    Book Now
                </a>
            <?php } ?>
        </div>
    </section>

    <section id="about">
        <h2>About Us</h2><br>
        <p>
            Vehicle Renting System(2-Wheelers) is a web-based application that allows
            customers to register, log in, browse available vehicles and
            book vehicles online.<br>It provides a fast, secure and convenient
            way to rent vehicles while helping administrators manage vehicles,
            customers and bookings efficiently.
        </p>
    </section>

    <section id="vehicles">
        <h2>Available Vehicles</h2><br>
        <div class="bike-container">
            <div class="bike-card">
                <img src="../assets/images/ather 450x.jpg" alt="Ather 450X">
                <h3>Ather 450X</h3>
                <p>Rs. 800 / Day</p>
            </div>

            <div class="bike-card">
                <img src="../assets/images/apache.jpg" alt="TVS Apache">
                <h3>TVS Apache RTR</h3>
                <p>Rs. 1000 / Day</p>
            </div>

            <div class="bike-card">
                <img src="../assets/images/r15.jpg" alt="Yamaha R15">
                <h3>Yamaha R15</h3>
                <p>Rs. 1200 / Day</p>
            </div>

            <div class="bike-card">
                <img src="../assets/images/shine.jpg" alt="Honda Shine">
                <h3>Honda Shine</h3>
                <p>Rs. 1400 / Day</p>
            </div>

            <div class="bike-card">
                <img src="../assets/images/royal enfield.jpg" alt="Royal Enfield Classic 650">
                <h3>Royal Enfield Classic 650</h3>
                <p>Rs. 1800 / Day</p>
            </div>

        </div>
    </section>

    <section class="service">
        <h2>Why Choose Vehicle Rent?</h2><br>
        <div class="service-box">
            <div>
                <h3>Affordable Prices</h3><br>
                <p>
                    Rent vehicles at reasonable prices with no hidden charges.
                </p>
            </div>

            <div>
                <h3>Easy Online Booking</h3><br>
                <p>
                    Book your preferred vehicle in just a few clicks.
                </p>
            </div>

            <div>
                <h3>24/7 Customer Support</h3><br>
                <p>
                    Our support team is always available to help you.
                </p>
            </div>

        </div>
    </section>

    <section id="contact">
        <h2>Contact Us</h2><br>
        <p>
            <strong>Address:</strong>
            Sundhara, Kathmandu, Nepal
        </p>
        <p>
            <strong>Phone:</strong>
            +977-9803925482
        </p>
        <p>
            <strong>Email:</strong>
            info@vehiclerent.com
        </p>
        <p>
            <strong>Working Hours:</strong>
            Sunday - Saturday
        </p>
    </section>

    <footer>
        <p>
            © 2026 Vehicle Renting System |
            Developed by BCA 4th Semester Student
        </p>
    </footer>

    <script src="../Assests/js/project.js"></script>
</body>
</html>