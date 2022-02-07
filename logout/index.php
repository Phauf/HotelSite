<?php
//echo "Log Out";
include("../functions.php");
session_start();//creates a session or resumes the current one based on a session identifier passed via a GET or POST request, or passed via a cookie.
  
session_destroy(); // destroys all of the data associated with the current session 
redirect("../index.php");//use for redirection 

?>