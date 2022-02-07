<?php
include("../functions.php");
session_start();// Initialize the session

//$_SESSION = array();// Unset all of the session variables
?>

<!DOCTYPE html>
<!-- SIGN IN *******MJ Healey*******-->
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
    <link rel="shortcut icon" type="image/x-icon" href="/images/favicon.png">
    
    <!-- Themefisher Icon font -->
    <link rel="stylesheet" href="../assets/plugins/themefisher-font/style.css">
    
    <!-- bootstrap.min css -->
    <link rel="stylesheet" href="../assets/plugins/bootstrap/css/bootstrap.min.css">
    
    <!-- Animate css -->
    <link rel="stylesheet" href="../assets/plugins/animate/animate.css">
    
    <!-- Slick Carousel -->
    <link rel="stylesheet" href="../assets/plugins/slick/slick.css">
    <link rel="stylesheet" href="../assets/plugins/slick/slick-theme.css">
  
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
</head>
    
<!-- BODY -->
<body id="body" style="background-color:lightsteelblue;">
    <section class="signin-page account">
        <div class="container">
            <div class="row">
                
                <div class="col-md-6 col-md-offset-3">
        				
                    <div class="block text-center" style="background-color:floralwhite;">
                        
                        <a class="logo" href="../">
                            <h1><b>G8 Booking</b></h1>
                        </a>
                        
                        <h2 class="text-center">Welcome Back</h2>
                        
                        <form class="text-left clearfix" action="" method="post">
                            <?php
			  					if (isset($_REQUEST['err']) && strstr($_REQUEST['err'],"emailNull"))
									echo '<div class="alert alert-danger">Email cannot be blank!</div>';
			  				?>
                            <div class="form-group">
                                <input type="email" class="form-control" placeholder="Email" name="email" required>
                            </div>
							<?php
			  					if (isset($_REQUEST['err']) && strstr($_REQUEST['err'],"pwdNull"))
									echo '<div class="alert alert-danger">Password cannot be blank!</div>';
			  				?>
                            <div class="form-group">
                                <input type="password" class="form-control" placeholder="Password" name="pwd" required>
                            </div>
                            
                            <div class="text-center" >
                                <button type="submit" class="btn btn-main text-center btn-solid-border" name="login" style="background-color:lightsteelblue;">Login</button>
                            </div>
                        
                        </form>
                        
                        <p class="mt-20">New in this site? <a href="../signup/" style="color:blue;">Create Account</a></p>
                        
        				</div>
                    
      				</div>
                
    			</div>
  			</div>
		</section>
        
    <!-- Essential Scripts -->
    
    <!-- Main jQuery -->
    <script src="../assets/plugins/jquery/dist/jquery.min.js"></script>
    
    <!-- Bootstrap 3.1 -->
    <script src="../assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    
    <!-- Bootstrap Touchpin -->
    <script src="../assets/plugins/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js"></script>
    
    <!-- Video Lightbox Plugin -->
    <script src="../assets/plugins/ekko-lightbox/dist/ekko-lightbox.min.js"></script>
    
    <!-- Count Down Js -->
    <script src="../assets/plugins/syo-timer/build/jquery.syotimer.min.js"></script>

    <!-- slick Carousel -->
    <script src="../assets/plugins/slick/slick.min.js"></script>
    <script src="../assets/plugins/slick/slick-animation.min.js"></script>

    <!-- Main Js File -->
    <script src="../assets/js/script.js"></script>
        
	</body>
</html>
<!DOCTYPE>

<?php
/*isset(): Check whether a variable is empty. Also check whether the variable is set/declared
$_POST is a super global variable which is used to collect form data after submitting an HTML form with method="post". $_POST is also widely used to pass variables*/
if (isset($_POST['login']))
{
	$err="";
	$userType="";
	
	if ($email=$_POST['email']=="")
		$err.="emailNull";
	if ($pwd=$_POST['pwd']=="")
		$err.="pwdNull";
	if ($err!="")
		redirect("index.php?email=$email&pwd=$pwd");
	
	/*$_REQUEST is a super global variable which is used to collect data after submitting an HTML form*/
    $email=$_REQUEST['email'];// this is the way to use the var passed in the target page
	$pwd=$_REQUEST['pwd'];
	
	/*if the person logging in is a customer*/
	$dblink=db_iconnect("hoteldB");	
	$userQuery="SELECT * FROM USER WHERE Email='$email'AND Password='$pwd'"; 	
	$resultUser=$dblink->query($userQuery) or
		die("Could not query $sql<br>".$dblink->error);
	$infoUser=$resultUser->fetch_array(MYSQLI_ASSOC);// contains $infoUser['UserID'],$infoUser['UName'],$infoUser['ULastName'],etc
	
	 if($infoUser['Email'] && $infoUser['Password'])//if the email and pwd entered are in USER table 
    {  
		//echo "From Customer: $email: $pwd<br>";
		$userType="Customer";
		$name=$infoUser['UName'];
		$last=$infoUser['ULastName'];
		$UserID=$infoUser['UserID'];
		redirect("../userProfile/index.php?email=$email&pwd=$pwd&userType=$userType&name=$name&last=$last&userID=$UserID");
  
    }else{ /*the person logging in might be an employee*/
		 $employeeQuery="SELECT * FROM EMPLOYEE WHERE Eemail='$email'AND Epassword='$pwd'";
		 $resultEmployee=$dblink->query($employeeQuery) or
		 	die("Could not query $sql<br>".$dblink->error);
		 $infoEmployee=$resultEmployee->fetch_array(MYSQLI_ASSOC);// contains $infoEmployee['EmployeeID'],$infoEmployee['HotelID'],$infoEmployee['EName'],etc
		 
		 if ($infoEmployee['Eemail'] && $infoEmployee['Epassword']){//if the email and pwd entered are in EMPLOYEE table
			//echo "From Employee: $email: $pwd<br>";
			$userType="Employee";
			$name=$infoEmployee['EName'];
			$last=$infoEmployee['ELastName'];
			$employeeID=$infoEmployee['EmployeeID'];
			redirect("../userProfile/index.php?email=$email&pwd=$pwd&userType=$userType&name=$name&last=$last&employeeID=$employeeID");
      	
    	} else{ /*the person trying to log in is not registered*/
			echo "<script>alert('Email or password is incorrect!')</script>"; 
	  		//redirect("index.php");
		}  
	 }
}
?>