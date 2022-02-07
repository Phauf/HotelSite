<!-- HOTELS -->
<?php
include("../functions.php");
session_start();

$email=$_REQUEST['email'];// this is the way to use the var passed in the target page
$pwd=$_REQUEST['pwd'];
$userType=$_REQUEST['userType'];
$name=$_REQUEST['name'];
$last=$_REQUEST['last'];
$userID=$_REQUEST['userID']; 
$employeeID=$_REQUEST['employeeID'];
?>

<!DOCTYPE html>
<html lang="en">

<!-- HEAD -->
<head>
    <meta charset="UTF-8">
    <!-- Title for tab -->
    <title>G8 Booking | SE Group 8</title>
    
    <!-- AVIATO -->
    <!-- Mobile Specific Metas -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Construction Html5 Template">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="author" content="Themefisher">
    <meta name="generator" content="Themefisher Constra HTML Template v1.0">
    
    <!-- FAVICON -->
    <link href="../images/favicon.png" rel="shortcut icon">
    
  <!-- Themefisher Icon font -->
  <link rel="stylesheet" href="../assets/plugins/themefisher-font/style.css">
    
  <!-- bootstrap.min css -->
  <link rel="stylesheet" href="../assets/plugins/bootstrap/css/bootstrap.min.css">
  
  <!-- Animate css -->
  <link rel="stylesheet" href="..assets/plugins/animate/animate.css">
    
  <!-- Slick Carousel -->
  <link rel="stylesheet" href="../assets/plugins/slick/slick.css">
  <link rel="stylesheet" href="../assets/plugins/slick/slick-theme.css">
  
  <!-- Main Stylesheet -->
  <link rel="stylesheet" href="../assets/css/style.css">
    
</head>

<!-- BODY -->
<body id="body" style="background-color:floralwhite;">
    
    <!-- Start Top Header Bar -->
    <section class="top-header" style="background-color:lightsteelblue;">
        <div class="container">
            <div class="row">
        
                <!-- 1/3 -->
                <div class="col-md-4 col-xs-12 col-sm-4">
                    
                </div>
                
                <!-- 2/3 Site Logo -->
                <div class="col-md-4 col-xs-12 col-sm-4">
                    
                    <div class="logo text-center">
                        <a href="../">
                            <h1><b>G8 Booking</b></h1>
                        </a>
                    </div>
                    
                </div>
                
                <!-- 3/3 Log out Button -->
                <div class="col-md-4 col-xs-12 col-sm-4">
                    <ul class="text-right list-inline mt-10">
                        
                        <li class="li">
                            <a href="../logout/index.php" class="btn btn-main btn-small btn-round-full" style="background-color:floralwhite; color:black; font-size: 16px;">Log out</a>
                        </li>
                        
                    </ul>
                </div>
                
            </div>
        </div>
    </section>

    <!-- Main Menu Section -->
    <section class="menu">
        <nav class="navbar navigation"> 
            <div class="container">
			
                <div class="navbar-header">
                    <h2 class="menu-title">Main Menu</h2>
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                </div>
                
                <div id="navbar" class="navbar-collapse collapse text-center">
                    <ul class="nav navbar-nav">
                        
                        <!-- Dashboard -->
                        <li class="dropdown dropdown-slide">
                            <a href="#!" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="350" role="button" aria-haspopup="true" aria-expanded="false">Dashboard <span class="tf-ion-ios-arrow-down"></span></a>
                            
                            <ul class="dropdown-menu" style="background-color:floralwhite;">
                                <li class="dropdown-header">Dashboard</li>
                                <li role="separator" class="divider"></li>
                                <li><a href="../userProfile/index.php?email=<?php echo $email?>&pwd=<?php echo $pwd?>&userType=<?php echo $userType?>&name=<?php echo $name?>&last=<?php echo $last?>&userID=<?php echo $userID?>&employeeID=<?php echo $employeeID?>">User Profile</a></li>
                                <li><a href="../reservations/index.php?email=<?php echo $email?>&pwd=<?php echo $pwd?>&userType=<?php echo $userType?>&name=<?php echo $name?>&last=<?php echo $last?>&userID=<?php echo $userID?>&employeeID=<?php echo $employeeID?>">Reservations</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
                
            </div>
        </nav>
    </section>

    <!-- Hotel Listing -->
    <section class="products selection">
        <div class="container">
            
            <!-- 0 - The Magnolia All Suites ******MJ Healey*******-->
            <div class="row">
                
                <!-- Hotel Image -->
                <div class="col-md-5">
                    <div class="single-product-details text-center">
                        <img src="../images/TMAS.jpg" alt="product-img" width="450" height="400">
                    </div>
                </div>
                
                <!-- Hotel Info -->
                <div class="col-md-7">
                    <div class="single-product-details">
						<h2><b>The Magnolia All Suites</b></h2>
						<form class="text-left clearfix" action="" method="post">
                            
                        	<!-- Amenities -->
                        	<div class="product-info">
                            	<span><b>Amenities:</b></span>
                            	<ul class="list-inline mt-10">
                                	<li><p>Pool 			&#127946;</p></li>
                                	<li><p>Gym 				&#127947;</p></li>
                                	<li><p>Spa 				&#129494;</p></li>
                                	<li><p>Business Office &#128187;</p></li>
                            	</ul>
                       	    </div>
                            
                        	<!-- Room Type -->
                        	<div>
                           		<span><b>Room Type: </b></span>					
                            	<select name="magnoliaRoomType">
									<option value="">Select...</option>
                                	<option value="Standard">Standard</option>
                                	<option value="Queen">Queen</option>
                                	<option value="King">King</option>
                            	</select>				
                        	</div>
                            
                        	<!-- Booking Date -->
                        	<div>
                            	<ul class="list-inline mt-10">
                                	<!-- Check-in -->
                                	<li>									
                                    	<div class="form-group">
                                        	<span>Check-in</span>
                                        	<input type="date" class="form-control" name="magnoliastartdate">
                                    	</div>									
                                	</li>
                                	<!-- Check-out -->
                                	<li>
                                    	<span>Check-out</span>
                                    	<input type="date" class="form-control" name="magnoliaenddate">			
                                	</li>
                            	</ul>
                        	</div>
                            
                            <!-- Booking Button -->
							<div>
              					<button type="submit" class="btn btn-main mt-20 btn-solid-border" style="background-color:lightsteelblue;" name="bookMagnolia">Book Now!</button>
							</div>  
                            
                    </div>
                </div>
                    
            </div>
            &nbsp;
            &nbsp;
                
            <!-- 1 - The Lofts at Town Centre -->
            <div class="row">
                
                <!-- Hotel Image -->
                <div class="col-md-5">
                    <div class="single-product-details text-center">
                        <img src="../images/TLTC.jpg" alt="product-img" width="450" height="400">
                    </div>
                </div>
                
                <!-- Hotel Info -->
                <div class="col-md-7">
                    <div class="single-product-details">
						<h2><b>The Lofts at Town Centre</b></h2>
						<form class="text-left clearfix" action="" method="post">
                            
                        	<!-- Amenities -->
                        	<div class="product-info">
                            	<span><b>Amenities:</b></span>
                            	<ul class="list-inline mt-10">
                                	<li><p>Pool 			&#127946;</p></li>
                                	<li><p>Gym 				&#127947;</p></li>
                                	<li><p>Business Office &#128187;</p></li>
                            	</ul>
                       	    </div>
                            
                        	<!-- Room Type -->
                        	<div>
                           		<span><b>Room Type: </b></span>					
                            	<select name="loftsRoomType">
									<option value="">Select...</option>
                                	<option value="Standard">Standard</option>
                                	<option value="Queen">Queen</option>
                                	<option value="King">King</option>
                            	</select>				
                        	</div>
                            
                        	<!-- Booking Date -->
                        	<div>
                            	<ul class="list-inline mt-10">
                                	<!-- Check-in -->
                                	<li>									
                                    	<div class="form-group">
                                        	<span>Check-in</span>
                                        	<input type="date" class="form-control" name="loftsstartdate">
                                    	</div>									
                                	</li>
                                	<!-- Check-out -->
                                	<li>
                                    	<span>Check-out</span>
                                    	<input type="date" class="form-control" name="loftsenddate">			
                                	</li>
                            	</ul>
                        	</div>
                            
                            <!-- Booking Button -->
							<div>
              					<button type="submit" class="btn btn-main mt-20 btn-solid-border" style="background-color:lightsteelblue;" name="bookLofts">Book Now!</button>
							</div>  
                            
                    </div>
                </div>
                    
            </div>
            &nbsp;
            &nbsp;
                
            <!-- 2 - Park North Hotel -->
            <div class="row">
                
                <!-- Hotel Image -->
                <div class="col-md-5">
                    <div class="single-product-details text-center">
                        <img src="../images/PNH.jpg" alt="product-img" width="450" height="400">
                    </div>
                </div>
                
                <!-- Hotel Info -->
                <div class="col-md-7">
                    <div class="single-product-details">
						<h2><b>Park North Hotel</b></h2>
						<form class="text-left clearfix" action="" method="post">
                            
                        	<!-- Amenities -->
                        	<div class="product-info">
                            	<span><b>Amenities:</b></span>
                            	<ul class="list-inline mt-10">
                                	<li><p>Pool 			&#127946;</p></li>
                                	<li><p>Gym 				&#127947;</p></li>
                            	</ul>
                       	    </div>
                            
                        	<!-- Room Type -->
                        	<div>
                           		<span><b>Room Type: </b></span>					
                            	<select name="parkRoomType">
									<option value="">Select...</option>
                                	<option value="Standard">Standard</option>
                                	<option value="Queen">Queen</option>
                                	<option value="King">King</option>
                            	</select>				
                        	</div>
                            
                        	<!-- Booking Date -->
                        	<div>
                            	<ul class="list-inline mt-10">
                                	<!-- Check-in -->
                                	<li>									
                                    	<div class="form-group">
                                        	<span>Check-in</span>
                                        	<input type="date" class="form-control" name="parkstartdate">
                                    	</div>									
                                	</li>
                                	<!-- Check-out -->
                                	<li>
                                    	<span>Check-out</span>
                                    	<input type="date" class="form-control" name="parkenddate">			
                                	</li>
                            	</ul>
                        	</div>
                            
                            <!-- Booking Button -->
							<div>
              					<button type="submit" class="btn btn-main mt-20 btn-solid-border" style="background-color:lightsteelblue;" name="bookPark">Book Now!</button>
							</div>  
                            
                    </div>
                </div>
                    
            </div>
            &nbsp;
            &nbsp;
                
            <!-- 3 - The Courtyard Suites -->
            <div class="row">
                
                <!-- Hotel Image -->
                <div class="col-md-5">
                    <div class="single-product-details text-center">
                        <img src="../images/TCS.jpg" alt="product-img" width="450" height="400">
                    </div>
                </div>
                
                <!-- Hotel Info -->
                <div class="col-md-7">
                    <div class="single-product-details">
						<h2><b>The Courtyard Suites</b></h2>
						<form class="text-left clearfix" action="" method="post">
                            
                        	<!-- Amenities -->
                        	<div class="product-info">
                            	<span><b>Amenities:</b></span>
                            	<ul class="list-inline mt-10">
                                	<li><p>Pool 			&#127946;</p></li>
                                	<li><p>Gym 				&#127947;</p></li>
                                	<li><p>Spa 				&#129494;</p></li>
                                	<li><p>Business Office &#128187;</p></li>
                            	</ul>
                       	    </div>
                            
                        	<!-- Room Type -->
                        	<div>
                           		<span><b>Room Type: </b></span>					
                            	<select name="courtRoomType">
									<option value="">Select...</option>
                                	<option value="Standard">Standard</option>
                                	<option value="Queen">Queen</option>
                                	<option value="King">King</option>
                            	</select>				
                        	</div>
                            
                        	<!-- Booking Date -->
                        	<div>
                            	<ul class="list-inline mt-10">
                                	<!-- Check-in -->
                                	<li>									
                                    	<div class="form-group">
                                        	<span>Check-in</span>
                                        	<input type="date" class="form-control" name="courtstartdate">
                                    	</div>									
                                	</li>
                                	<!-- Check-out -->
                                	<li>
                                    	<span>Check-out</span>
                                    	<input type="date" class="form-control" name="courtenddate">			
                                	</li>
                            	</ul>
                        	</div>
                            
                            <!-- Booking Button -->
							<div>
              					<button type="submit" class="btn btn-main mt-20 btn-solid-border" style="background-color:lightsteelblue;" name="bookCourt">Book Now!</button>
							</div>  
                            
                    </div>
                </div>
                    
            </div>
            &nbsp;
            &nbsp;
                
            <!-- 4 - The Regency Rooms -->
            <div class="row">
                
                <!-- Hotel Image -->
                <div class="col-md-5">
                    <div class="single-product-details text-center">
                        <img src="../images/TRR.jpg" alt="product-img" width="450" height="400">
                    </div>
                </div>
                
                <!-- Hotel Info -->
                <div class="col-md-7">
                    <div class="single-product-details">
						<h2><b>The Regency Rooms</b></h2>
						<form class="text-left clearfix" action="" method="post">
                            
                        	<!-- Amenities -->
                        	<div class="product-info">
                            	<span><b>Amenities:</b></span>
                            	<ul class="list-inline mt-10">
                                	<li><p>Pool 			&#127946;</p></li>
                                	<li><p>Gym 				&#127947;</p></li>
                                	<li><p>Spa 				&#129494;</p></li>
                                	<li><p>Business Office &#128187;</p></li>
                            	</ul>
                       	    </div>
                            
                        	<!-- Room Type -->
                        	<div>
                           		<span><b>Room Type: </b></span>					
                            	<select name="regencyRoomType">
									<option value="">Select...</option>
                                	<option value="Standard">Standard</option>
                                	<option value="Queen">Queen</option>
                                	<option value="King">King</option>
                            	</select>				
                        	</div>
                            
                        	<!-- Booking Date -->
                        	<div>
                            	<ul class="list-inline mt-10">
                                	<!-- Check-in -->
                                	<li>									
                                    	<div class="form-group">
                                        	<span>Check-in</span>
                                        	<input type="date" class="form-control" name="regencystartdate">
                                    	</div>									
                                	</li>
                                	<!-- Check-out -->
                                	<li>
                                    	<span>Check-out</span>
                                    	<input type="date" class="form-control" name="regencyenddate">			
                                	</li>
                            	</ul>
                        	</div>
                            
                            <!-- Booking Button -->
							<div>
              					<button type="submit" class="btn btn-main mt-20 btn-solid-border" style="background-color:lightsteelblue;" name="bookRegency">Book Now!</button>
							</div>  
                            
                    </div>
                </div>
                    
            </div>
            &nbsp;
            &nbsp;
                
            <!-- 5 -Town Inn Budget Rooms -->
            <div class="row">
                
                <!-- Hotel Image -->
                <div class="col-md-5">
                    <div class="single-product-details text-center">
                        <img src="../images/TIBR.jpg" alt="product-img" width="450" height="400">
                    </div>
                </div>
                
                <!-- Hotel Info -->
                <div class="col-md-7">
                    <div class="single-product-details">
						<h2><b>Town Inn Budget Rooms</b></h2>
						<form class="text-left clearfix" action="" method="post">
                            
                        	<!-- Amenities -->
                        	<div class="product-info">
                            	<span><b>Amenities:</b></span>
                            	<ul class="list-inline mt-10">
                                	<li><p>Pool 			&#127946;</p></li>
                            	</ul>
                       	    </div>
                            
                        	<!-- Room Type -->
                        	<div>
                           		<span><b>Room Type: </b></span>					
                            	<select name="townRoomType">
									<option value="">Select...</option>
                                	<option value="Standard">Standard</option>
                                	<option value="Queen">Queen</option>
                                	<option value="King">King</option>
                            	</select>				
                        	</div>
                            
                        	<!-- Booking Date -->
                        	<div>
                            	<ul class="list-inline mt-10">
                                	<!-- Check-in -->
                                	<li>									
                                    	<div class="form-group">
                                        	<span>Check-in</span>
                                        	<input type="date" class="form-control" name="townstartdate">
                                    	</div>									
                                	</li>
                                	<!-- Check-out -->
                                	<li>
                                    	<span>Check-out</span>
                                    	<input type="date" class="form-control" name="townenddate">			
                                	</li>
                            	</ul>
                        	</div>
                            
                            <!-- Booking Button -->
							<div>
              					<button type="submit" class="btn btn-main mt-20 btn-solid-border" style="background-color:lightsteelblue;" name="bookTown">Book Now!</button>
							</div>  
                            
                    </div>
                </div>
                    
            </div>
            &nbsp;
            &nbsp;
                
            <!-- 6 - The Comfy Motel Place -->
            <div class="row">
                
                <!-- Hotel Image -->
                <div class="col-md-5">
                    <div class="single-product-details text-center">
                        <img src="../images/TCM.jpg" alt="product-img" width="450" height="400">
                    </div>
                </div>
                
                <!-- Hotel Info -->
                <div class="col-md-7">
                    <div class="single-product-details">
						<h2><b>The Comfy Motel Place</b></h2>
						<form class="text-left clearfix" action="" method="post">
                            
                        	<!-- Amenities -->
                        	<div class="product-info">
                            	<span><b>Amenities:</b></span>
                            	<ul class="list-inline mt-10">
                                	<li><p>NONE</p></li>
                            	</ul>
                       	    </div>
                            
                        	<!-- Room Type -->
                        	<div>
                           		<span><b>Room Type: </b></span>					
                            	<select name="comfyRoomType">
									<option value="">Select...</option>
                                	<option value="Standard">Standard</option>
                                	<option value="Queen">Queen</option>
                            	</select>				
                        	</div>
                            
                        	<!-- Booking Date -->
                        	<div>
                            	<ul class="list-inline mt-10">
                                	<!-- Check-in -->
                                	<li>									
                                    	<div class="form-group">
                                        	<span>Check-in</span>
                                        	<input type="date" class="form-control" name="comfystartdate">
                                    	</div>									
                                	</li>
                                	<!-- Check-out -->
                                	<li>
                                    	<span>Check-out</span>
                                    	<input type="date" class="form-control" name="comfyenddate">			
                                	</li>
                            	</ul>
                        	</div>
                            
                            <!-- Booking Button -->
							<div>
              					<button type="submit" class="btn btn-main mt-20 btn-solid-border" style="background-color:lightsteelblue;" name="bookComfy">Book Now!</button>
							</div>  
                            
                    </div>
                </div>
                    
            </div>
            &nbsp;
            &nbsp;
                
            <!-- 7 - Sun Palace Inn -->
            <div class="row">
                
                <!-- Hotel Image -->
                <div class="col-md-5">
                    <div class="single-product-details text-center">
                        <img src="../images/SPI.jpg" alt="product-img" width="450" height="400">
                    </div>
                </div>
                
                <!-- Hotel Info -->
                <div class="col-md-7">
                    <div class="single-product-details">
						<h2><b>Sun Palace Inn</b></h2>
						<form class="text-left clearfix" action="" method="post">
                            
                        	<!-- Amenities -->
                        	<div class="product-info">
                            	<span><b>Amenities:</b></span>
                            	<ul class="list-inline mt-10">
                                	<li><p>Pool 			&#127946;</p></li>
                                	<li><p>Gym 				&#127947;</p></li>
                            	</ul>
                       	    </div>
                            
                        	<!-- Room Type -->
                        	<div>
                           		<span><b>Room Type: </b></span>					
                            	<select name="sunRoomType">
									<option value="">Select...</option>
                                	<option value="Standard">Standard</option>
                                	<option value="Queen">Queen</option>
                                	<option value="King">King</option>
                            	</select>				
                        	</div>
                            
                        	<!-- Booking Date -->
                        	<div>
                            	<ul class="list-inline mt-10">
                                	<!-- Check-in -->
                                	<li>									
                                    	<div class="form-group">
                                        	<span>Check-in</span>
                                        	<input type="date" class="form-control" name="sunstartdate">
                                    	</div>									
                                	</li>
                                	<!-- Check-out -->
                                	<li>
                                    	<span>Check-out</span>
                                    	<input type="date" class="form-control" name="sunenddate">			
                                	</li>
                            	</ul>
                        	</div>
                            
                            <!-- Booking Button -->
							<div>
              					<button type="submit" class="btn btn-main mt-20 btn-solid-border" style="background-color:lightsteelblue;" name="bookSun">Book Now!</button>
							</div>  
                            
                    </div>
                </div>
                    
            </div>
            &nbsp;
            &nbsp;
                
            <!-- 8 - HomeAway Inn -->
            <div class="row">
                
                <!-- Hotel Image -->
                <div class="col-md-5">
                    <div class="single-product-details text-center">
                        <img src="../images/HAI.jpg" alt="product-img" width="450" height="400">
                    </div>
                </div>
                
                <!-- Hotel Info -->
                <div class="col-md-7">
                    <div class="single-product-details">
						<h2><b>HomeAway Inn</b></h2>
						<form class="text-left clearfix" action="" method="post">
                            
                        	<!-- Amenities -->
                        	<div class="product-info">
                            	<span><b>Amenities:</b></span>
                            	<ul class="list-inline mt-10">
                                	<li><p>Pool 			&#127946;</p></li>
                                	<li><p>Business Office &#128187;</p></li>
                            	</ul>
                       	    </div>
                            
                        	<!-- Room Type -->
                        	<div>
                           		<span><b>Room Type: </b></span>					
                            	<select name="homeRoomType">
									<option value="">Select...</option>
                                	<option value="Standard">Standard</option>
                            	</select>				
                        	</div>
                            
                        	<!-- Booking Date -->
                        	<div>
                            	<ul class="list-inline mt-10">
                                	<!-- Check-in -->
                                	<li>									
                                    	<div class="form-group">
                                        	<span>Check-in</span>
                                        	<input type="date" class="form-control" name="homestartdate">
                                    	</div>									
                                	</li>
                                	<!-- Check-out -->
                                	<li>
                                    	<span>Check-out</span>
                                    	<input type="date" class="form-control" name="homeenddate">			
                                	</li>
                            	</ul>
                        	</div>
                            
                            <!-- Booking Button -->
							<div>
              					<button type="submit" class="btn btn-main mt-20 btn-solid-border" style="background-color:lightsteelblue;" name="bookHome">Book Now!</button>
							</div>  
                            
                    </div>
                </div>
                    
            </div>
            &nbsp;
            &nbsp;
                
            <!-- 9 - Rio Inn -->
            <div class="row">
                
                <!-- Hotel Image -->
                <div class="col-md-5">
                    <div class="single-product-details text-center">
                        <img src="../images/RI.jpg" alt="product-img" width="450" height="400">
                    </div>
                </div>
                
                <!-- Hotel Info -->
                <div class="col-md-7">
                    <div class="single-product-details">
						<h2><b>Rio Inn</b></h2>
						<form class="text-left clearfix" action="" method="post">
                        
                        <!-- Amenities -->
                        <div class="product-info">
                            <span><b>Amenities:</b></span>
                            <ul class="list-inline mt-10">
                                <li><p>Pool 			&#127946;</p></li>
                            </ul>
                        </div>
                        
                        <!-- Room Type -->
                        <div>
                            <span><b>Room Type: </b></span>
                            <select name="rioRoomType">
                                <option value="">Select...</option>
                                <option value="Standard">Standard</option>
                                <option value="Queen">Queen</option>
                                <option value="King">King</option>
                            </select>
                        </div>
                        
                        <!-- Booking Date -->
                        <div>
                            <ul class="list-inline mt-10">
                                <!-- Check-in -->
                                <li>
                                    <div class="form-group">
                                        <span>Check-in</span>
                                        <input type="date" class="form-control" name="riostartdate">
                                    </div>
                                </li>
                                <!-- Check-out -->
                                <li>
                                    <span>Check-out</span>
                                    <input type="date" class="form-control" name="rioenddate">
                                </li>
                            </ul>
                        </div>
                        
                        <!-- Booking Button -->
                        <div>
                            <button type="submit" class="btn btn-main mt-20 btn-solid-border" style="background-color:lightsteelblue;" name="bookRio">Book Now!</button>
                        </div>
                        
                    </div>
                </div>
            </div>
            &nbsp;
            &nbsp;
                
        </div>
        
    </section>
        
    <!-- FOOTER -->
    <footer class="footer section text-center" style="background-color:lightsteelblue;">
        
        <div class="row">
            <!-- Credits -->
            <p>
                <a href="/credits/">CREDITS</a>
            
            </p>
            
            <!-- Copyright -->
            <p class="copyright-text">Copyright ©2021, Designed &amp; Developed by <a href="https://themefisher.com/">Themefisher</a>
            </p>
        
        </div>
    
    </footer>
        
    <!-- Essential Scripts -->
    
    <!-- Main jQuery -->
    <script src="../assets/plugins/jquery/dist/jquery.min.js"></script>
        
    <!-- Bootstrap 3.1 -->
    <script src="../assets/plugins/bootstrap/js/bootstrap.min.js"></script>
        
    <!-- Bootstrap Touchpin -->
    <script src="../assets/plugins/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js"></script>
        
    <!-- slick Carousel -->
    <script src="../assets/plugins/slick/slick.min.js"></script>
    <script src="../assets/plugins/slick/slick-animation.min.js"></script>
        
    <!-- Main Js File -->
    <script src="js/script.js"></script>

</body>
</html>

<?php
	/*******MJ Healey******/
	$email=$_REQUEST['email'];// this is the way to use the var passed in the target page
	$pwd=$_REQUEST['pwd'];
	$userType=$_REQUEST['userType'];
    $name=$_REQUEST['name'];
    $last=$_REQUEST['last'];
    $userID=$_REQUEST['userID']; 
    $employeeID=$_REQUEST['employeeID'];
									   
	/*booking in Magnolia Hotel*/
	if (isset($_POST['bookMagnolia'])){
		$hotelID=0;		
		$roomType=$_POST['magnoliaRoomType'];
		$startDate=$_POST['magnoliastartdate'];	
	 	$endDate=$_POST['magnoliaenddate'];
		booking($email,$pwd,$userType,$name,$last,$userID,$employeeID,$hotelID,$roomType,$startDate,$endDate);
	}
    
    /*booking in The Lofts at Town Centre */
    if (isset($_POST['bookLofts'])){
		$hotelID=1;		
		$roomType=$_POST['loftsRoomType'];
		$startDate=$_POST['loftsstartdate'];	
	 	$endDate=$_POST['loftsenddate'];
		booking1($email,$pwd,$userType,$name,$last,$userID,$employeeID,$hotelID,$roomType,$startDate,$endDate);
	}
                                       
    /*booking in Park North Hotel*/
    if (isset($_POST['bookPark'])){
		$hotelID=2;		
		$roomType=$_POST['parkRoomType'];
		$startDate=$_POST['parkstartdate'];	
	 	$endDate=$_POST['parkenddate'];
		booking2($email,$pwd,$userType,$name,$last,$userID,$employeeID,$hotelID,$roomType,$startDate,$endDate);
	}
    
    /*booking in The Courtyard Suites*/
    if (isset($_POST['bookCourt'])){
		$hotelID=3;		
		$roomType=$_POST['courtRoomType'];
		$startDate=$_POST['courtstartdate'];	
	 	$endDate=$_POST['courtenddate'];
		booking3($email,$pwd,$userType,$name,$last,$userID,$employeeID,$hotelID,$roomType,$startDate,$endDate);
	}
                                       
    /*booking in The Regency Rooms*/
    if (isset($_POST['bookRegency'])){
		$hotelID=4;		
		$roomType=$_POST['regencyRoomType'];
		$startDate=$_POST['regencystartdate'];	
	 	$endDate=$_POST['regencyenddate'];
		booking4($email,$pwd,$userType,$name,$last,$userID,$employeeID,$hotelID,$roomType,$startDate,$endDate);
	}
                                       
    /*booking in Town Inn Budget Rooms*/
    if (isset($_POST['bookTown'])){
		$hotelID=5;		
		$roomType=$_POST['townRoomType'];
		$startDate=$_POST['townstartdate'];	
	 	$endDate=$_POST['townenddate'];
		booking5($email,$pwd,$userType,$name,$last,$userID,$employeeID,$hotelID,$roomType,$startDate,$endDate);
	}
                                       
    /*booking in The Comfy Motel Place*/
    if (isset($_POST['bookComfy'])){
		$hotelID=6;		
		$roomType=$_POST['comfyRoomType'];
		$startDate=$_POST['comfystartdate'];	
	 	$endDate=$_POST['comfyenddate'];
		booking6($email,$pwd,$userType,$name,$last,$userID,$employeeID,$hotelID,$roomType,$startDate,$endDate);
	}
                                       
    /*booking in Sun Palace Inn*/
    if (isset($_POST['bookSun'])){
		$hotelID=7;		
		$roomType=$_POST['sunRoomType'];
		$startDate=$_POST['sunstartdate'];	
	 	$endDate=$_POST['sunenddate'];
		booking7($email,$pwd,$userType,$name,$last,$userID,$employeeID,$hotelID,$roomType,$startDate,$endDate);
	}
                                       
    /*booking in HomeAway Inn*/
    if (isset($_POST['bookHome'])){
		$hotelID=8;		
		$roomType=$_POST['homeRoomType'];
		$startDate=$_POST['homestartdate'];	
	 	$endDate=$_POST['homeenddate'];
		booking8($email,$pwd,$userType,$name,$last,$userID,$employeeID,$hotelID,$roomType,$startDate,$endDate);
	}
                                      
    /*Booking in Rio Inn */
	if (isset($_POST['bookRio'])){
		$hotelID=9;		
		$roomType=$_POST['rioRoomType'];
		$startDate=$_POST['riostartdate'];	
	 	$endDate=$_POST['rioenddate'];
		booking9($email,$pwd,$userType,$name,$last,$userID,$employeeID,$hotelID,$roomType,$startDate,$endDate);
	}
?>