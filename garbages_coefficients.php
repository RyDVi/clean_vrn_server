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
			if (isset($_GET['id_game'])) {
				$stmtCoefficients = $conn->prepare("SELECT gc.id, gc.id_garbage, gc.id_game, g.name, gc.coefficient FROM garbage_coefficients gc INNER JOIN garbages g ON gc.id_garbage=g.id WHERE id_game=? UNION ALL SELECT null, id as id_garbage, null, name, null FROM garbages WHERE id NOT IN (SELECT id_garbage FROM garbage_coefficients WHERE id_game=?)");
				$stmtCoefficients->bind_param('ii', $_GET['id_game'], $_GET['id_game']);
				$stmtCoefficients->execute();
				$stmtCoefficients->bind_result(
					$id,
					$id_garbage,
					$id_game,
					$name,
					$coefficient
				);
				$garbagesCoefficients = [];
				while ($stmtCoefficients->fetch()) {
					//sum_points - количество очков за вид мусора (количество * коэффициент мусора)
					//id_collected_garbage - ид из таблицы teams_garbages
					array_push($garbagesCoefficients, [
						"id" => $id, "id_garbage" => $id_garbage, "name" => $name,
						"id_game" => $id_game, "coefficient" => $coefficient
					]);
				}
				echo json_encode($garbagesCoefficients);
			} else {
				echoError(4041);
			}
		}
		break;

	case "POST":
		if (date_g($conn)) {
			if ($_SERVER["CONTENT_TYPE"] !=  'application/json') {
				echoError(5011);
			}
			$postData = file_get_contents('php://input');
			$data = json_decode($postData, true);
			if (isset($_GET['id_game']) && isset($data)) {
				$createdCoefficient = [];
				foreach ($data['coefficients'] as &$coefficients) {
					$stmt = $conn->prepare("INSERT INTO garbage_coefficients(id_garbage, id_game, coefficient) VALUES (?,?,?)");
					$stmt->bind_param("iii", $coefficients['id_garbage'], $_GET['id_game'], $coefficients['coefficient']);
					if (!$stmt->execute()) {
						echoError(5002);
					} else {
						array_push($createdCoefficient, [
							"id" => $stmt->insert_id, "id_garbage" => $coefficients['id_garbage'],
							"id_game" => $_GET['id_game'], "coefficient" => $coefficients['coefficient']
						]);
					}
				}
				http_response_code(201);
				echo json_encode($createdCoefficient);
			} else {
				echoError(4001);
			}
		} else {
			echoError(4003);
		}
		break;
	case "PUT":
		if (date_g($conn)) {
			if ($_SERVER["CONTENT_TYPE"] != 'application/json') {
				echoError(5011);
			} else {
				$postData = file_get_contents('php://input');
				$data = json_decode($postData, true);
				if (isset($_GET['id_game']) && isset($data)) {
					foreach ($data['coefficients'] as &$coefficients) {
						$stmt = null;
						if (!isset($coefficients['id'])) {
							$stmt = $conn->prepare("INSERT INTO garbage_coefficients(id_garbage, id_game, coefficient) VALUES (?,?,?)");
							$stmt->bind_param('iii', $coefficients['id_garbage'], $_GET['id_game'], $coefficients['coefficient']);
						} else {
							$stmt = $conn->prepare("UPDATE garbage_coefficients SET coefficient=? WHERE id=?");
							$stmt->bind_param('ii', $coefficients['coefficient'], $coefficients['id']);
						}
						$stmt->execute();
						if ($stmt->fetch()) {
							//TODO: unknown
						} else {
							//TODO: unknown
						}
					}
					http_response_code(200);
				} else {
					echoError(4001);
				}
			}
		} else {
			echoError(4003);
		}
		break;
	default:
		echoError(4051);
}

$conn->close();
/**
 * Проверка на совпадение времени
 */
function date_g(mysqli $conn)
{
	$time = date('Y-m-d H:i:s');
	$stmt = $conn->prepare("SELECT g.datetime FROM garbage_coefficients gc inner JOIN games g on gc.id_game = g.id WHERE gc.id=?");
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
