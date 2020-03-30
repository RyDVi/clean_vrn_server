<?php
include("connect_db.php");
$postData = file_get_contents('php://input');
$data = json_decode($postData, true);
header("Content-Type: application/json");
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($data['username']) && isset($data['password'])) {
        $stmt = $conn->prepare("SELECT id, id_type, firstname, lastname, middlename, email, phone FROM users WHERE email=? AND password=?");
        $password = md5($data["password"]);
        $stmt->bind_param("ss", $data["username"], $password);
        if (!$stmt->execute()) {
            // http_response_code(201);
            json_encode(["error" => $stmt->error]);
        } else {
            $stmt->bind_result($id, $idUserType, $firstname, $lastname, $middlename, $email, $phone);
            if ($stmt->fetch()) {
                // http_response_code(201);
                $_SESSION['id_user_type'] = $idUserType;
                echo json_encode([
                    'session_id' => 'PHPSESSID=' . session_id(), 'id_user_type' => $idUserType,
                    'id_user' => $id, 'firstname' => $firstname, 'lastname' => $lastname,
                    'middlename' => $middlename, 'email' => $email, 'phone' => $phone
                ]);
            } else {
                // http_response_code(201);
                echo json_encode(["error" => "Логин или пароль введен неверно"]);
            }
        }
    } else if (isset($data['is_player'])) {
        echo json_encode(['session_id' => 'PHPSESSID=' . session_id(), 'id_user_type' => 3]);
    } else if (!isset($data['username']) && !isset($data['password'])) {
        // http_response_code(201);
        echo json_encode(["error" => "Логин и пароль не введены"]);
    } else if (!isset($data['username'])) {
        // http_response_code(201);
        echo json_encode(["error" => "Логин не введен"]);
    } else {
        // http_response_code(201);
        echo json_encode(["error" => "Пароль не введен"]);
    }
} else {
    http_response_code(405);
    echo 'Method not implemented';
}
$conn->close();
