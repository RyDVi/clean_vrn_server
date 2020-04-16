<?php
include("connect_db.php");

header("Content-Type: application/json");
switch ($_SERVER['REQUEST_METHOD']) {
	case "GET":
		if (!empty($_GET['id'])) {
			$id_person = $_GET['id'];
			$stmt = $conn->prepare("SELECT * FROM games WHERE id=?");
			$stmt->bind_param("i", $id_person);
			if (!$stmt->execute()) {
				echoError(5002);
			} else {
				$stmt->bind_result($id, $id_status, $name, $description, $route, $datetime);
				if ($stmt->fetch()) {
					http_response_code(200);
					$new_id_status;
					$time = date('Y-m-d H:i:s');
					if ($id_status != 2) {
						if (strtotime($time) < strtotime($datetime))
							$new_id_status = 3;
						else
							$new_id_status = 1;
					} else {
						$new_id_status = 2;
					}
					$tempDatetime = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
					echo json_encode([
						"id" => $id, "id_status" => $new_id_status, "name" => $name, "description" => $description,
						"route" => $route, "datetime" =>  $tempDatetime->format('Y-m-d\TH:i:s\Z')
					]);
				} else {
					echoError(4041);
				}
			}
		} else {
			$stmt = $conn->prepare("SELECT * FROM games");
			if (!$stmt->execute()) {
				echoError(5002);
			}
			$stmt->bind_result($id, $id_status, $name, $description, $route, $datetime);
			$data = [];
			$time = date('Y-m-d H:i:s');
			while ($stmt->fetch()) {
				$new_id_status;
				if ($id_status != 2) {
					if (strtotime($time) < strtotime($datetime))
						$new_id_status = 3;
					else
						$new_id_status = 1;
				} else {
					$new_id_status = 2;
				}
				$tempDatetime = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
				array_push($data, [
					"id" => $id, "id_status" => $new_id_status, "name" => $name, "description" => $description,
					"route" => $route, "datetime" => $tempDatetime->format('Y-m-d\TH:i:s\Z')
				]);
			}
			http_response_code(200);
			echo json_encode($data);
		}
		break;
	case "POST":
		if (isset($_SESSION['id_user_type'])) {
			if ($_SESSION['id_user_type'] === 1) {
				if ($_SERVER["CONTENT_TYPE"] !=  'application/json') {
					echoError(5011);
				} else {
					$postData = file_get_contents('php://input');
					$data = json_decode($postData, true);
					if (isset($data)) {
						if (strtotime($data["datetime"]) > strtotime(date('Y-m-d H:i:s'))) {
							$stmt = $conn->prepare("INSERT INTO games(id_status, name, route, datetime) VALUES(1, ?, ?, ?)");
							$stmt->bind_param("sss", $data["name"],  $data["route"], $data["datetime"]);
							if (!$stmt->execute()) {
								echoError(5002);
							} else {
								http_response_code(201);
								echo json_encode([
									"id" => $stmt->insert_id, "name" => $data['name'],
									"route" => $data['route'], "datetime" => $data['datetime']
								]);
							}
						} else {
							echoError(4008);
						}
					} else {
						echoError(4001);
					}
				}
			} else {
				echoError(4031);
			}
		} else {
			echoError(4013);
		}
		break;
	case "PUT":
		if (date_g($conn)) {
			if (isset($_SESSION['id_user_type'])) {
				if ($_SESSION['id_user_type'] === 1) {
					if ($_SERVER["CONTENT_TYPE"] !=  'application/json') {
						echoError(5011);
					} else {
						$postData = file_get_contents('php://input');
						$data = json_decode($postData, true);
						if (isset($data) && isset($_GET['id_game'])) {
							$stmt = $conn->prepare("UPDATE games SET name=?,  route=?, datetime=?  WHERE id=?");
							$stmt->bind_param("sssi", $data["name"],  $data["route"], $data["datetime"], $_GET['id_game']);
							if (!$stmt->execute()) {
								echoError(5002);
							} else {
								http_response_code(201);
								echo json_encode([
									"id" => $_GET['id_game'], "name" => $data['name'],
									"route" => $data['route'], "datetime" => $data['datetime']
								]);
							}
						} else {
							echoError(4001);
						}
					}
				} else {
					echoError(4031);
				}
			} else {
				echoError(4013);
			}
		} else {
			echoError(4003);
		}
		break;
	case "DELETE":
		if (isset($_SESSION['id_user_type'])) {
			if ($_SESSION['id_user_type'] === 1) {
				if (isset($_GET['id_game'])) {
					$id_game = $_GET['id_game'];
					$stmt = $conn->prepare("DELETE FROM games WHERE id=?");
					$stmt->bind_param("i", $id_game);
					$stmt->execute();
					if ($stmt->affected_rows > 0) {
						http_response_code(200);
					} else {
						echoError(4041);
					}
					$stmt->close();
				} else {
					echoError(4001);
				}
			} else {
				echoError(4031);
			}
		} else {
			echoError(4013);
		}
		break;
	default:
		echoError(5001);
}
$conn->close();
/**
 * Проверка на совпадение времени
 */
function date_g(mysqli $conn)
{
	$time = date('Y-m-d H:i:s');
	$stmt = $conn->prepare("SELECT datetime FROM games WHERE id=?");

	$stmt->bind_param('i', $_GET['id']);
	if (!$stmt->execute()) {
		echoError(5002);
	} else {
		$stmt->bind_result($datetime_g);
		$stmt->fetch();
		if (strtotime($time) < strtotime($datetime_g)) {
			return true;
		} else {
			return false;
		}
	}
}
