<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cleanvrn_1";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

session_start();

// // Check connection
// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }
// echo "Connected successfully";
