<!-- UPDATE PROFILE -->
<?php
include("../functions.php");
session_start();

/*variables sent from userProfile*/
$email=$_REQUEST['email'];// this is the way to use the var passed in the target page
$pwd=$_REQUEST['pwd'];
$userType=$_REQUEST['userType'];
$name=$_REQUEST['name'];
$last=$_REQUEST['last'];
$userID=$_REQUEST['userID']; 
$employeeID=$_REQUEST['employeeID'];

?>

<!DOCTYPE html>
<!--UPDATE PROFILE *******MJ Healey*******-->
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
    
    <!-- FAVICON -->
    <link href="images/favicon.png" rel="shortcut icon">
    
    <!-- Themefisher Icon font -->
    <link rel="stylesheet" href="../assets/plugins/themefisher-font/style.css">
    
    <!-- bootstrap.min css -->
    <link rel="stylesheet" href="../assets/plugins/bootstrap/css/bootstrap.min.css">
  
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
</head>

<body id="body" style="background-color:lightsteelblue;">

<section class="signin-page account">
  <div class="container">
    <div class="row">
      <div class="col-md-6 col-md-offset-3">
        <div class="block text-center" style="background-color:floralwhite;">
          <a class="logo" href="../">
          	<h1><b>G8 Booking</b></h1>
          </a>
          <h2 class="text-center">Update Your Account</h2>
          <form class="text-left clearfix" action="" method="post">
			  <?php
			  	/*strstr() :Find the first occurrence of "firstNull" inside "err" and return the rest of the string*/
			  	if (isset($_REQUEST['err']) && strstr($_REQUEST['err'],"firstNull"))
					echo '<div class="alert alert-danger">First name cannot be blank!</div>';
			  ?>
            <div class="form-group"><label>Name </label>
              <input type="text" class="form-control" name="name" required value=<?php echo "'$name'";?> > 
            </div>
			  <?php
			  	if (isset($_REQUEST['err']) && strstr($_REQUEST['err'],"lastNull"))
					echo '<div class="alert alert-danger">Last name cannot be blank!</div>';
			  ?>
            <div class="form-group"><label>Last Name </label>
              <input type="text" class="form-control" name="last" required value=<?php echo "'$last'";?>>
			</div>
			  <?php
			  	if (isset($_REQUEST['err']) && strstr($_REQUEST['err'],"emailNull"))
					echo '<div class="alert alert-danger">Email cannot be blank!</div>';
			  ?>
            <div class="form-group"> <label>Email </label>
              <input type="email" class="form-control" name="email" required value=<?php echo $email;?>>
            </div>
			  <?php
			  	if (isset($_REQUEST['err']) && strstr($_REQUEST['err'],"pwdNull"))
					echo '<div class="alert alert-danger">Password cannot be blank!</div>';
			  ?>
            <div class="form-group"><label>Password </label>
              <input type="password" class="form-control" name="pwd" required value=<?php echo $pwd;?>>
            </div>
			  
            <div class="text-center">
              <button type="submit" class="btn btn-main text-center btn-solid-border" name="updateprofile" style="background-color:lightsteelblue;">Update</button>
            </div></a>
			&nbsp
			&nbsp
			<div class="text-center">
              <button type="submit" class="btn btn-main text-center btn-solid-border" name="cancel" style="background-color:lightsteelblue;">Cancel</button>
            </div></a>
          
        </div>
      </div>
    </div>
  </div>
</section>
    
  </body>
  </html>
<!DOCTYPE>

<?php
/*isset(): Check whether a variable is empty. Also check whether the variable is set/declared
$_POST is a super global variable which is used to collect form data after submitting an HTML form with method="post". $_POST is also widely used to pass variables*/
if (isset($_POST['updateprofile']))
{
	$err="";
	if ($name=$_POST['name']=="")
		$err.="firstNull";
	if ($last=$_POST['last']=="")
		$err.="lastNull";
	if ($email=$_POST['email']=="")
		$err.="emailNull";
	if ($pwd=$_POST['pwd']=="")
		$err.="pwdNull";
	if ($err!="")
		redirect("index.php?email=$email&last=$last&name=$name&pwd=$pwd");
	
	/*$_REQUEST is a super global variable which is used to collect data after submitting an HTML form*/
    $email=$_REQUEST['email'];// this is the way to use the var passed in the target page
	$name=$_REQUEST['name'];
	$last=$_REQUEST['last'];
	$pwd=$_REQUEST['pwd'];
	$userID=$_REQUEST['userID'];
	$employeeID=$_REQUEST['employeeID'];

	if (strcmp("Customer",$userType)==0){
		/*update user in the dB*/ 
    	$updateQuery="UPDATE USER SET UName='$name',ULastName='$last',Email='$email',Password='$pwd' WHERE UserID='$userID'"; 	
	}else{
		$updateQuery="UPDATE EMPLOYEE SET EName='$name',ELastName='$last',Eemail='$email',Epassword='$pwd' WHERE EmployeeID='$employeeID'"; 
	}	
	$dblink=db_iconnect("hoteldB");		
    if(mysqli_query($dblink,$updateQuery)){ //update successful
		/*go back to userProfile*/
		redirect("../userProfile/index.php?email=$email&pwd=$pwd&userType=$userType&name=$name&last=$last&userID=$userID&employeeID=$employeeID"); 
    } else{
		echo "<script>alert('Could not query $sql<br>'.$dblink->error)</script>";
	}
} elseif (isset($_POST['cancel'])){
	redirect("../userProfile/index.php?email=$email&pwd=$pwd&userType=$userType&name=$name&last=$last&userID=$userID&employeeID=$employeeID"); 	
}
?>