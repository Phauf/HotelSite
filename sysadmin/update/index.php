<?php
include("../../functions.php");

?>

<!DOCTYPE html>
<!--UPDATE RESERVATIONS *******MJ Healey*******-->
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
            </div>
        </div>
    </section>

<!-- Welcome Tag -->
<section class="page-header" style="background-color:floralwhite;">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="content">
					<!--<h1 class="page-name">System Administration</h1> -->
					<ol class="breadcrumb">
						<li class="active"><span style="font-weight:bold" style="color:black"><?php echo "SYS ADMIN ";?></span></li>
					</ol>
					<div class="media-body">
							<h2 class="media-heading"><span style="font-weight:bold" style="color:black"><?php echo "Welcome " ;?></span></h2>
					</div>
</section>
<section class="user-dashboard page-wrapper">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				
				<div class="dashboard-wrapper user-dashboard">
					<div class="table-responsive">
						<table class="table">
							<?php
								$dblink=db_iconnect("hoteldB");
						   		if (mysqli_connect_errno())
								{
									echo "Failed to connect to hoteldB: " . mysqli_connect_error();

								}
						   		
						   		$reservationQ = "SELECT * FROM RESERVATION";						   
						   		$resultReservation = mysqli_query($dblink, $reservationQ);
						   		$numRows=mysqli_num_rows($resultReservation); 
						   		if ($numRows>0){ //if the query result was not empty
									echo "<thead>
									<tr>
										<th>ReservationID</th>
										<th>StartDate</th>
										<th>EndDate</th>
										<th>HotelID</th>
										<th>UserID</th>
										<th>RoomID</th>
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
										$hotelID=$row['HotelID'];										
										echo "<td>" ."#". $hotelID. "</td>";
										$userID=$row['UserID'];
										echo "<td>". "#".$userID . "</td>";										
										$roomNum=$row['RoomID'];
										echo "<td>"."#". $roomNum . "</td>";
										echo '<form class="text-left clearfix" action="" method="post">
												   <td><a <div class="text-center" >
              									  			<button type="submit" class="btn btn-default"  name="cancel">Cancel
															</button>
														  </div>
													   </a>
												   </td>';
										/*cancel a reservation*/
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
												redirect("index.php");
										}
										echo '<form class="text-left clearfix" action="" method="post">
												   <td><a <div class="text-center" >
              									  			<button type="submit" class="btn btn-default"  name="modify">Modify
															</button>
														  </div>
													   </a>
												   </td>';
										/*employee can modify a reservation*/
										if (isset($_POST['modify'])){
											redirect("../?/index.php");
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

