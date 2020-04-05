<?php
include("connect_db.php");

// Check connection
if ($conn->connect_error) {
	echoError(5003);
}
header("Content-type: application/json");
switch ($_SERVER['REQUEST_METHOD']) {
	case "GET":
		if (!empty($_GET['id'])) {
			$id_organizator = $_GET['id'];
			$stmt = $conn->prepare("SELECT id, lastname, firstname, middlename, email, phone FROM users WHERE id=? and id_type=2");
			$stmt->bind_param("i", $id_organizator);
			$stmt->execute();
			$stmt->bind_result($id,  $lastname, $firstname, $middlename, $email, $phone);
			if ($stmt->fetch()) {
				http_response_code(200);
				$data = json_encode([
					"id" => $id, "lastname" => $lastname, "firstname" => $firstname,
					"middlename" => $middlename, "email" => $email, "phone" => $phone
				]);
				echo $data;
			} else {
				echoError(4041);
			}
			$stmt->close();
		} else {
			if (isset($_SESSION['id_game'])) {
				$stmt = $conn->prepare("SELECT u.id, u.lastname, u.firstname, u.middlename, u.email, u.phone FROM users u INNER JOIN game_users gu on u.id=gu.id_user WHERE id_type=2 AND gu.id_game=?");
				$stmt->bind_param('i', $_SESSION['id_game']);
				$stmt->execute();
				$stmt->bind_result($id,  $lastname, $firstname, $middlename, $email, $phone);
				$data = [];
				while ($stmt->fetch()) {
					array_push($data, [
						"id" => $id, "lastname" => $lastname, "firstname" => $firstname,
						"middlename" => $middlename, "email" => $email, "phone" => $phone
					]);
				}
				echo json_encode($data);
			} else {
				echoError(4002);
			}
		}
		break;

	case "POST":
		if (isset($_SESSION['id_user_type'])) {
			if ($_SESSION['id_user_type'])
				if ($_SESSION['id_user_type'] === 1) {
					if ($_SERVER["CONTENT_TYPE"] !=  'application/json') {
						echoError(5011);
					} else {
						$postData = file_get_contents('php://input');
						$data = json_decode($postData, true);
						if (isset($data) && $_SESSION["id_game"]) {
							$stmt = $conn->prepare("INSERT INTO users(id_type, lastname, firstname, middlename, email, phone) 
						VALUES(2,?,?,?,?,?)");
							$stmt->bind_param("sssss", $data["lastname"], $data["firstname"], $data["middlename"], $data["email"], $data["phone"]);
							if (!$stmt->execute()) {
								echoError(5002);
							} else {
								$stmtGameUser = $conn->prepare("INSERT INTO game_users(id_game, id_user) VALUES (?,?)");
								$idCreatedUser = $stmt->insert_id;
								$stmtGameUser->bind_param("ii", $_SESSION["id_game"], $idCreatedUser);
								if (!$stmtGameUser->execute()) {
									echoError(5002);
								} else {
									http_response_code(201);
									echo json_encode([
										"id" => $stmt->insert_id,  "lastname" => $data["lastname"], "firstname" => $data["firstname"],
										"middlename" => $data["middlename"], "email" => $data["email"], "phone" => $data["phone"]
									]);
								}
							}
						} else {
							echoError(4001);
						}
					}
				} else {
					echoError(4031);
				}
		} else {
			echoError(4031);
		}
		break;
	case "PUT":
		if (isset($_SESSION['id_user_type'])) {
			if ($_SESSION['id_user_type'])
				if ($_SESSION['id_user_type'] === 1) {
					if ($_SERVER["CONTENT_TYPE"] !=  'application/json') {
						echoError(5011);
					} else {
						$postData = file_get_contents('php://input');
						$data = json_decode($postData, true);
						if (isset($data) && $_GET["id"]) {
							$stmt = $conn->prepare("UPDATE users SET lastname=?, firstname=?, middlename=?, email=?, phone=? WHERE id=?");
							$stmt->bind_param("sssssi", $data["lastname"], $data["firstname"], $data["middlename"], $data["email"], $data["phone"], $_GET["id"]);
							if (!$stmt->execute()) {
								echoError(5002);
							} else {
								http_response_code(201);
								echo json_encode(['session_id' => 'PHPSESSID=' . session_id(), 'id_user_type' => null, 'id_user' => null]);
							}
						} else {
							echoError(4001);
						}
					}
				} else {
					echoError(4031);
				}
		} else {
			echoError(4031);
		}
		break;
	case "DELETE":
		if (isset($_SESSION['id_user_type'])) {
			if ($_SESSION['id_user_type'])
				if ($_SESSION['id_user_type'] === 1) {
					if (isset($_GET['id'])) {
						$stmt = $conn->prepare("DELETE FROM users WHERE id=?");
						$stmt->bind_param("i", $_GET['id']);
						$stmt->execute();
						if ($stmt->affected_rows > 0) {
							http_response_code(200);
						} else {
							echoError(4041);
						}
						$stmt->close();
					} else {
						echoError(4041);
					}
				} else {
					echoError(4031);
				}
		} else {
			echoError(4031);
		}
		break;
	default:
		echoError(4051);
}
$conn->close();
function CheckEmailPhone(mysqli $conn)
{
	$stmt = $conn->prepare("SELECT d FROM organizators");
	if (!$stmt->execute()) {
		echoError(5002);
	}
	$stmt->bind_result($id, $id_status, $name, $description, $route, $datetime);
	$data = [];
	while ($stmt->fetch()) {
		$tempDatetime = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
		array_push($data, [
			"id" => $id, "id_status" => $id_status, "name" => $name, "description" => $description,
			"route" => $route, "datetime" => $tempDatetime->format('Y-m-d\TH:i:s\Z')
		]);
	}
	http_response_code(200);
	echo json_encode($data);
}
