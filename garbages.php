<?php
include("connect_db.php");
// Check connection
if ($conn->connect_error) {
    echoError(5003);
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmtGarbages = $conn->prepare("SELECT id, name FROM garbages");
        $stmtGarbages->execute();
        $stmtGarbages->bind_result($id, $name);
        $garbages = [];
        while ($stmtGarbages->fetch()) {
            array_push($garbages, ["id" => $id, "name" => $name]);
        }
        header("Content-Type: application/json");
        http_response_code(200);
        echo json_encode($garbages);
    } else {
        echoError(4051);
    }
    $conn->close();
}
