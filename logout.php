<?php
include("connect_db.php");
session_unset();
session_destroy();
header("Content-type: application/json");
http_response_code(200);
echo json_encode(['session_id' => null, 'id_user_type' => null, 'id_user' => null]);
