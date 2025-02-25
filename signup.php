<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Accents Clothing - Signup</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            font-family: "Anton", serif;
            font-weight: 400;
            font-style: normal;
            box-sizing: border-box;
        }

        .header {
            min-height: 100vh;
            width: 100%;
            background-image: linear-gradient(rgba(233, 233, 233, 0.7), rgba(4, 9, 30, 0.7)), url(images/bg1.jpg);
            background-position: center;
            background-size: cover;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        nav {
            display: flex;
            justify-content: space-between;
            padding: 2% 6%;
            align-items: center;
            width: 100%;
            position: absolute;
            top: 0;
        }

        nav img {
            width: 120px;
        }
        .nav-links {
            flex: 1;
            text-align: right;
        }

        .nav-links ul li{
            list-style: none;
            display: inline-block;
            padding: 8px 12px;
            position: relative;
        }

        .nav-links ul li a{
            color: black;
            text-decoration: none;
            font-size: 20px;
        }

        .nav-links ul li::after{
            content: '';
            width: 0%;
            height: 2px;
            background:rgb(0, 0, 0);
            display: block;
            margin: auto;
            transition: 0.5s;
        }

        .nav-links ul li:hover::after{
            width: 100%;
        }

        .signup-container {
            background: rgba(255, 255, 255, 0.58); /* 80% transparent */
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
            margin-top: 55px;
        }


        .signup-container img {
            width: 80px;
            margin-bottom: 15px;
            height: 20px;
        }

        .signup-container h2 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #333;
        }

        .signup-container input, .signup-container select {
            width: 100%;
            padding: 6px; 
            margin-bottom: 12px; 
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;

        .signup-container button {
            width: 100%;
            padding: 10px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .signup-container button:hover {
            background-color:rgb(79, 34, 34);
            border: 1px solid rgb(0, 0, 0);
 
            color: white;
            transition: 1s;
        }
        .signup-container button{
            display: inline-block;
            text-decoration: none;
            color: white;
            border: 1px solid black;
            padding: 12px 34px;
            font-size: 1.2em;
            background:rgb(0, 0, 0) ;
            position: relative;
            cursor: pointer;
        }


        .signup-container p {
            padding: 10px;
            font-size: 12px; /* Adjust the font size to make the text smaller */
            color: black;
        }

        .signup-container p a {
            color:rgb(22, 95, 168);
            text-decoration: none;
        }

        .signup-container p a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <section class="header">
        <nav>
            <a href="homepage.php"><img src="images/the_accents_logo.png"></a>
            <div class="nav-links" id="navLinks">
                <ul>
                    <li><a href="shop.php">SHOP</a></li>
                    <li><a href="aboutus.php">ABOUT US</a></li>
                    <li><a href="contactus.php">CONTACT US</a></li>
                    <li><a href="login.php">LOG IN</a></li>
                    <li><a href="signup.php">SIGN UP</a></li>
                </ul>
            </div>
        </nav>
        <div class="signup-container">
            <img src="images/the_accents_logo.png" alt="The Accents Clothing">
            <h2>Create an Account</h2>
            <form action="signup_process.php" method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <input type="text" name="phone" placeholder="Phone Number" required>
                <select name="role" required>
                    <option value="customer">Customer</option>
                    <option value="admin">Admin</option>
                </select>
                <button type="submit">Sign up</button>
            </form>
            <p>Already had an account? <a href="login.php">Login here.</a></p>
        </div>
    </section>
</body>
</html>
