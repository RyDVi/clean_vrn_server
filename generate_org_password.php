<?php
include("connect_db.php");
// Check connection
if ($conn->connect_error) {
	echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
} else {
	header("Content-type: application/json");
	switch ($_SERVER['REQUEST_METHOD']) {
		case "GET":
			if (isset($_GET["id"])) {
				$password = generatePassword(8);
				$cryptoPassword = md5($password);
				$stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
				$stmt->bind_param('si', $cryptoPassword, $_GET['id']);
				if ($stmt->execute()) {
					http_response_code(200);
					echo json_encode(["password" => $password]);
				} else {
					http_response_code(500);
					echo json_encode(["error" => $stmt->error]);
				}
			} else {
				http_response_code(500);
				echo json_encode(["error" => "id not found"]);
			}
			break;
		default:
			http_response_code(405);
			echo 'Method not implemented';
	}
}
$conn->close();

function generatePassword($countChars)
{
	$chars = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
	// Определяем количество символов в $chars

	$size = StrLen($chars) - 1;

	// Определяем пустую переменную, в которую и будем записывать символы.

	$password = "";

	// Создаём пароль.

	for ($i = 0; $i < $countChars; $i++) {
		$password .= $chars[rand(0, $size)];
	}
	return $password;
}
