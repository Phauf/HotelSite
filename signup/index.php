<?php
include("../functions.php");
?>

<!DOCTYPE html>
<!-- SIGN UP *******MJ Healey*******-->
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
    <link rel="shortcut icon" type="image/x-icon" href="../images/favicon.png">
    
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
                        
                        <h2 class="text-center">Create Your Account</h2>
                        
                        <form class="text-left clearfix" action="" method="post">
                            
                            <?php
                            /*strstr() :Find the first occurrence of "firstNull" inside "err" and return the rest of the string*/
                            if (isset($_REQUEST['err']) && strstr($_REQUEST['err'],"firstNull"))
                                echo '<div class="alert alert-danger">First name cannot be blank!</div>';
                            ?>
                            
                            <div class="form-group">
                                <input type="text" class="form-control" name="name" placeholder="Name" required>
                            </div>
                            
                            <?php
                            if (isset($_REQUEST['err']) && strstr($_REQUEST['err'],"lastNull"))
                                echo '<div class="alert alert-danger">Last name cannot be blank!</div>';
                            ?>
            
                            <div class="form-group">
                                <input type="text" class="form-control" name="lName" placeholder="Last Name" required>
                            </div>
                            
                            <?php
                            if (isset($_REQUEST['err']) && strstr($_REQUEST['err'],"emailNull"))
                                echo '<div class="alert alert-danger">Email cannot be blank!</div>';
                            ?>
            
                            <div class="form-group">
                                <input type="email" class="form-control" name="email" placeholder="Email" required>
                            </div>
                            
                            <?php
                            if (isset($_REQUEST['err']) && strstr($_REQUEST['err'],"pwdNull"))
                                echo '<div class="alert alert-danger">Password cannot be blank!</div>';
                            ?>
            
                            <div class="form-group">
                                <input type="password" class="form-control" name="pwd" placeholder="Password" required>
                            </div>
                            
                            <?php
                            if (isset($_REQUEST['err']) && strstr($_REQUEST['err'],"duplicateUser"))
                                echo '<div class="alert alert-danger">Email already exists in our database, Please choose a different one!</div>';
                            ?>
                            
                            <div class="text-center">
                                <button type="submit" class="btn btn-main text-center btn-solid-border" name="register" style="background-color:lightsteelblue;">Sign Up</button>
                            </div>
                            
                        </form>
                        
                        <p class="mt-20">Already have an account? <a href="../signin/" style="color:blue;">Sign in</a></p>
                    
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
if (isset($_POST['register']))
{
	$err="";
	if ($name=$_POST['name']=="")
		$err.="firstNull";
	if ($lname=$_POST['lName']=="")
		$err.="lastNull";
	if ($email=$_POST['email']=="")
		$err.="emailNull";
	if ($pwd=$_POST['pwd']=="")
		$err.="pwdNull";
	if ($err!="")
		redirect("index.php?email=$email&lName=$lname&name=$name&pwd=$pwd");
	
	/*$_REQUEST is a super global variable which is used to collect data after submitting an HTML form*/
    $email=$_REQUEST['email'];// this is the way to use the var passed in the target page
	$name=$_REQUEST['name'];
	$lname=$_REQUEST['lName'];
	$pwd=$_REQUEST['pwd'];
 
	/*Check if the user is already registered*/ 
    $checkUserQuery="SELECT * FROM USER WHERE Email='$email'";  
	$dblink=db_iconnect("hoteldB");	
	$resultChecking=$dblink->query($checkUserQuery) or
		die("Could not query $sql<br>".$dblink->error);
	$infoResCheck=$resultChecking->fetch_array(MYSQLI_ASSOC);
  
    if($infoResCheck['Email'] ){ 
		$err.="duplicateUser";
		redirect("index.php?email=$email");
		
    }else{
		/*insert new user in the dB*/ 
    	$insertUser="INSERT INTO USER (UserID,UName,ULastName,Email,Password) VALUE (NULL,'$name','$lname','$email','$pwd')"; 
		
    	if(mysqli_query($dblink,$insertUser)){ //registration successful
			redirect("../signin/index.php"); // go to login page
    	}  
	} 

}
?>