<?php
include("connect_db.php");
include("errors.php");
$postData = file_get_contents('php://input');
$data = json_decode($postData, true);
header("Content-Type: application/json");
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($data['id_game'])) {
        $id_game = $data['id_game'];
        $stmt = $conn->prepare("SELECT id, id_status, name, description, route, datetime FROM games WHERE id=?");
        $stmt->bind_param("i", $id_game);
        $stmt->execute();
        $stmt->bind_result($id, $id_status, $name, $description, $route, $datetime);
        if ($stmt->fetch()) {
            $_SESSION['id_game'] = $data['id_game'];
            http_response_code(200);
        } else {
            echoError(4041);
        }
    }
} else {
    echoError(4051);
}
$conn->close();
