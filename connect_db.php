<?php
include("errors.php");
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cleanvrn";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    header("Content-Type: application/json");
    echoError(5003);
    die();
}
session_start();
