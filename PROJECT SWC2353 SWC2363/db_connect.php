<?php

$servername = "localhost";
$username = "root";  // Change if using a different user
$password = "";      // Change if you set a password
$database = "zeusgadgetstore";  // Your actual database name

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
