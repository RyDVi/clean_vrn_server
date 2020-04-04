<?php
// https://www.php.net/manual/ru/function.http-response-code.php - коды ошибок
// https://restfulapi.net/http-methods/ - описание методов и ответов, которые должны возвращаться
// https://laravel.ru/ - php фреймворк для написания REST API
include("connect_db.php");
include("errors.php"); //Подключаем все параметры из connect_db.php
// Check connection
if ($conn->connect_error) {
	echoError(5003);
}
header("Content-type: application/json");
switch ($_SERVER['REQUEST_METHOD']) {
	case "GET": //GET-запрос - это запросы на получение данных
		if (!empty($_GET['id'])) {
			$id_team = $_GET['id'];
			$stmt = $conn->prepare("SELECT * FROM teams WHERE id=?"); //запрос к базе данных
			$stmt->bind_param("i", $id_team); //Заменяем ? на переменную id_team типа i (integer)
			$stmt->execute(); //Выполняем запрос
			$stmt->bind_result($id, $number, $name); //называем переменные, куда заносятся данные
			if ($stmt->fetch()) { // Выбираем следующие значения (следующую строку), при это возвращается bool значение
				http_response_code(200);
				//Сериализация данных в JSON формат
				$data = json_encode([
					"id" => $id, "name" => $name, "number" => $number
				]);
				echo $data; //Возвращаем данные (отправляем данные клиенту, который производил запрос)
			} else {
				echoError(4041);
			}
			$stmt->close(); //Завершение запроса
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
		// echo "Это POST запрос, необходим для вставки новых строк в таблицы";
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
		break;
	case "PUT":
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
		break;
	default:
		echoError(4051);
}
$conn->close(); //Закрытие соединения (необходимо делать после окончания работы с БД)
