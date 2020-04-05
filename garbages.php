<?php
include("connect_db.php");

header("Content-Type: application/json");
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!empty($_GET['id'])) {
        $id_garbages = $_GET['id'];
        $stmt = $conn->prepare("SELECT * FROM garbages WHERE id=?");
        $stmt->bind_param("i", $id_garbages);
        if (!$stmt->execute()) {
            echoError(5002);
        } else {
            $stmt->bind_result($id, $name);
            if ($stmt->fetch()) {
                http_response_code(200);
                echo json_encode([
                    "id" => $id, "name" => $name
                ]);
            } else {
                echoError(4041);
            }
        }
    } else {
        $stmt = $conn->prepare("SELECT * FROM garbages");
        if (!$stmt->execute()) {
            echoError(5002);
        }
        $stmt->bind_result($id, $name);
        $data = [];
        while ($stmt->fetch()) {
            array_push($data, [
                "id" => $id, "name" => $name
            ]);
        }
        http_response_code(200);
        echo json_encode($data);
    }
} else {
    echoError(4051);
}
$conn->close();
