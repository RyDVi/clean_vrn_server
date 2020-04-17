<?php
include("connect_db.php");

switch ($_SERVER['REQUEST_METHOD']) {
	case "GET":
		header("Content-type: application/json");
		if (!empty($_GET['id'])) {
			$id_team = $_GET['id'];
			$stmt = $conn->prepare("SELECT * FROM teams WHERE id=?");
			$stmt->bind_param("i", $id_team);
			$stmt->execute();
			$stmt->bind_result($id, $number, $name);
			if ($stmt->fetch()) {
				header("Content-Type: application/json");
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
			if (isset($_SESSION['id_game'])) {
				$stmtGarbages = $conn->prepare("SELECT tg.id_team, SUM(count*coefficient)FROM garbages g INNER JOIN garbage_coefficients gc ON g.id=gc.id_garbage INNER JOIN teams_garbages tg ON g.id=tg.id_garbage WHERE gc.id_game=? GROUP BY tg.id_team");
				$stmtGarbages->bind_param('i', $_SESSION['id_game']);
				$stmtGarbages->execute();
				$stmtGarbages->bind_result($id_team, $sum_points);
				$teamsPoints = [];
				while ($stmtGarbages->fetch()) {
					array_push($teamsPoints, [
						"id_team" => $id_team, "sum_points" => (int) $sum_points
					]);
				}
				$stmt = $conn->prepare("SELECT t.id, t.name, t.number FROM teams t WHERE t.id_game=?");
				$stmt->bind_param('i', $_SESSION['id_game']);
				$stmt->execute();
				$stmt->bind_result($id,  $name, $number);
				$data = [];
				while ($stmt->fetch()) {
					$indexOfTeamPoints = array_search($id, array_column($teamsPoints, 'id_team'));
					if ($indexOfTeamPoints !== false) {
						array_push($data, [
							"id" => $id, "name" => $name, "number" => $number, "sum_points" => $teamsPoints[$indexOfTeamPoints]['sum_points']
						]);
					} else {
						array_push($data, [
							"id" => $id, "name" => $name, "number" => $number, "sum_points" => 0
						]);
					}
				}
				echo json_encode($data);
			} else {
				echoError(4002);
			}
		}
		break;

	case "POST":
		if (isset($_SESSION['id_user_type'])) {
			if ($_SESSION['id_user_type'] === 1 || $_SESSION['id_user_type'] === 2) {
				if ($_SERVER["CONTENT_TYPE"] !=  'application/json') {
					echoError(5011);
				}
				$postData = file_get_contents('php://input');
				$data = json_decode($postData, true);
				if (isset($data)) {
					if (isset($data['number'])) {
						$numberTeam = $data["number"];
					} else {
						$numberTeam = generateNumber($conn, $_SESSION['id_game']);
					}
					if (checkNumber($conn, $numberTeam, null, $_SESSION['id_game'])) {
						$stmt = $conn->prepare("INSERT INTO teams(number, name, id_game) VALUES(?,?,?)");
						$stmt->bind_param("isi", $numberTeam, $data["name"], $_SESSION["id_game"]);
						if (!$stmt->execute()) {
							echoError(5002);
						}
						header("Content-Type: application/json");
						http_response_code(201);
						echo json_encode([
							"id" => $stmt->insert_id, 'number' => $numberTeam,
							"name" => $data['name']
						]);
					} else {
						echoError(4005);
					}
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
			if ($_SESSION['id_user_type'] === 1 || $_SESSION['id_user_type'] === 2) {
				if ($_SERVER["CONTENT_TYPE"] !=  'application/json') {
					echoError(5011);
				} else {
					header("Content-Type: application/json");
					$postData = file_get_contents('php://input');
					$data = json_decode($postData, true);
					if (isset($data) && isset($_GET['id_team'])) {
						if (isset($data['number'])) {
							$numberTeam = $data["number"];
						} else {
							$numberTeam = generateNumber($conn, $_SESSION['id_game']);
						}
						if (checkNumber($conn, $numberTeam, $_GET["id_team"], $_SESSION['id_game'])) {
							$stmt = $conn->prepare("UPDATE teams SET number=?, name=? WHERE id=?");
							$stmt->bind_param("isi", $numberTeam, $data["name"], $_GET['id_team']);
							if (!$stmt->execute()) {
								echoError(5002);
							} else {
								http_response_code(201);
								echo json_encode([
									"id" => $_GET['id_team'], "number" => $numberTeam,
									"name" => $data['name']
								]);
							}
						} else {
							echoError(4005);
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
			if ($_SESSION['id_user_type'] === 1 || $_SESSION['id_user_type'] === 2) {
				header("Content-type: application/json");
				if (isset($_GET['id'])) {
					$stmt = $conn->prepare("DELETE FROM teams WHERE id=?");
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
function checkNumber(mysqli $conn, $CheckNumber, $idTeam, $idGame)
{
	if (isset($idTeam)) {
		$stmt = $conn->prepare("SELECT * FROM teams WHERE number=? AND id!=? AND id_game=?");
		$stmt->bind_param('iii', $CheckNumber, $idTeam, $idGame);
	} else {
		$stmt = $conn->prepare("SELECT * FROM teams WHERE number=? AND id_game=?");
		$stmt->bind_param('ii', $CheckNumber, $idGame);
	}
	if (!$stmt->execute()) {
		echoError(5002);
	}
	$tr = true;
	if ($stmt->fetch())
		$tr = false;

	return $tr;
}

function generateNumber(mysqli $conn, $idGame)
{
	$stmt = $conn->prepare("SELECT number FROM teams WHERE id_game=?");
	$stmt->bind_param('i', $idGame);
	if (!$stmt->execute()) {
		echoError(5002);
	}
	$stmt->bind_result($numberTeam);
	$numbers = [];
	while ($stmt->fetch()) {
		array_push($numbers, $numberTeam);
	}
	$isNumberFinded = false;
	$generatedNumber = 0;
	while (!$isNumberFinded) {
		$generatedNumber++;
		$isNumberFinded = true;
		foreach ($numbers as &$number) {
			if ($generatedNumber === $number) {
				$isNumberFinded = false;
				break;
			}
		}
	}
	return $generatedNumber;
}
