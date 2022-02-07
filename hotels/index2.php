<!-- Hotels
             ================================================== -->

<?php
	?>
	<!DOCTYPE html>

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
								<li><a href="../cart/index.php" class="btn btn-small">View Cart</a></li>
								<li><a href="../checkout/index.php" class="btn btn-small btn-solid-border">Checkout</a></li>
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


<!-- Main Menu Section -->
<section class="menu">
	<!--<nav:represents a section of a page whose purpose is to provide navigation links, either within the current document or to other documents -->
	<nav class="navbar navigation"> 
		<div class="container">
			<div class="navbar-header">
				<h2 class="menu-title">Main Menu</h2>
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>

			</div><!-- / .navbar-header -->

			<!-- Navbar Links -->
			<div id="navbar" class="navbar-collapse collapse text-center">
				<ul class="nav navbar-nav">

					<!-- Home -->
					<li class="dropdown ">
						<a href="../index.php">Home</a>
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
										<li><a href="../userProfile/index.php">User Profile</a></li>
										<li><a href="../reservations/index.php">Reservations</a></li>									
									</ul>
								</div>

								<!-- Utility -->
								<div class="col-sm-3 col-xs-12">
									<ul>
										<li class="dropdown-header">Utility</li>
										<li role="separator" class="divider"></li>
										<li><a href="../signin/index.php">Login Page</a></li>
										<li><a href="../signup/index.php">Signup Page</a></li>							
									</ul>
								</div>
								
								<!-- Booking -->
								<div class="col-sm-3 col-xs-12">
									<ul>
										<li class="dropdown-header">Booking</li>
										<li role="separator" class="divider"></li>
										<li><a href="../hotels/index.php">Hotels</a></li>
										<li><a href="../checkout/index.php">Checkout</a></li>	
										<li><a href="../cart/index.php">cart</a></li>
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



<section class="products selection">
	<div class="container">
					<div class="col-md-4">
				<div class="product-item">
					<div class="product-thumb">
						<span class="bage">Sale</span>
						<img class="img-responsive" src="../assets/images/hotels/The Magnolia All Suites.jpg" alt="product-img">
						<div class="preview-meta">
							<ul>
								<li>
			                        <a href="/hotels/The-Magnolia-All-Suites/"><p><strong>Info</strong></p></a>
								</li>
							</ul>
                      	</div>
					</div>
					<div class="product-content">
						<h4><a href="/hotels/The-Magnolia-All-Suites/">The Magnolia All Suites</a></h4>
						<p class="amenities">
								<ul >
									<li><p>Pool 			&#127946;</p></li>
									<li><p>Gym 				&#127947;</p></li>
									<li><p>Spa 				&#129494;</p></li>
									<li><p>Business Office &#128187;</p></li>
								</ul></p>
						<p class="price">$100 - Standard, $150 - Queen, $250 - King</p>
						<p class="rooms avaliable">Number of Rooms: 20</p>
					</div>
				</div>
			</div>
	
	<div class="col-md-4">
				<div class="product-item">
					<div class="product-thumb">
						<img class="img-responsive" src="../assets/images/hotels/The Lofts at Town Centre.jpg" alt="product-img" >
						<div class="preview-meta">
							<ul class="amenit">
								<li>
			                        <a href="/hotels/The-Lofts-at-Town-Centre/"><p><strong>Info</strong></p></a>
								</li>
							</ul>
                      	</div>
					</div>
					<div class="product-content">
						<h4><a href="/hotels/The-Lofts-at-Town-Centre/">The Lofts at Town Centre</a></h4>
						<p class="amenities">
						<div><spacing></spacing></div>
								<ul>
									<li><p>Pool 			&#127946;</p></li>
									<li><p>Gym 				&#127947;</p></li>
									<li><p>Business Office &#128187;</p></li>
								</ul></p>
						<p class="price">$105 - Standard, $120 - Queen, $190 - King</p>
						<p class="rooms avaliable">Number of Rooms: 60</p>
					</div>
				</div>
			</div>


	<div class="col-md-4">
				<div class="product-item">
					<div class="product-thumb">
						<img class="img-responsive" src="../assets/images/hotels/Park North Hotel.jpg" alt="product-img" >
						<div class="preview-meta">
							<ul>
								<li>
			                        <a href="/hotels/Park-North-Hotel/"><p><strong>Info</strong></p></a>
								</li>
							</ul>
                      	</div>
					</div>
					<div class="product-content">
						<h4><a href="/hotels/Park-North-Hotel/">Park North Hotel</a></h4>
						<p class="amenities">
						<div><spacing></spacing></div>
								<ul>
									<li><p>Pool 			&#127946;</p></li>
									<li><p>Gym 				&#127947;</p></li>
								</ul></p>
						<p class="price">$50 - Standard, $75 - Queen, $90 - King</p>
						<p class="rooms avaliable">Number of Rooms: 100</p>
					</div>
				</div>
			</div>

<div class="col-md-4">
				<div class="product-item">
					<div class="product-thumb">
						<img class="img-responsive" src="../assets/images/hotels/The Courtyard Suites.jpg" alt="product-img" >
						<div class="preview-meta">
							<ul>
								<li>
			                        <a href="/hotels/The-Courtyard-Suites/"><p><strong>Info</strong></p></a>
								</li>
							</ul>
                      	</div>
					</div>
					<div class="product-content">
						<h4><a href="/hotels/The-Courtyard-Suites/">The Courtyard Suites</a></h4>
						<p class="amenities">
						<div><spacing></spacing></div>
								<ul>
									<li><p>Pool 			&#127946;</p></li>
									<li><p>Gym 				&#127947;</p></li>
									<li><p>Spa 				&#129494;</p></li>
									<li><p>Business Office &#128187;</p></li>
								</ul></p>
						<p class="price">$100 - Standard, $150 - Queen, $250 - King</p>
						<p class="rooms avaliable">Number of Rooms: 20</p>
					</div>
				</div>
			</div>

<div class="col-md-4">
				<div class="product-item">
					<div class="product-thumb">
						<img class="img-responsive" src="../assets/images/hotels/The Regency Rooms.jpg" alt="product-img" >
						<div class="preview-meta">
							<ul>
								<li>
			                        <a href="/hotels/The-Regency-Rooms/"><p><strong>Info</strong></p></a>
								</li>
							</ul>
                      	</div>
					</div>
					<div class="product-content">
						<h4><a href="/hotels/The Regency Rooms/">The Regency Rooms</a></h4>
						<p class="amenities">
						<div><spacing></spacing></div>
								<ul>
									<li><p>Pool 			&#127946;</p></li>
									<li><p>Gym 				&#127947;</p></li>
									<li><p>Spa 				&#129494;</p></li>
									<li><p>Business Office &#128187;</p></li>
								</ul></p>
						<p class="price">$100 - Standard, $150 - Queen, $250 - King</p>
						<p class="rooms avaliable">Number of Rooms: 20</p>
					</div>
				</div>
			</div>


<div class="col-md-4">
				<div class="product-item">
					<div class="product-thumb">
						<img class="img-responsive" src="../assets/images/hotels/Town Inn Budget Rooms.jpg" alt="product-img" >
						<div class="preview-meta">
							<ul>
								<li>
			                        <a href="/hotels/Town-Inn-Budget-Rooms/"><p><strong>Info</strong></p></a>
								</li>
							</ul>
                      	</div>
					</div>
					<div class="product-content">
						<h4><a href="/hotels/Town-Inn-Budget-Rooms/">Town Inn Budget Rooms</a></h4>
						<p class="amenities">
						<div><spacing></spacing></div>
								<ul>
									<li><p>Pool 			&#127946;</p></li>
								</ul></p>
						<p class="price">$25 - Standard, $50 - Queen, $60 - King</p>
						<p class="rooms avaliable">Number of Rooms: 150</p>
					</div>
				</div>
			</div>


<div class="col-md-4">
				<div class="product-item">
					<div class="product-thumb">
						<img class="img-responsive" src="../assets/images/hotels/The Comfy Motel Place.jpg" alt="product-img" >
						<div class="preview-meta">
							<ul>
								<li>
			                        <a href="/hotels/The-Comfy-Motel-Place/"><p><strong>Info</strong></p></a>
								</li>
							</ul>
                      	</div>
					</div>
					<div class="product-content">
						<h4><a href="/hotels/The-Comfy-Motel-Place/">The Comfy Motel Place</a></h4>
						<p class="price">$30 - Standard, $50 - Queen, N/A - King</p>
						<p class="rooms avaliable">Number of Rooms: 50</p>
					</div>
				</div>
			</div>

<div class="col-md-4">
				<div class="product-item">
					<div class="product-thumb">
						<img class="img-responsive" src="../assets/images/hotels/Sun Palace Inn.jpg" alt="product-img" >
						<div class="preview-meta">
							<ul>
								<li>
			                        <a href="/hotels/Sun-Palace-Inn/"><p><strong>Info</strong></p></a>
								</li>
							</ul>
                      	</div>
					</div>
					<div class="product-content">
						<h4><a href="/hotels/Sun-Palace-Inn/">Sun Palace Inn</a></h4>
						<p class="amenities">
						<div><spacing></spacing></div>
								<ul>
									<li><p>Pool 			&#127946;</p></li>
									<li><p>Gym 				&#127947;</p></li>
								</ul></p>
						<p class="price">$40 - Standard, $60 - Queen, $80 - King</p>
						<p class="rooms avaliable">Number of Rooms: 50</p>
					</div>
				</div>
			</div>


<div class="col-md-4">
				<div class="product-item">
					<div class="product-thumb">
						<img class="img-responsive" src="../assets/images/hotels/HomeAway Inn.jpg" alt="product-img" >
						<div class="preview-meta">
							<ul>
								<li>
			                        <a href="/hotels/HomeAway-Inn/"><p><strong>Info</strong></p></a>
								</li>
							</ul>
                      	</div>
					</div>
					<div class="product-content">
						<h4><a href="/hotels/HomeAway-Inn/">HomeAway Inn</a></h4>
						<p class="amenities">
						<div><spacing></spacing></div>
								<ul>
									<li><p>Pool 			&#127946;</p></li>
									<li><p>Business Office &#128187;</p></li>
								</ul></p>
						<p class="price">$50 - Standard, N/A - Queen, N/A - King</p>
						<p class="rooms avaliable">Number of Rooms: 30</p>
					</div>
				</div>
			</div>


<div class="col-md-4">
				<div class="product-item">
					<div class="product-thumb">
						<img class="img-responsive" src="../assets/images/hotels/Rio Inn.jpg" alt="product-img" >
						<div class="preview-meta">
							<ul>
								<li>
			                        <a href="/hotels/Rio-Inn/"><p><strong>Info</strong></p></a>
								</li>
							</ul>
                      	</div>
					</div>
					<div class="product-content">
						<h4><a href="/hotels/Rio-Inn/">Rio Inn</a></h4>
						<p class="amenities">
						<div><spacing></spacing></div>
								<ul>
									<li><p>Pool 			&#127946;</p></li>
								</ul></p>
						<p class="price">$25 - Standard, $55 - Queen, $89 - King</p>
						<p class="rooms avaliable">Number of Rooms: 50</p>
					</div>
				</div>
			</div>
</div>
</section>

  <!-- Basic Page Needs
  ================================================== -->

					<link rel=stylesheet href="../assets/css/style.css">
					<style>
						body {background-color: powderblue;}
						h1   {color: blue;}
						p    {color: #242424;}
					</style>
  <meta charset="utf-8">
  <title>Aviato | E-commerce template</title>

  <!-- Mobile Specific Metas
  ================================================== -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="Construction Html5 Template">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
  <meta name="author" content="Themefisher">
  <meta name="generator" content="Themefisher Constra HTML Template v1.0">
  
  <!-- Favicon -->
  <link rel="shortcut icon" type="image/x-icon" href="../assets/images/favicon.png">
  
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

<script type="text/javascript" charset="UTF-8" src="https://maps.googleapis.com/maps-api-v3/api/js/46/11/common.js"></script><script type="text/javascript" charset="UTF-8" src="https://maps.googleapis.com/maps-api-v3/api/js/46/11/util.js"></script></head>
				</div></ul>
</div>
	
