<?php
include("connect_db.php");

header("Content-type: application/json");
switch ($_SERVER['REQUEST_METHOD']) {
	case "GET":
		if (!empty($_GET['id'])) {
			$id_team = $_GET['id'];
			$stmt = $conn->prepare("SELECT * FROM teams WHERE id=?");
			$stmt->bind_param("i", $id_team);
			$stmt->execute();
			$stmt->bind_result($id, $number, $name);
			if ($stmt->fetch()) {
				http_response_code(200);
				$data = json_encode([
					"id" => $id, "name" => $name, "number" => $number
				]);
				echo $data;
			} else {
				echoError(4041);
			}
			$stmt->close();
		} else {
			if (isset($_SESSION['id_game']) && isset($_GET['id_team'])) {
				$stmtGarbages = $conn->prepare("SELECT tg.id, tg.id_team, tg.id_garbage, gc.id_game, 
				g.name, tg.count, gc.coefficient FROM teams_garbages tg 
				INNER JOIN garbages g ON tg.id_garbage=g.id 
				INNER JOIN garbage_coefficients gc ON g.id=gc.id_garbage 
				WHERE tg.id_team=? AND gc.id_game=?
				UNION ALL 
				SELECT NULL, NULL, g.id, NULL, g.name, NULL, gc.coefficient
				FROM garbages g INNER JOIN garbage_coefficients gc ON g.id=gc.id_garbage 
				WHERE g.id NOT IN (SELECT id_garbage FROM teams_garbages WHERE id_team=?) AND gc.id_game=?");
				$stmtGarbages->bind_param('iiii', $_GET['id_team'], $_SESSION['id_game'], $_GET['id_team'], $_SESSION['id_game']);
				$stmtGarbages->execute();
				$stmtGarbages->bind_result(
					$id_collected_garbage,
					$id_team,
					$id_garbage,
					$id_game,
					$garbage_name,
					$count,
					$coefficient
				);
				$teamsGarbages = [];
				while ($stmtGarbages->fetch()) {
					//sum_points - количество очков за вид мусора (количество * коэффициент мусора)
					//id_collected_garbage - ид из таблицы teams_garbages
					array_push($teamsGarbages, [
						"id_collected_garbage" => $id_collected_garbage, "id_team" => $id_team,
						"id_garbage" => $id_garbage, "garbage_name" => $garbage_name, "count" => $count,
						"coefficient" => $coefficient,	"sum_points" => $count * $coefficient
					]);
				}
				echo json_encode($teamsGarbages);
			} else {
				echoError(4002);
			}
		}
		break;

	case "POST":
		if (isset($_SESSION['id_user_type'])) {
			if ($_SESSION['id_user_type'])
				if ($_SESSION['id_user_type'] === 1 || $_SESSION['id_user_type'] === 2) {
					if ($_SERVER["CONTENT_TYPE"] !=  'application/json') {
						echoError(5011);
					}
					$postData = file_get_contents('php://input');
					$data = json_decode($postData, true);
					if ($data != NULL) {
						$stmt = $conn->prepare("INSERT INTO persons(lastname,name,middlename,date_of_birth) 
						VALUES(?,?,?,?)");
						$stmt->bind_param("sssd", $data["lastname"], $data["name"], $data["middlename"], $data["date_of_birth"]);
						if (!$stmt->execute()) {
							echoError(5002);
						}
						http_response_code(201);
						$data = json_encode(["id" => $stmt->insert_id]);
						echo $data;
					} else {
						echoError(4001);
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
				if ($_SESSION['id_user_type'] === 1 || $_SESSION['id_user_type'] === 2) {
					if ($_SERVER["CONTENT_TYPE"] != 'application/json') {
						echoError(5011);
					} else {
						$postData = file_get_contents('php://input');
						$data = json_decode($postData, true);
						if (isset($_GET['id_team']) && isset($data)) {
							foreach ($data['collщзжected_garbages'] as &$collected_garbage) {
								$stmt = null;
								if (!isset($collected_garbage['id_collected_garbage'])) {
									$stmt = $conn->prepare("INSERT INTO teams_garbages(id_team, id_garbage, count) VALUES (?,?,?)");
									$stmt->bind_param('iii', $_GET['id_team'], $collected_garbage['id_garbage'], $collected_garbage['count']);
								} else {
									$stmt = $conn->prepare("UPDATE teams_garbages SET count=? WHERE id=?");
									$stmt->bind_param('ii', $collected_garbage['count'], $collected_garbage['id_collected_garbage']);
								}
								$stmt->execute();
								if ($stmt->fetch()) {
									//TODO: unknown
								} else {
									//TODO: unknown
								}
							}
							http_response_code(200);
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
				if ($_SESSION['id_user_type'] === 1 || $_SESSION['id_user_type'] === 2) {
					if (isset($_GET['id'])) {
						$stmt = $conn->prepare("DELETE FROM teams_garbages WHERE id=?");
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
