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
				echo json_encode(['error' => "Session id of game or id_team not found"]);
			}
		}
		break;

	case "POST":
		// echo "Это POST запрос, необходим для вставки новых строк в таблицы";
		if ($_SERVER["CONTENT_TYPE"] !=  'application/json') {
			http_response_code(501);
			die(json_encode(["error" => "Server support only json request"]));
		}
		$postData = file_get_contents('php://input');
		$data = json_decode($postData, true);
		if ($data != NULL) {
			$stmt = $conn->prepare("INSERT INTO persons(lastname,name,middlename,date_of_birth) 
			VALUES(?,?,?,?)");
			$stmt->bind_param("sssd", $data["lastname"], $data["name"], $data["middlename"], $data["date_of_birth"]);
			if (!$stmt->execute()) {
				die(json_encode(["error" => $stmt->error]));
			}
			header("Content-Type: application/json");
			http_response_code(201);
			$data = json_encode(["id" => $stmt->insert_id]); //в insert_id находится сгенерированный ИД
			echo $data;
		} else {
			http_response_code(204);
			die(json_encode(["error" => "No Сontent"]));
		}
		break;
	case "PUT":
		if ($_SERVER["CONTENT_TYPE"] != 'application/json') {
			http_response_code(501);
			echo json_encode(["error" => "Server support only json request"]);
		} else {
			$postData = file_get_contents('php://input');
			$data = json_decode($postData, true);
			if (isset($_GET['id_team']) && isset($data)) {
				foreach ($data['collected_garbages'] as &$collected_garbage) {
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
				header("Content-Type: application/json");
				http_response_code(200);
				echo json_encode(['session_id' => 'PHPSESSID=' . session_id(), 'id_user_type' => null, 'id_user' => null]);
			}
		}
		break;
		// case "DELETE":
		// 	// echo "Это DELETE запрос, необходим для удаления строк из таблиц";
		// 	if ($_PUT["id"] == NULL) {
		// 		http_response_code(204);
		// 		die(json_encode(["error" => "No id"]));
		// 	}
		// 	$stmt = $conn->prepare("DELETE FROM persons WHERE id=?");
		// 	$stmt->bind_param("i", $_PUT["id"]);
		// 	if (!$stmt->execute()) {
		// 		die(json_encode(["error" => $stmt->error]));
		// 	}
		// 	header("Content-Type: application/json");
		// 	http_response_code(201);
		// 	$data = json_encode(["id" => $stmt->insert_id]); //в insert_id находится сгенерированный ИД
		// 	echo $data;
		// 	break;
	default:
		http_response_code(405);
		echo 'Method not implemented'; //Это в случае, если используется другой метод, который не реализован
}
$conn->close(); //Закрытие соединения (необходимо делать после окончания работы с БД)