<!-- RESERVATIONS -->
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

<!doctype html>
<!-- Reservations *******MJ Healey*******-->
<html lang="en">

<!-- HEAD -->
<head>
  <meta charset="UTF-8">
  <title>G8 Booking | SE Group 8</title>

  <!-- Mobile Specific Metas
  ================================================== -->
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
    <section class="menu">
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
					<h1 class="page-name">Reservations</h1>
					<ol class="breadcrumb">
						<li class="active"><?php echo "ACCOUNT TYPE: " .$userType;?></li>
					</ol>
					<div class="media-body">
							<h2 class="media-heading"><span style="font-weight:bold" style="color:black"><?php echo "Welcome " .$name." ".$last;?></span></h2>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- Reservation Section -->
<section class="user-dashboard page-wrapper">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<ul class="list-inline dashboard-menu text-center">
					<li><a href="../userProfile/index.php?email=<?php echo $email?>&pwd=<?php echo $pwd?>&userType=<?php echo $userType?>&name=<?php echo $name?>&last=<?php echo $last?>&userID=<?php echo $userID?>&employeeID=<?php echo $employeeID?>" class="btn btn-default btn-round-full" style="background-color:lightsteelblue; color:black; font-size: 20px;">Dashboard</a></li>
				</ul>
				<div class="dashboard-wrapper user-dashboard">
					<div class="table-responsive">
						<table class="table">
							<?php
								$dblink=db_iconnect("hoteldB");
						   		if (mysqli_connect_errno())
								{
									echo "Failed to connect to hoteldB: " . mysqli_connect_error();

								}
						   		/*to find out which hotel the employee who logged in works at*/
						   		$employeeWorks=mysqli_query($dblink,"SELECT HotelID FROM EMPLOYEE WHERE EmployeeID='$employeeID'");
						   		$empWorks=mysqli_fetch_array($employeeWorks);
						   		$hotelEmpWorks=$empWorks['HotelID'];
						   
						   		/*to display reservations that belong to a specific user or a specific hotel(according to the employee that works in that specific hotel)*/
						   		if (strcmp("Customer",$userType)==0){
									$reservationQ = "SELECT * FROM RESERVATION WHERE UserID='$userID'";				
								}else{
									/*to find out which hotel the employee who logged in works at*/
						   			$employeeWorks=mysqli_query($dblink,"SELECT HotelID FROM EMPLOYEE WHERE EmployeeID='$employeeID'");
						   			$empWorks=mysqli_fetch_array($employeeWorks);
						   			$hotelEmpWorks=$empWorks['HotelID'];
									$reservationQ = "SELECT * FROM RESERVATION WHERE HotelID='$hotelEmpWorks'"; 
								}
						   								   
						   		$resultReservation = mysqli_query($dblink, $reservationQ);
						   		$numRows=mysqli_num_rows($resultReservation); 
						   		if ($numRows>0){ //if the query result was not empty
									echo "<thead>
									<tr>
										<th>ReservationID</th>
										<th>StartDate</th>
										<th>EndDate</th>
										<th>Hotel</th>
										<th>Type Room</th>
										<th>Total Prize</th>
										<th></th>
									</tr>
									</thead>";
						   			echo"<tbody>";
						   			while($row = mysqli_fetch_array($resultReservation))//while there are reservations
									{
										/*prints information from the Reservations table*/
										echo "<tr>";
										$reservationid=$row['ReservationID'];
										echo "<td>" ."#". $reservationid . "</td>";
										$startD = $row['StartDate'];
										$endD = $row['EndDate'];
										echo "<td>" . $startD . "</td>";
										echo "<td>" . $endD . "</td>";
										
										/*prints the name of the hotel*/
										$hotelID=$row['HotelID'];
										$hotelQ = "SELECT HotelName,WeekendDifferential FROM HOTEL WHERE HotelID='$hotelID'" ;
						   				$resultHotel = mysqli_query($dblink, $hotelQ);
										$hotelN= mysqli_fetch_array($resultHotel);
										$hotelName=$hotelN['HotelName'];
										$weekendDiff=$hotelN['WeekendDifferential'];
										
										echo "<td>" . $hotelName. "</td>";
										
										/*prints the type of room and prize of the room booked*/
										$roomNum=$row['RoomID'];
										$roomQ = "SELECT Prize,Type FROM ROOMS WHERE RoomID='$roomNum'" ;
						   				$resultRoom = mysqli_query($dblink, $roomQ);
										$roomDetails=mysqli_fetch_array($resultRoom);
										$roomtype=$roomDetails['Type'];
										echo "<td>". $roomtype . "</td>";
										
										/*calculate the number of days booked*/
										$dateDiff = strtotime($endD) - strtotime($startD);
										$numDays= round($dateDiff / (60 * 60 * 24));
										
										/*To apply the weekend differential*/
										$whichDay = date('w', strtotime($startD));//Sun=0...Sat=6
										$numWeekends= (int) (($numDays+$whichDay)/6); // quotient without decimal
										if (($numDays+$whichDay) % 6 == 0){
											$daysWDiff= ($numWeekends-1)*2; 
										}else{
											$daysWDiff=$numWeekends*2;// #Sat,Sun to apply differential 
										}
										
										$applyWeekendDiff=1-$weekendDiff;
										$priceRegDays=($numDays-$daysWDiff) * $roomDetails['Prize'];
										$priceDiffDays= $daysWDiff * ($applyWeekendDiff * $roomDetails['Prize']);
										$totalPrice= $priceRegDays + $priceDiffDays;
										
										echo "<td>"."$" . $totalPrice. "</td>";
										
										if (strcmp("Customer",$userType)==0){
											echo '<form class="text-left clearfix" action="" method="post">
												   <td><a <div class="text-center" >
              									  			<button type="submit" class="btn btn-default" style="background-color:lightsteelblue;" name="cancel">Cancel Reservation
															</button>
														  </div>
													   </a>
												   </td>';
											/*if the user wants to cancel a reservation*/
											if (isset($_POST['cancel'])){
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
												redirect("index.php?email=$email&pwd=$pwd&userType=$userType&name=$name&last=$last&userID=$userID&employeeID=$employeeID&reservationid=$reservationid&startdate=$startD&enddate=$endD&hotelname=$hotelName&roomtype=$roomtype");
											}
							
										}else{ //employee
											echo '<form class="text-left clearfix" action="" method="post">
												   <td><a <div class="text-center" >
              									  			<button type="submit" class="btn btn-default"  name="modify">Modify Reservation
															</button>
														  </div>
													   </a>
												   </td>';
											/*employee can modify a reservation*/
											if (isset($_POST['modify'])){
												redirect("../modifyreservation/index.php?reservationid=$reservationid&startdate=$startD&enddate=$endD&hotelname=$hotelName&roomtype=$roomtype&roomNum=$roomNum&hotelID=$hotelID&email=$email&pwd= $pwd&userType=$userType&name=$name&last=$last&userID=$userID&employeeID=$employeeID");
											}

										}								
										
										echo "</tr>";
									}
									
						   			echo "</tbody>";
								}else{
									echo "<span class='label label-primary'>NO RESERVATIONS TO DISPLAY</span>";
								}						   		
	
							?>
						</table>
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
