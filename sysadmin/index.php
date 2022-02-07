<!-- SYS ADMIN -->
<?php
include("../functions.php");
$employeeID=$_REQUEST['employeeID'];// this is the way to use the var passed in the target page
?>
<!doctype html>
<!--sysadmin *******MJ Healey*******-->
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
					
					
					
					
				   <div id="navbar" class="navbar-collapse collapse text-center">
                    <ul class="nav navbar-nav">
                        <!-- employee -->
                        <li class="dropdown dropdown-slide">
                            <a href="#!" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="350" role="button" aria-haspopup="true" aria-expanded="false">Employee <span class="tf-ion-ios-arrow-down"></span></a>
                            
                            <ul class="dropdown-menu" style="background-color:floralwhite;">
                               <!-- <li class="dropdown-header">Employee</li>
                                <li role="separator" class="divider"></li>-->
                                <li><a href="employee/index.php">Create Account</a></li>
                            </ul>
                        </li>
                        
                        <!-- reservations -->
                        <li class="dropdown dropdown-slide">
                            <a href="#!" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="350" role="button" aria-haspopup="true" aria-expanded="false">Reservations <span class="tf-ion-ios-arrow-down"></span></a>
                            
                            <ul class="dropdown-menu" style="background-color:floralwhite;">
                               <!-- <li class="dropdown-header">Reservations</li> 
                                <li role="separator" class="divider"></li> -->
                                <li><a href="update/index.php">Update</a></li>
                            </ul>
                        </li>
					  
                    </ul>
                </div>
				</div>
			</div>
		</div>
	</div>
</section>


<section class="user-dashboard page-wrapper" action="">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="dashboard-wrapper user-dashboard" style="background-color:lightsteelblue;">
					<div class="media">
						<div class="media-body"> 
							<h2 class="media-heading"><span style="font-weight:bold" style="color:black">Employees</span></h2>
							
							<table class="table">
							<?php
								$dblink=db_iconnect("hoteldB");
						   		if (mysqli_connect_errno())
								{
									echo "Failed to connect to hoteldB: " . mysqli_connect_error();

								}
						   		$employeeQ = "SELECT * FROM EMPLOYEE";		
						   		$resultEmployee = mysqli_query($dblink, $employeeQ);
						   		$numRows=mysqli_num_rows($resultEmployee); 
						   		if ($numRows>0){ //if the query result was not empty
									echo "<thead>
									<tr>
										<th>Employee ID</th>
										<th>Employee Name</th>
										<th>Employee Last Name</th>
										<th>Employee Email</th>
										<th>Works At</th>
										<th></th>
									</tr>
									</thead>";
						   			echo"<tbody>";
						   			while($row = mysqli_fetch_array($resultEmployee))//while there are employees
									{
										
										/*prints information from the Employee table*/
										echo "<tr>";
										$employeeid=$row['EmployeeID'];
										echo "<td>" . $employeeid . "</td>";
										$ename = $row['EName'];
										$elastname = $row['ELastName'];
										$eEmail = $row['Eemail'];
										echo "<td>" . $ename . "</td>";
										echo "<td>" . $elastname . "</td>";
										echo "<td>" . $eEmail . "</td>";
										
										/*prints the name of the hotel the employee works at*/
										$hotelID=$row['HotelID'];
										$hotelQ = "SELECT HotelName FROM HOTEL WHERE HotelID='$hotelID'" ;
						   				$resultHotel = mysqli_query($dblink, $hotelQ);
										$hotelN= mysqli_fetch_array($resultHotel);
										$hotelName=$hotelN['HotelName'];
										
										echo "<td>" . $hotelName. "</td>";
										echo '<form class="text-left clearfix" action="" method="post">
												   <td><a <div class="text-center" >
              									  			<button type="submit" href="index.php?employeeID=<?php echo $employeeid?>" class="btn btn-default"  name="removeE">Remove
															</button>
														  </div>
													   </a>
												   </td>';
										echo '<form class="text-left clearfix" action="" method="post">
												   <td><a <div class="text-center" >
              									  			<button type="submit" class="btn btn-default"  name="updateE">Update
															</button>
														  </div>
													   </a>
												   </td>';
										
										
										echo "</tr>";
									}
									
						   			echo "</tbody>";
								}else{
									echo "<span class='label label-primary'>NO EEMPLOYEES TO DISPLAY</span>";
								}						   		
	
							?>
						</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
	
<section class="user-dashboard page-wrapper" action="">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="dashboard-wrapper user-dashboard" style="background-color:lightsteelblue;">
					<div class="media">
						<div class="media-body"> 
							
							<h2 class="media-heading"><span style="font-weight:bold" style="color:black">Customers</span></h2>
							<table class="table">
							<?php
								$dblink=db_iconnect("hoteldB");
						   		if (mysqli_connect_errno())
								{
									echo "Failed to connect to hoteldB: " . mysqli_connect_error();

								}
						   		$customerQ = "SELECT * FROM USER";		
						   		$resultCustomer = mysqli_query($dblink, $customerQ);
						   		$numRows=mysqli_num_rows($resultCustomer); 
						   		if ($numRows>0){ //if the query result was not empty
									echo "<thead>
									<tr>
										<th>Customer ID</th>
										<th>Customer Name</th>
										<th>Customer Last Name</th>
										<th>Customer Email</th>
										<th></th>
									</tr>
									</thead>";
						   			echo"<tbody>";
						   			while($row = mysqli_fetch_array($resultCustomer))//while there are employees
									{
										
										/*prints information from the Employee table*/
										echo "<tr>";
										$customerid=$row['UserID'];
										echo "<td>" ."#". $customerid . "</td>";
										$name = $row['UName'];
										$lastname = $row['ULastName'];
										$Email = $row['Email'];
										echo "<td>" . $name . "</td>";
										echo "<td>" . $lastname . "</td>";
										echo "<td>" . $Email . "</td>";
										
										echo '<form class="text-left clearfix" action="" method="post">
												   <td><a <div class="text-center" >
              									  			<button type="submit" href="index.php?employeeID=<?php echo $employeeid?>" class="btn btn-default"  name="removeC">Remove
															</button>
														  </div>
													   </a>
												   </td>';
										echo '<form class="text-left clearfix" action="" method="post">
												   <td><a <div class="text-center" >
              									  			<button type="submit" class="btn btn-default"  name="updateC">Update
															</button>
														  </div>
													   </a>
												   </td>';
										
										
										
										echo "</tr>";
									}
									
						   			echo "</tbody>";
								}else{
									echo "<span class='label label-primary'>NO CUSTOMERS TO DISPLAY</span>";
								}						   		
	
							?>
						</table>
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

  </body>
</html>
<!DOCTYPE>
<?php
	$employeeID=$_REQUEST['employeeID'];// this is the way to use the var passed in the target page
	/*remove employee*/
	if (isset($_POST['removeE'])){
		$removeEmployee = "DELETE FROM EMPLOYEE WHERE EmployeeID=$employeeid";
		if ($dblink->query($removeEmployee) === TRUE) {
  			echo "Record deleted successfully";
		} else {
  			echo "<script>alert('Error deleting record: $sql<br>' )" . $conn->error;
		}
			redirect("index.php");												
		}else{
			echo "<script>alert('Employee could not be removed $sql<br>'.$dblink->error)</script>";
		}
?>
