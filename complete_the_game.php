<?php
include("connect_db.php");

$postData = file_get_contents('php://input');
$data = json_decode($postData, true);
header("Content-Type: application/json");
if ($_SERVER['REQUEST_METHOD'] == "GET") {
    if (isset($_SESSION['id_user_type'])) {
        if ($_SESSION['id_user_type'] === 1) {
            if ($_SERVER["CONTENT_TYPE"] !=  'application/json') {
                echoError(5011);
            } else {
                $postData = file_get_contents('php://input');
                $data = json_decode($postData, true);
                $stmt = $conn->prepare("UPDATE games SET id_status=2 WHERE id=?");
                $stmt->bind_param("i", $_GET['id']);
                if (!$stmt->execute()) {
                    echoError(5002);
                } else {
                    http_response_code(200);
                }
            }
        } else {
            echoError(4031);
        }
    } else {
        echoError(4013);
    }
} else {
    echoError(4051);
}
$conn->close();
