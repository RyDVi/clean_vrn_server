<?php
include("connect_db.php");
include("errors.php");

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
					echo json_encode([
						"id" => $id, "id_status" => $id_status, "name" => $name, "description" => $description,
						"route" => $route, "datetime" => $datetime
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
			while ($stmt->fetch()) {
				array_push($data, [
					"id" => $id, "id_status" => $id_status, "name" => $name, "description" => $description,
					"route" => $route, "datetime" => $datetime
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
