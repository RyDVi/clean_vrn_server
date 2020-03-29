<?php
// https://www.php.net/manual/ru/function.http-response-code.php - коды ошибок
// https://restfulapi.net/http-methods/ - описание методов и ответов, которые должны возвращаться
// https://laravel.ru/ - php фреймворк для написания REST API
// Проверка гит
include("connect_db.php"); //Подключаем все параметры из connect_db.php
// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error); //die прекращает работу php и выводит указанное сообщение об ошибке
}

switch ($_SERVER['REQUEST_METHOD']) {
	case "GET": //GET-запрос - это запросы на получение данных
		header("Content-type: application/json");
		if (!empty($_GET['id'])) {
			$id_person = $_GET['id'];
			$stmt = $conn->prepare("SELECT * FROM games WHERE id=?"); //запрос к базе данных
			$stmt->bind_param("i", $id_person); //Заменяем ? на переменную id_person типа i (integer)
			$stmt->execute(); //Выполняем запрос
			$stmt->bind_result($id, $id_status, $name, $description, $route, $datetime); //называем переменные, куда заносятся данные
			if ($stmt->fetch()) { // Выбираем следующие значения (следующую строку), при это возвращается bool значение
				header("Content-Type: application/json"); //Указываем, что возвращаем данные в виде JSON объекта
				http_response_code(200);
				//Сериализация данных в JSON формат
				$data = json_encode([
					"id" => $id, "id_status" => $id_status, "name" => $name, "description" => $description,
					"route" => $route, "datetime" => $datetime
				]);
				echo $data; //Возвращаем данные (отправляем данные клиенту, который производил запрос)
			} else {
				http_response_code(404);
			}
		} else {
			$stmt = $conn->prepare("SELECT * FROM games");
			$stmt->execute();
			$stmt->bind_result($id, $id_status, $name, $description, $route, $datetime);
			$data = [];
			while ($stmt->fetch()) {
				array_push($data, [
					"id" => $id, "id_status" => $id_status, "name" => $name, "description" => $description,
					"route" => $route, "datetime" => $datetime
				]);
			}
			echo json_encode($data);
		}
		break;

	case "POST":
		if ($_SERVER["CONTENT_TYPE"] !=  'application/json') {
			http_response_code(501);
			die(json_encode(["error" => "Server support only json request"]));
		}
		$postData = file_get_contents('php://input');
		$data = json_decode($postData, true);
		header("Content-Type: application/json");
		if (isset($data)) {
			$stmt = $conn->prepare("INSERT INTO games(id_status, name, route, datetime) VALUES(1, ?, ?, ?)");
			$stmt->bind_param("sss", $data["name"],  $data["route"], $data["datetime"]);
			if (!$stmt->execute()) {
				die(json_encode(["error" => $stmt->error]));
			}
			http_response_code(201);
			echo json_encode([
				"id" => $stmt->insert_id, "name" => $data['name'],
				"route" => $data['route'], "datetime" => $data['datetime']
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
			$postData = file_get_contents('php://input');
			$data = json_decode($postData, true);
			header("Content-Type: application/json");
			if (isset($data) && isset($_GET['id_game'])) {
				$stmt = $conn->prepare("UPDATE games SET name=?,  route=?, datetime=?  WHERE id=?");
				$stmt->bind_param("sssi", $data["name"],  $data["route"], $data["datetime"], $_GET['id_game']);
				if (!$stmt->execute()) {
					die(json_encode(["error" => $stmt->error]));
				} else {
					http_response_code(201);
					echo json_encode([
						"id" => $_GET['id_game'], "name" => $data['name'],
						"route" => $data['route'], "datetime" => $data['datetime']
					]);
				}
			} else {
				http_response_code(204);
				echo json_encode(["error" => "No Сontent"]);
			}
		}
		break;
	case "DELETE":
		header("Content-type: application/json");
		if (isset($_GET['id'])) {
			$stmt = $conn->prepare("DELETE FROM games WHERE id=?");
			$stmt->bind_param("i", $_GET['id']);
			$stmt->execute();
			if ($stmt->affected_rows > 0) {
				http_response_code(200);
			} else {
				http_response_code(404);
			}
			$stmt->close();
		} else {
			http_response_code(404);
		}
		break;
	default:
		http_response_code(405);
		echo 'Method not implemented';
}
$conn->close();
