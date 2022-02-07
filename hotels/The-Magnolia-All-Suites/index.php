	<!-- Magnolia All Suites Hotel
             ================================================== -->

<?php
include("../../functions.php");
session_start();// Initialize the session



?>
<!doctype html>

<section class="top-header">
	<!--div is an HTML element that groups other elements of the page together. class is an attribute. All HTML elements can carry a class attribute. If your elements have a class attribute then you will be able to write a CSS rule to select that class. nav and container are names of classes-->
	<div class="container">
		<div class="row">			
			<div class="col-md-4 col-xs-12 col-sm-4">
				<!-- Site Logo -->
				<div class="logo text-center">
					<a href="index.html"> <!--anchor tag: creates a hyperlink to web pages, files, email addresses, locations in the same page, or anything else a URL can address-->
						<!-- replace logo here -->
						<svg width="135px" height="29px" viewBox="0 0 155 29" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"> <!--Scalable Vector Graphics: language for describing 2D graphics in XML-->
							<g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" font-size="40" font-family="AustinBold, Austin" font-weight="bold"> <!--container used to group other SVG elements -->
								<g id="Group8" transform="translate(-108.000000, -297.000000)" fill="#000000">
									<text id="Group8">
										<tspan x="108.94" y="325">Group8</tspan>
									</text>
								</g>
							</g>
						</svg>
					</a>
				</div>
			</div>
			<div class="col-md-4 col-xs-12 col-sm-4">
				<!-- Cart -->
				<ul class="top-menu text-right list-inline">
					<li class="dropdown cart-nav dropdown-slide">
						<a href="#!" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown"><i class="tf-ion-android-cart"></i>Cart</a>
						<div class="dropdown-menu cart-dropdown">
							<!-- Cart Item -->
							<div class="media">
								<a class="pull-left" href="#!">
									<img class="media-object" src="images/shop/cart/cart-1.jpg" alt="image">
								</a>
								<div class="media-body">
									<h4 class="media-heading"><a href="#!">Room1 Booked</a></h4>
									<div class="cart-price">
										<span>1 x</span>
										<span>1250.00</span>
									</div>
									<h5><strong>$1200</strong></h5>
								</div>
								<a href="#!" class="remove"><i class="tf-ion-close"></i></a>
							</div><!-- / Cart Item -->
							<!-- Cart Item -->
							<div class="media">
								<a class="pull-left" href="#!">
									<img class="media-object" src="images/shop/cart/cart-2.jpg" alt="image">
								</a>
								<div class="media-body">
									<h4 class="media-heading"><a href="#!">Room2 booked</a></h4>
									<div class="cart-price">
										<span>1 x</span>
										<span>1250.00</span>
									</div>
									<h5><strong>$1200</strong></h5>
								</div>
								<a href="#!" class="remove"><i class="tf-ion-close"></i></a>
							</div><!-- / Cart Item -->

							<div class="cart-summary">
								<span>Total</span>
								<span class="total-price">$1799.00</span>
							</div>
							<ul class="text-center cart-buttons">
								<!-- <li:used to represent an item in a list -->
								<li><a href="../../../cart/index.php" class="btn btn-small">View Cart</a></li>
								<li><a href="../../../checkout/index.php" class="btn btn-small btn-solid-border">Checkout</a></li>
							</ul>
						</div>

					</li><!-- / Cart -->

					<!-- Search -->
					<li class="dropdown search dropdown-slide">
						<a href="#!" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown"><i class="tf-ion-ios-search-strong"></i> Search</a>
						<ul class="dropdown-menu search-dropdown">
							<li>
								<form action="post"><input type="search" class="form-control" placeholder="Search..."></form>
							</li>
						</ul>
					</li><!-- / Search -->

				</ul><!-- / .nav .navbar-nav .navbar-right -->
			</div>
		</div>
	</div>
</section><!-- End Top Header Bar -->
<head>
	<!-- Mobile Specific Metas <!-- Main Menu Section -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="Construction Html5 Template">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
  <meta name="author" content="Themefisher">
  <meta name="generator" content="Themefisher Constra HTML Template v1.0">
  
		<script src="../../assets/plugins/jquery/dist/jquery.min.js"></script>
  <!-- Favicon -->
  <link rel="shortcut icon" type="image/x-icon" href="../../assets/images/favicon.png">
  
  <!-- Themefisher Icon font -->
  <link rel="stylesheet" href="../../assets/plugins/themefisher-font/style.css">
  <!-- bootstrap.min css -->
  <link rel="stylesheet" href="../../assets/plugins/bootstrap/css/bootstrap.min.css">
  
  <!-- Animate css -->
  <link rel="stylesheet" href="../../assets/plugins/animate/animate.css">
  <!-- Slick Carousel -->
  <link rel="stylesheet" href="../../assets/plugins/slick/slick.css">
  <link rel="stylesheet" href="../../assets/plugins/slick/slick-theme.css">
  

<script src="../../assets/plugins/bootstrap/js/bootstrap.min.js"></script>
<script src="../../assets/js/script.js"></script>
		<script src="../../assets/plugins/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js"></script>
		
		
  <!-- Main Stylesheet -->
   <link rel=stylesheet href="../../assets/css/style.css">
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
 

					<style>
						body {background-color: powderblue;}
						h1   {color: blue;}
						p    {color: #242424;}
					</style>
  <meta charset="utf-8">
  <title>Aviato | E-commerce template</title></head>
<section class="menu">
	<!--<nav:represents a section of a page whose purpose is to provide navigation links, either within the current document or to other documents -->
	<nav class="navbar navigation"> 
		<div class="container">
			
			<!-- Navbar Links -->
			<div id="navbar" class="navbar-collapse collapse text-center">
				<ul class="nav navbar-nav">

					<!-- Home -->
					<li class="dropdown ">
						<a href="../../../index.php">Home</a>
					</li><!-- / Home -->


					
					<!-- Pages -->
					<li class="dropdown full-width dropdown-slide">
						<a href="#!" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="350" role="button" aria-haspopup="true" aria-expanded="false">Pages <span class="tf-ion-ios-arrow-down"></span></a>
						<div class="dropdown-menu">
							<div class="row">
								<!-- Contact -->
								<div class="col-sm-3 col-xs-12">
									<!--<ul:represents an unordered list of items,typically rendered as a bulleted list-->
									<ul>
										<li class="dropdown-header">Dashboard</li>
										<li role="separator" class="divider"></li>
										<li><a href="../../../userProfile/index.php">User Profile</a></li>
										<li><a href="../../../reservations/index.php">Reservations</a></li>									
									</ul>
								</div>

								<!-- Utility -->
								<div class="col-sm-3 col-xs-12">
									<ul>
										<li class="dropdown-header">Utility</li>
										<li role="separator" class="divider"></li>
										<li><a href="../../../signin/index.php">Login Page</a></li>
										<li><a href="../../../signup/index.php">Signup Page</a></li>							
									</ul>
								</div>
								
								<!-- Booking -->
								<div class="col-sm-3 col-xs-12">
									<ul>
										<li class="dropdown-header">Booking</li>
										<li role="separator" class="divider"></li>
										<li><a href="../../../hotels/index.php">Hotels</a></li>
										<li><a href="../../../checkout/index.php">Checkout</a></li>	
										<li><a href="../../../cart/index.php">cart</a></li>
									</ul>
								</div>
								
							</div><!-- / .row -->
						</div><!-- / .dropdown-menu -->
					</li><!-- / Pages -->
					
				</ul><!-- / .nav .navbar-nav -->

			</div>
			<!--/.navbar-collapse -->
		</div><!-- / .container -->
	</nav>
</section>
<section class="page-header">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="content">
					<ol class="breadcrumb">
						<h1 class="text-center" style="font-size: 32px">The Magnolia All Suites Hotel</h1>
					</ol>
				</div>
			</div>
		</div>
	</div>
</section>

<html lang="en">	
<body id="body">
	<section class="products section">
	<div class="container">
		<div class="row">
		  <div class="col-md-3">
				<div class="widget product-category">
					<h4 class="widget-title">Categories</h4>
					<div class="panel-group commonAccordion" id="accordion" role="tablist" aria-multiselectable="true">
						
						<div class="panel panel-default"; style="height: 37px">
							<div class="panel-heading" role="tab" id="headingOne">
							</div>
							<h4 >
								<a style="text-transform: uppercase;display: block;font-size: 14px; padding: 1px 10px;" href="#!">Overview</a>
							</h4>
						</div>
						
					  <div class="panel panel-default">
					    <div class="panel-heading" role="tab" id="headingTwo">
					      <h4 class="panel-title">
					        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
					         	Rooms
					        </a>
					      </h4>
					    </div>
					    <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo" aria-expanded="false">
					    	<div class="panel-body">
					     		<ul>
									<li><a href="#!">Standard</a></li>
									<li><a href="#!">Queen</a></li>
									<li><a href="#!">King</a></li>
								</ul>
					    	</div>
					    </div>
					  </div>
						
					  <div class="panel panel-default" style="height: 37px">
					    <div class="panel-heading" role="tab" id="headingThree">
					    </div>
						  <h4 >
								<a style="text-transform: uppercase;display: block;font-size: 14px; padding: 1px 10px;" href="#!">Policies</a>
						</h4>
					  </div>
					
					</div>
				</div>
			</div>
			
			
			<div class="col-md-9">
						<div style="">
							<img src="../../assets/images/hotels/The Magnolia All Suites.jpg" width="441" style="border: 5px solid#555;">
							<img style="position: absolute; width: 306px; height: 179px; left: 466px; top: -2px; border: 5px solid#555;" src="../../assets/images/hotels/MAS-pool.jpg">
							<img style="position: absolute; width: 306px; height: 179px; left: 466px; top: 184px; border: 5px solid#555;" src="../../assets/images/hotels/MAS-room.jpg">
						</div>
				<div class="container-fluid">
				<div class="row">
					<div class="col-md-8" style="height: 100">
						<div id"information">
							<div class="conatiner" style="">
								<h1 style="font-size:20px; padding-left: 50px">Popular amenities</h1></div>
								<div class="row">
								</div>
									<div class="col-md-5">
										<ul><li>Pet friendly</li>
										<li>Hot Tub</li></ul>
									</div>
									<div class="col-md-5">
										<ul>
										<li>Spa</li>
										<li>Air conditioning</li>
										</ul>
									</div>
						<div class="row" style="padding-top: 80px">
							<h1 style="font-size:20px; padding-left: 50px">Cleaning and Saftey Practices</h1>
							<div class="col-md-5">
										<ul><li>Cleaned with disinfectant</li>
										<li>Contactless check-in</li></ul>
									</div>
									<div class="col-md-5">
										<ul>
										<li>24-hour vacancy between guest room stays</li>
										<li>Hand sanitizer provided</li>
										</ul>
									</div>
						</div>
						</div>
					 </div>
					<div class="col-md-4" style="padding-bottom: 50px;">
						<h1 style="font-size:20px">Location</h1>
						<div class="media">
							<img src="../../assets/images/hotels/MASlocation.JPG">
						</div>
					</div>
				</div>
				</div>
					
			<div class="col-md-4">
				<div class="product-item">
					<div class="product-thumb">
						<span class="bage">Sale</span>
						<img class="img-responsive" style="border: 5px solid#555" src="../../assets/images/hotels/standard-hotel-room-layout (1).jpg" alt="product-img">
						<div class="preview-meta"><p>
								1 Standard Bed
								Fully refundable
							</p>
                      	</div>
				  </div>
					<div class="product-content">
						<h4>Standard Room</h4>
						<p>$100</p>
						<div class="input-group">
								  <span class="input-group-btn">
									  <button type="submit" class="btn btn-default btn-number" data-type="minus" data-field="quant[1]" disabled="disabled">
										  <span class="glyphicon glyphicon-minus"></span>
									  </button>
								  </span>
								  <input type="text" name="quant[0]" class="form-control input-number" value="1" min="1" max="10">
								  <span class="input-group-btn">
									  <button type="button" class="btn btn-default btn-number" data-type="plus" data-field="quant[1]">
										  <span class="glyphicon glyphicon-plus"></span>
									  </button>
								  </span>
						 </div>
						<div class="text-center" style="left: 100px">
							<form action="index.php" method="POST">
						<button type="submit" name="reserves" method="POST"><p>Reserve</p></button>
							</form>
						</div>
					</div>
				</div>
			</div>  
				
			<div class="col-md-4">
				<div class="product-item">
					<div class="product-thumb">
						<img class="img-responsive" style="border: 5px solid#555" src="../../assets/images/hotels/queenRoom.jpg" alt="product-img">
						<div class="preview-meta">
							<p>
								1 Queen Bed
								city view
								Fully refundable
							</p>
                      	</div>
					</div>
					<div class="product-content" method="post">
						<h4><a href="product-single.html">Queen Room</a></h4>
						<p class="price">$150</p>
						<div class="text-center" style="left: 100px">
							<form action="index.php" method="POST">
							<button type="submit" name="reserveq" method="POST"><p>Reserve</p></button>
							</form>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="product-item">
					<div class="product-thumb">
						<img class="img-responsive" style="border: 5px solid#555" src="../../assets/images/hotels/kingBedroom.jpg" alt="product-img">
						<div class="preview-meta">
							<p>
								1 King Bed
								city view
								Fully refundable
							</p>
                      	</div>
					</div>
					<div class="product-content">
						<h4><a href="product-single.html">King Room</a></h4>
						<p class="price">$250</p>
						<div class="input-group">
								  <span class="input-group-btn">
									  <button type="button" class="btn btn-default btn-number" data-type="minus" data-field="quant[1]" disabled="disabled">
										  <span class="glyphicon glyphicon-minus"></span>
									  </button>
								  </span>
								  <input type="text" name="quant[0]" class="form-control input-number" value="1" min="1" max="10">
								  <span class="input-group-btn">
									  <button type="button" class="btn btn-default btn-number" data-type="plus" data-field="quant[1]">
										  <span class="glyphicon glyphicon-plus"></span>
									  </button>
								  </span>
						  </div>
						<div class="text-center" style="left: 100px">
							<form action="index.php" method="POST">
						<button type="submit" name="reservek" method="POST"><p>Reserve</p></button>
							</form>
						</div>
					</div>
				</div>
			</div>
				
				<div class="container-fluid">
				<div class="row">
					<div class="col-md-10" style="height: 100; left: 50px">
						<div id="information">
							<div class="container-fluid">
								<h1 style="font-size:30px; padding-left: 50px">Policies</h1></div>
							<div class="container-fluid">
											<div class="col-md-6">
											<h1 style="font-size: 20px;">Check-in</h1>
											</div>
											<div class="col-md-5">
												<h1 style="font-size: 20px;"> Check-out</h1>
									</div>
									<div class="col-md-6" style="right: 15px">
										<ul><li>Check-in from 4:00PM - Anytime</li>
										<li>Minimum check-in age - 21</li></ul>
									</div>
									<div class="col-md-6" style="right: 15px">
										<ul>
										<li>Check-out before 11:00AM</li>
										</ul>
								</div> 
							</div>
							
						<div class="container-fluid">
							<h1 style="font-size:20px;">Special Check-in instructions</h1>
							<div class="col-md-6" style="right:15px">
								<ul><li>Front desk staff will greet guests on arrival</li></ul>
									</div>
									<div class="col-md-6" style="right 15px">
									</div>
							
						</div>
							<div class="container-fluid">
							<h1 style="font-size:20px">Access methods</h1>
								<div class="col-md-12" style="right:15px"><ul><li>Staffed front desk, smart lock</p></li></ul></div></div>
					
					<div class="container-fluid">
										<h1 style="font-size:20px; padding-right: 15px">Pets</h1>
						<div class="col-md-12" style="right:15px"><ul>
							<li>Pets stay free</li>
							<li>Dogs welcome</li>
							<li>Food and water bowls are available</li>
							<li>Service animals are exempt from fees</li></ul></div>
				</div>
					<div class="container-fluid">
										<h1 style="font-size:20px; padding-right: 15px">Children and extra beds</h1>
						<div class="col-md-12" style="right:15px"><ul>
							<li>Children are welcome</li>
							<li>Kids stay free! Children stay free when using existing bedding</li>
				</div>
				</div>
			  </div>				
		  </div>
		
		</div>
	</div>
</section>
</body>
</html>
<!DOCTYPE>
	
	
<?php
$roomType="";
	
$email=$_REQUEST['email'];// this is the way to use the var passed in the target page
$pwd=$_REQUEST['pwd'];
$userType=$_REQUEST['userType'];
$name=$_REQUEST['name'];
$last=$_REQUEST['last'];
$userID=$_REQUEST['userID']; 
$employeeID=$_REQUEST['employeeID'];
	
if (isset($_POST['reserves']))
{
	echo "Trying to reserve standard";
	$roomType="standard";
	$cost="$100";
	redirect("../../reserve/index.php?roomType=$roomType&cost=$cost&email=$email&pwd=$pwd&userType=$userType&name=$name&last=$last&userID=$userID&employeeID=$employeeID");

}
	if (isset($_POST['reserveq']))
{
	echo "Trying to reserve queen";
	$roomType="queen";
	$cost="$150";
	redirect("../../reserve/index.php?roomType=$roomType&cost=$cost&email=$email&pwd=$pwd&userType=$userType&name=$name&last=$last&userID=$userID&employeeID=$employeeID");
		
			
}
	if (isset($_POST['reservek']))
{
	echo "Trying to reserve king";
	$roomType="king";
	$cost="$250";
	redirect("../../reserve/index.php?roomType=$roomType&cost=$cost&email=$email&pwd=$pwd&userType=$userType&name=$name&last=$last&userID=$userID&employeeID=$employeeID");
			
}
	echo $email;
?>


	