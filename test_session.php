<?php
// var_dump(getallheaders());
// session_id();
include("connect_db.php");
$user1 = "user1_not_found";
$user2 = "user2_not_found";
if (isset($_SESSION['user'])) {
    $user1 = $_SESSION['user'];
}

if (isset($_SESSION['user2'])) {
    $user2 = $_SESSION['user2'];
}

header("Content-type: application/json");
echo json_encode(['user1' => $user1, 'user2' => $user2]);
