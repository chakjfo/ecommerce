<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Accents Clothing</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js"></script>
    <style>
        *{
            margin: 0;
            padding: 0;
            font-family: "Anton", serif;
            font-weight: 400;
            font-style: normal;
        }

        .header{
            min-height: 100vh;
            width: 100%;
            background-image: linear-gradient(rgba(233, 233, 233, 0.7),rgba(4,9,30,0.7)),url(images/bg1.jpg);    
            background-position: center;
            background-size: cover;
            position: relative;
        }

        nav{
            display: flex;
            justify-content: space-between;
            padding: 2% 6%;
            align-items: center;
        }

        nav img{
            width: 150px;
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

        .text-box{
            width: 90%;
            color: rgba(0, 1, 1, 0.76);
            position: absolute;
            font-size: 1.5em;
            top: 50%;
            left: 50%;
            transform: translate(-50%,-50%);
            text-align: center;
        }

        .text-box h1{
            font-size: 2.5em;
        }

        .text-box p{
            margin: 10px 0 20px;
            font-size: 1em;
        }

        .hero-btn{
            display: inline-block;
            text-decoration: none;
            color: rgb(0, 0, 0);
            border: 1px solid white;
            padding: 12px 34px;
            font-size: 1.2em;
            background: transparent;
            position: relative;
            cursor: pointer;
        }

        .hero-btn:hover{
            border: 1px solidrgb(0, 0, 0);
            background:rgb(0, 0, 0);
            color: white;
            transition: 1s;
        }
        
        nav .fa-bars{
            display: none;
        }   

        nav .fa-xmark{
            display: none;
        }  

        @media(max-width: 700px){
            .text-box h1{
                font-size: 1.5em;
            }
            .nav-links ul li {
                display: block;
            }

            .nav .fa-bars{
                display: block;
                color: black;
                font-size: 22px;
                cursor: pointer;
            }

            .nav-links{
                position:absolute;
                background:rgba(255, 0, 0, 0.6);
                height: 100%;
                width: 200px;
                top: 0;
                right: -200px;
                text-align: left;
                z-index: 2;
                transition: right 1s;
            }
            nav .fa-xmark{
                display: block;
                color:#black;
                margin: 10px;
                font-size: 22px;
                cursor: pointer;
            }
            
            .nav-links ul{
                padding: 30px;
            }
        }
    </style>
<body>
    <section class="header">
        <nav>
            <a href="homepage.php"><img src="images/the_accents_logo.png"></a>
            <div class="nav-links" id="navLinks">
            <i class="fa fa-xmark" onclick="hideMenu()"></i>
                <ul>
                    <li><a href="shop_customer.php">SHOP</a></li>
                    <li><a href="">ABOUT US</a></li>
                    <li><a href="">CONTACT US</a></li>
                    <li><a href="login.php">LOG IN</a></li>
                    <li><a href="signup.php">SIGN UP</a></li>
                </ul>
            </div>
            <i class="fa fa-bars" onclick="showMenu()"></i>
        </nav>
        <div class="text-box">
            <h1>We make stories, not shirts.</h1>
            <p>The Accents is a lifestyle brand that embraces diversity in different subcultures in our society.</p>
            <a href="shop_customer.php" class="hero-btn">SHOP NOW</a>
        </div>
    </section>
    <script>
        var navLinks = document.getElementById("navLinks");
        function showMenu(){
            navLinks.style.right = "0";
        }
        function hideMenu(){
            navLinks.style.right = "-200px";
        }
    </script>
</div>
</body>
</html>
