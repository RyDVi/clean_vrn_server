<?php
include("connect_db.php");
$postData = file_get_contents('php://input');
$data = json_decode($postData, true);
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($data['id_game'])) {
        $id_game = $data['id_game'];
        $stmt = $conn->prepare("SELECT id, id_status, name, description, route, datetime FROM games WHERE id=?");
        $stmt->bind_param("i", $id_game);
        $stmt->execute();
        $stmt->bind_result($id, $id_status, $name, $description, $route, $datetime);
        if ($stmt->fetch()) {
            $_SESSION['id_game'] = $data['id_game'];
            header("Content-Type: application/json");
            http_response_code(200);
            echo json_encode([
                "id" => $id, "id_status" => $id_status, "name" => $name, "description" => $description,
                "route" => $route, "datetime" => $datetime
            ]);
        } else {
            http_response_code(404);
        }
    }
} else {
    http_response_code(405);
    echo 'Method not implemented';
}
$conn->close();
