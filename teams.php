<?php
// https://www.php.net/manual/ru/function.http-response-code.php - коды ошибок
// https://restfulapi.net/http-methods/ - описание методов и ответов, которые должны возвращаться
// https://laravel.ru/ - php фреймворк для написания REST API
include("connect_db.php"); //Подключаем все параметры из connect_db.php
// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error); //die прекращает работу php и выводит указанное сообщение об ошибке
}

switch ($_SERVER['REQUEST_METHOD']) {
	case "GET": //GET-запрос - это запросы на получение данных
		header("Content-type: application/json");
		if (!empty($_GET['id'])) {
			$id_team = $_GET['id'];
			$stmt = $conn->prepare("SELECT * FROM teams WHERE id=?"); //запрос к базе данных
			$stmt->bind_param("i", $id_team); //Заменяем ? на переменную id_team типа i (integer)
			$stmt->execute(); //Выполняем запрос
			$stmt->bind_result($id, $number, $name); //называем переменные, куда заносятся данные
			if ($stmt->fetch()) { // Выбираем следующие значения (следующую строку), при это возвращается bool значение
				header("Content-Type: application/json"); //Указываем, что возвращаем данные в виде JSON объекта
				http_response_code(200);
				//Сериализация данных в JSON формат
				$data = json_encode([
					"id" => $id, "name" => $name, "number" => $number
				]);
				echo $data; //Возвращаем данные (отправляем данные клиенту, который производил запрос)
			} else {
				http_response_code(404);
			}
			$stmt->close(); //Завершение запроса
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
				echo json_encode(['error' => "Session id of game not found"]);
			}
		}
		break;

	case "POST":
		if ($_SERVER["CONTENT_TYPE"] !=  'application/json') {
			http_response_code(501);
			die(json_encode(["error" => "Server support only json request"]));
		}
		$postData = file_get_contents('php://input');
		$data = json_decode($postData, true);
		if (isset($data)) {
			if (isset($data['number'])) {
				$stmt = $conn->prepare("INSERT INTO teams(number, name, id_game) VALUES(?,?,?)");
				$stmt->bind_param("isi", $data["number"], $data["name"], $_SESSION["id_game"]);
			} else {
				//TODO: сделать генерацию номера команды
				// $stmtTeamsNumbers = $conn->prepare("SELECT number FROM teams WHERE id_game=?");
				// $stmt->bind_param('i', $data['id_game']);
				// $stmt->bind_result($number);
				// while ($stmt->fetch()) {
				// }
				$stmt = $conn->prepare("INSERT INTO teams(name, id_game) VALUES(?,?)");
				$stmt->bind_param("si", $data["name"], $_SESSION["id_game"]);
			}
			if (!$stmt->execute()) {
				die(json_encode(["error" => $stmt->error]));
			}
			header("Content-Type: application/json");
			http_response_code(201);
			echo json_encode([
				"id" => $stmt->insert_id, 'number' => $data['number'],
				"name" => $data['name']
			]);
		} else {
			http_response_code(204);
			echo json_encode(["error" => "No Сontent"]);
		}
		break;
	case "PUT":
		if ($_SERVER["CONTENT_TYPE"] !=  'application/json') {
			http_response_code(501);
			echo json_encode(["error" => "Server support only json request"]);
		} else {
			header("Content-Type: application/json");
			$postData = file_get_contents('php://input');
			$data = json_decode($postData, true);
			if (isset($data) && isset($_GET['id_team'])) {
				$stmt = $conn->prepare("UPDATE teams SET number=?, name=? WHERE id=?");
				$stmt->bind_param("isi", $data["number"], $data["name"], $_GET['id_team']);
				if (!$stmt->execute()) {
					die(json_encode(["error" => $stmt->error]));
				} else {
					http_response_code(201);
					echo json_encode([
						"id" => $_GET['id_team'], "number" => $data['number'],
						"name" => $data['name']
					]);
				}
			} else {
				http_response_code(204);
				echo json_encode(["error" => "No Сontent"]);
			}
		}
		break;
	default:
		http_response_code(405);
		echo json_encode(['error' => 'Method not implemented']);
}
$conn->close();
