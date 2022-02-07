<?php
include("../functions.php");
session_start();

/*variables sent from Reservations*/
$email=$_REQUEST['email'];
$pwd=$_REQUEST['pwd'];
$userType=$_REQUEST['userType'];
$name=$_REQUEST['name'];
$last=$_REQUEST['last'];
$userID=$_REQUEST['userID']; 
$employeeID=$_REQUEST['employeeID'];


$reservationid=$_REQUEST['reservationid'];// this is the way to use the var passed in the target page
$startdate=$_REQUEST['startdate'];
$enddate=$_REQUEST['enddate'];
$hotelname=$_REQUEST['hotelname'];
$roomtype=$_REQUEST['roomtype'];
$roomNum=$_REQUEST['roomNum'];
$hotelID=$_REQUEST['hotelID'];

?>

<!DOCTYPE html>
<!--MODIFY RESERVATION *******MJ Healey*******-->
<html lang="en">
<head>

  <!-- Basic Page Needs
  ================================================== -->
  <meta charset="utf-8">
  <title>G8 Booking | SE Group 8</title>

  <!-- Mobile Specific Metas
  ================================================== -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="Construction Html5 Template">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
  <meta name="author" content="Themefisher">
  <meta name="generator" content="Themefisher Constra HTML Template v1.0">
  
  <!-- Favicon -->
  <link rel="shortcut icon" type="image/x-icon" href="../assets/images/favicon.png" />
  
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

<body id="body" style="background-color:lightsteelblue;">

<section class="signin-page account">
  <div class="container">
    <div class="row">
      <div class="col-md-6 col-md-offset-3">
        <div class="block text-center" style="background-color:floralwhite;">
          <a class="logo" href="../">
          	<h1><b>G8 Booking</b></h1>
          </a>
          <h2 class="text-center">Modify Reservation</h2>
          <form class="text-left clearfix" action="" method="post">
			  
            <div class="form-group"><label>ReservationID#</label>
              <input type="text" class="form-control" name="reservationid" required readonly value=<?php echo $reservationid;?> > 
            </div>
			  
            <div class="form-group"><label>Start Date </label>
              <input type="date" class="form-control" name="startdate" required value=<?php echo $startdate;?>>
			</div>
			  
            <div class="form-group"> <label>End Date </label>
              <input type="date" class="form-control" name="enddate" required value=<?php echo $enddate;?>>
            </div>
			  
            <div class="form-group"><label>Hotel </label>
              <input type="text" class="form-control" name="hotelname" required readonly value=<?php echo "'$hotelname'";?>>
            </div>
			  
			<div class="form-group"><label>Type Room </label>
              <input type="text" class="form-control" name="typeroom" required value=<?php echo $roomtype;?>>
            </div>
			  
			<div class="text-center">
              <button type="submit" class="btn btn-main text-center btn-solid-border" name="modify" style="background-color:lightsteelblue;">Modify Reservation</button>
            </div></a>
			&nbsp
			&nbsp
			<div class="text-center">
              <button type="submit" class="btn btn-main text-center btn-solid-border" name="cancel" style="background-color:lightsteelblue;">Cancel Reservation</button>
            </div></a>
		    &nbsp
			&nbsp
		  	<div class="text-center">
              <button type="submit" class="btn btn-default btn-solid-border" name="back" style="background-color:lightsteelblue;">Back to Reservations</button>
            </div></a>
		    
	  
        </div>
      </div>
    </div>
  </div>
</section>

    <!-- 
    Essential Scripts
    =====================================-->
    
    <!-- Main jQuery -->
    <script src="../assets/plugins/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap 3.1 -->
    <script src="../assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <!-- Bootstrap Touchpin -->
    <script src="../assets/plugins/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js"></script>
    <!-- Instagram Feed Js -->
    <script src="../assets/plugins/instafeed/instafeed.min.js"></script>
    <!-- Video Lightbox Plugin -->
    <script src="../assets/plugins/ekko-lightbox/dist/ekko-lightbox.min.js"></script>
    <!-- Count Down Js -->
    <script src="../assets/plugins/syo-timer/build/jquery.syotimer.min.js"></script>

    <!-- slick Carousel -->
    <script src="../assets/plugins/slick/slick.min.js"></script>
    <script src="../assets/plugins/slick/slick-animation.min.js"></script>

    <!-- Google Mapl -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCC72vZw-6tGqFyRhhg5CkF2fqfILn2Tsw"></script>
    <script type="text/javascript" src="../assets/plugins/google-map/gmap.js"></script>

    <!-- Main Js File -->
    <script src="../assets/js/script.js"></script>

  </body>
  </html>
<!DOCTYPE>

<?php
/*variables sent from Reservations*/
$reservationid=$_REQUEST['reservationid'];// this is the way to use the var passed in the target page
$startdate=$_REQUEST['startdate'];
$enddate=$_REQUEST['enddate'];
$hotelname=$_REQUEST['hotelname'];
$roomtype=$_REQUEST['roomtype'];
$roomNum=$_REQUEST['roomNum'];
$hotelID=$_REQUEST['hotelID'];
				   
$email=$_REQUEST['email'];
$pwd=$_REQUEST['pwd'];
$userType=$_REQUEST['userType'];
$name=$_REQUEST['name'];
$last=$_REQUEST['last'];
$userID=$_REQUEST['userID']; 
$employeeID=$_REQUEST['employeeID'];

/*isset(): Check whether a variable is empty. Also check whether the variable is set/declared
$_POST is a super global variable which is used to collect form data after submitting an HTML form with method="post". $_POST is also widely used to pass variables*/
if (isset($_POST['back'])){
	redirect("../reservations/index.php?email=$email&pwd= $pwd&userType=$userType&name=$name&last=$last&userID=$userID&employeeID=$employeeID");
	
}

$dblink=db_iconnect("hoteldB");

if (isset($_POST['modify']))
{ 	
	/*$_REQUEST is a super global variable which is used to collect data after submitting an HTML form*/
    $reservationid=$_REQUEST['reservationid'];// this is the way to use the var passed in the target page
	$startdate=$_REQUEST['startdate'];
	$enddate=$_REQUEST['enddate'];
	$hotelname=$_REQUEST['hotelname'];
	$typeroom=$_REQUEST['typeroom'];
	
	/*find available typeroom to update reservation*/
	$findRoom="SELECT * FROM ROOMS WHERE Type='$typeroom'"; 
	$resultFindRoom = mysqli_query($dblink, $findRoom);
	$numRows=mysqli_num_rows($resultFindRoom); 
	if ($numRows>0){ //if there's an available room of the specify type 
		while($row = mysqli_fetch_array($resultFindRoom)){
			if (strcmp($row['Status'],"available")==0){ //found an available room
				$roomid=$row['RoomID'];
				break;
			}
			
		}		
		/*update reservation*/
		$updateReservation="UPDATE RESERVATION SET StartDate='$startdate',EndDate='$enddate',RoomID='$roomid' WHERE ReservationID='$reservationid'"; 
    	if(mysqli_query($dblink,$updateReservation)){ //update successful
			/*free previos room and update new room status*/
			$freeRoomQ="UPDATE ROOMS SET Status='available' WHERE RoomID='$roomNum'";
			if ($dblink->query($freeRoomQ) === TRUE) {
  				echo "Room status updated successfully";
			} else {
  				echo "Error updating room status: " . $conn->error;
			}
			
			$bookRoomQ="UPDATE ROOMS SET Status='booked' WHERE RoomID='$roomid'";
			if ($dblink->query($bookRoomQ) === TRUE) {
  				echo "Room status updated successfully";
			} else {
  				echo "Error updating room status: " . $conn->error;
			}
			
			/*go back to reservations*/
			redirect("../reservations/index.php?email=$email&pwd=$pwd&userType=$userType&name=$name&last=$last&userID=$userID&employeeID=$employeeID"); 
    	} else{
			echo "<script>alert('Could not query $sql<br>'.$dblink->error)</script>";
		}
	}else{
		echo "<script>alert('There's no available room of the type specified)</script>";
	}
	
}
if (isset($_POST['cancel']))
{
	/*free room*/
	$freeRoomQ="UPDATE ROOMS SET Status='available' WHERE RoomID='$roomNum'";
	if ($dblink->query($freeRoomQ) === TRUE) {
  		echo "Room status updated successfully";
	} else {
  		echo "Error updating room status: " . $conn->error;
	}
												
	/*delete reservation*/												
	$cancelReservation="DELETE FROM RESERVATION WHERE ReservationID=$reservationid";
	if ($dblink->query($cancelReservation) === TRUE) {
  		echo "Record deleted successfully";
	} else {
  		echo "Error deleting record: " . $conn->error;
	}
												
	/*back to displaying reservations*/
	redirect("../reservations/index.php?email=$email&pwd=$pwd&userType=$userType&name=$name&last=$last&userID=$userID&employeeID=$employeeID&reservationid=$reservationid&startdate=$startD&enddate=$endD&hotelname=$hotelName&roomtype=$roomtype");	
}
?>