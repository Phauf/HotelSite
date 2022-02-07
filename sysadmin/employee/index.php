<?php
include("../../functions.php");
?>

<!DOCTYPE html>
<!-- EMPLOYEE *******MJ Healey*******-->
<html lang="en">
    
<!-- HEAD -->
<head>
    <meta charset="UTF-8">
    <!-- Title for tab -->
    <title>G8 Booking | SE Group 8</title>
    
    <!-- Mobile Specific Metas -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Construction Html5 Template">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="author" content="Themefisher">
    <meta name="generator" content="Themefisher Constra HTML Template v1.0">
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="../../images/favicon.png">
    
    <!-- Themefisher Icon font -->
    <link rel="stylesheet" href="../../assets/plugins/themefisher-font/style.css">
    
    <!-- bootstrap.min css -->
    <link rel="stylesheet" href="../../assets/plugins/bootstrap/css/bootstrap.min.css">
    
    <!-- Animate css -->
    <link rel="stylesheet" href="../../assets/plugins/animate/animate.css">
    
    <!-- Slick Carousel -->
    <link rel="stylesheet" href="../../assets/plugins/slick/slick.css">
    <link rel="stylesheet" href="../../assets/plugins/slick/slick-theme.css">
  
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="../../assets/css/style.css">
    
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
                        <a href="index.php">
                            <h1><b>G8 Booking</b></h1>
                        </a>
                    </div>
                    
                </div>
                
                <!-- 3/3 Sign in & Sign up -->
                <div class="col-md-4 col-xs-12 col-sm-4">
                    
                </div>
                
            </div>
        </div>
    </section>
    
    <section class="signin-page account">
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
        
                    <div class="block text-center" style="background-color:floralwhite;">
                        
                        <a class="logo" href="../">
                            <h1><b>G8 Booking</b></h1>
                        </a>
                        
                        <h2 class="text-center">Create Employee Account</h2>
                        
                        <form class="text-left clearfix" action="" method="post">  
							<div class="form-group"> <label>Employee ID </label>
                                <input type="text" class="form-control" name="employeeID" placeholder="Magnolia01" required>
                            </div>
							<div class="form-group"> <label>Hotel ID </label>
                                <input type="text" class="form-control" name="hotelID" placeholder="0" required>
                            </div>
                            
                            <div class="form-group"><label>Employee Name </label>
                                <input type="text" class="form-control" name="employeename" placeholder="Name" required>
                            </div>
            
                            <div class="form-group"><label>Employee Last Name </label>
                                <input type="text" class="form-control" name="employeelName" placeholder="Last Name" required>
                            </div>
            
                            <div class="form-group"><label>Email </label>
                                <input type="email" class="form-control" name="eEmail" placeholder="Email" required>
                            </div>
                            
                            <div class="form-group"><label>Password </label>
                                <input type="password" class="form-control" name="password" placeholder="Password" required>
                            </div>
                            
                            <div class="text-center">
                                <button type="submit" class="btn btn-main text-center btn-solid-border" name="create" style="background-color:lightsteelblue;">Create</button>
                            </div>
                            
                        </form>
                    
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
            <p class="copyright-text">Copyright © 2021, Designed &amp; Developed by <a href="https://themefisher.com/">Themefisher</a>
            </p>
        
        </div>
    
    </footer>

     <!-- Essential Scripts -->
    
    <!-- Main jQuery -->
    <script src="../../assets/plugins/jquery/dist/jquery.min.js"></script>
    
    <!-- Bootstrap 3.1 -->
    <script src="../../assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    
    <!-- Bootstrap Touchpin -->
    <script src="../../assets/plugins/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js"></script>
    
    <!-- Video Lightbox Plugin -->
    <script src="../../assets/plugins/ekko-lightbox/dist/ekko-lightbox.min.js"></script>
    
    <!-- Count Down Js -->
    <script src="../../assets/plugins/syo-timer/build/jquery.syotimer.min.js"></script>

    <!-- slick Carousel -->
    <script src="../../assets/plugins/slick/slick.min.js"></script>
    <script src="../../assets/plugins/slick/slick-animation.min.js"></script>

    <!-- Main Js File -->
    <script src="../../assets/js/script.js"></script>

  </body>
  </html>
<!DOCTYPE>

<?php
/*isset(): Check whether a variable is empty. Also check whether the variable is set/declared
$_POST is a super global variable which is used to collect form data after submitting an HTML form with method="post". $_POST is also widely used to pass variables*/
if (isset($_POST['create']))
{	
	/*$_REQUEST is a super global variable which is used to collect data after submitting an HTML form*/
	$employeeID=$_REQUEST['employeeID'];
	$hotelID=$_REQUEST['hotelID'];
	$ename=$_REQUEST['employeename'];
	$elname=$_REQUEST['employeelName'];
	$eEmail=$_REQUEST['eEmail'];
	$password=$_REQUEST['password'];
 
	/*Check if the employee already has an account*/ 
    $checkEmployeeQuery="SELECT * FROM EMPLOYEE WHERE Eemail='$eEmail'";  
	$dblink=db_iconnect("hoteldB");	
	$resultChecking=$dblink->query($checkEmployeeQuery) or
		die("Could not query $sql<br>".$dblink->error);
	$infoResCheck=$resultChecking->fetch_array(MYSQLI_ASSOC);
  
    if($infoResCheck['Eemail'] ){ 
		echo "<script>alert('Employee already have an account')</script>";
		redirect("index.php");
		
    }else{
		/*insert new employee in the dB*/ 
    	$insertEmployee="INSERT INTO EMPLOYEE (EmployeeID,HotelID,EName,ELastName,Eemail,Epassword) VALUE ('$employeeID','$hotelID','$ename','$elname','$eEmail','$password')"; 
		
    	if(mysqli_query($dblink,$insertEmployee)){ //employee account creation successful
			redirect("../../sysadmin/index.php"); // go to sysadmin page
		}else{
			echo "<script>alert('Employee account could not be created $sql<br>'.$dblink->error)</script>";
			redirect("index.php");
		}
	} 

}
