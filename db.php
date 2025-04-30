<?php
$host = "your_host"; 
$user = "your_username";       
$password = "your_password";       
$database = "your_database";

// Create a connection
$conn = new mysqli($host, $user, $password, $database );

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
   
}
?>
