<!-- USER PROFILE -->
<?php
include("../functions.php");
session_start();

/*variables sent from login*/
$email=$_REQUEST['email'];// this is the way to use the var passed in the target page
$pwd=$_REQUEST['pwd'];
$userType=$_REQUEST['userType'];
$name=$_REQUEST['name'];
$last=$_REQUEST['last'];
if (strcmp("Customer",$userType)==0){
	$userID=$_REQUEST['userID'];
	$employeeID="";
	
}else{
	$employeeID=$_REQUEST['employeeID'];
	$userID=-1;
}
?>

<!doctype html>
<!--userProfile *******MJ Healey*******-->
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
                        <a href="../index.php">
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
    <section class="menu" action="">
        <nav class="navbar navigation"> 
            <div class="container">
                
                <div class="navbar-header">
                    <h2 class="menu-title">Main Menu</h2>
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                </div>
                
                <div id="navbar" class="navbar-collapse collapse text-center">
                    <ul class="nav navbar-nav">
                        
					<li class="dropdown dropdown-slide">
						<a href="#!" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="350" role="button" aria-haspopup="true" aria-expanded="false">Booking <span class="tf-ion-ios-arrow-down"></span></a>
                        
                        <!-- Booking -->
                        <div class="dropdown-menu" style="background-color:floralwhite;">
                            <ul>
                                <li class="dropdown-header">Booking</li>
                                <li role="separator" class="divider"></li>
                                <li><a href="../hotels/index.php?email=<?php echo $email?>&pwd=<?php echo $pwd?>&userType=<?php echo $userType?>&name=<?php echo $name?>&last=<?php echo $last?>&userID=<?php echo $userID?>&employeeID=<?php echo $employeeID?>">Hotels</a></li>
                            </ul>
                        </div>
					</li>
					
                    </ul>  
                </div>
                
            </div>
        </nav>
    </section>

<!-- Welcome Tag -->
<section class="page-header" style="background-color:floralwhite;">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="content">
					<h1 class="page-name">Dashboard</h1>
					<ol class="breadcrumb">
						<li class="active"><span style="font-weight:bold" style="color:black"><?php echo "ACCOUNT TYPE: " .$userType;?></span></li>
					</ol>
					<div class="media-body">
							<h2 class="media-heading"><span style="font-weight:bold" style="color:black"><?php echo "Welcome " .$name." ".$last;?></span></h2>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- Profile Details -->
<section class="user-dashboard page-wrapper" action="">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="dashboard-wrapper user-dashboard">
					<div class="media">
						<div class="media-body"> 
							<h2 class="media-heading"><span style="font-weight:bold" style="color:black">PROFILE DETAILS</span></h2>
						</div>
						<div class="col-md-12">									
        					<ul class="list-inline dashboard-menu text-center"> 
								          						
								<td><a href="../reservations/index.php?email=<?php echo $email?>&pwd=<?php echo $pwd?>&userType=<?php echo $userType?>&name=<?php echo $name?>&last=<?php echo $last?>&userID=<?php echo $userID?>&employeeID=<?php echo $employeeID?>" class="btn btn-default btn-round-full" style="background-color:lightsteelblue; color:black; font-size: 20px;">Reservations</a></td>
								<!-- &nbsp is a space that doesn't break into a new line -->
								<!--&nbsp
								&nbsp -->
								
        					</ul> 										
        					<div class="dashboard-wrapper dashboard-user-profile">
            					<div class="media-body">
              						<ul class="user-profile-list">
                						<li><span>Name:</span><?php echo $name;?></li>
                						<li><span>Last Name:</span><?php echo $last;?></li>
                						<li><span>Email:</span><?php echo $email;?></li>
										<li><span>User Type:</span><?php echo $userType;?></li>	
										<br>
          								<td><a href="../updateprofile/index.php?email=<?php echo $email?>&pwd=<?php echo $pwd?>&userType=<?php echo $userType?>&name=<?php echo $name?>&last=<?php echo $last?>&userID=<?php echo $userID?>&employeeID=<?php echo $employeeID?>" class="btn btn-default btn-round-full" style="background-color:lightsteelblue; color:black; font-size: 20px;">Update</a></td>
              						</ul>
            					</div>
        					</div>
      					</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
    
     <!-- Footer -->
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
    
        <!-- 
    Essential Scripts
    =====================================-->
    
    <!-- Main jQuery -->
    <script src="../assets/plugins/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap 3.1 -->
    <script src="../assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <!-- Bootstrap Touchpin -->
    <script src="../assets/plugins/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js"></script>
    <!-- Instagram Feed Js 
    <script src="../assets/plugins/instafeed/instafeed.min.js"></script> -->
    <!-- Video Lightbox Plugin 
    <script src="../assets/plugins/ekko-lightbox/dist/ekko-lightbox.min.js"></script> -->
    <!-- Count Down Js 
    <script src="../assets/plugins/syo-timer/build/jquery.syotimer.min.js"></script> -->

    <!-- slick Carousel -->
    <script src="../assets/plugins/slick/slick.min.js"></script>
    <script src="../assets/plugins/slick/slick-animation.min.js"></script>

    <!-- Google Mapl 
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCC72vZw-6tGqFyRhhg5CkF2fqfILn2Tsw"></script>
    <script type="text/javascript" src="../assets/plugins/google-map/gmap.js"></script> -->

    <!-- Main Js File -->
    <script src="js/script.js"></script> 
  </body>
</html>
<!DOCTYPE>
