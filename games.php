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
			$stmt->close(); //Завершение запроса
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
			// http_response_code(404);
			// die("id of person not specified");
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
		echo "Это PUT запрос, необходим для изменения полей строк, например, изменения имени с Василий на Максим";
		// if ($_PUT["id"] == NULL) {
		// 	http_response_code(204);
		// 	die(json_encode(["error" => "No id"]));
		// }
		// if ($_SERVER["CONTENT_TYPE"] !=  'application/json') {
		// 	http_response_code(501);
		// 	die(json_encode(["error" => "Server support only json request"]));
		// }
		// $postData = file_get_contents('php://input');
		// $data = json_decode($postData, true);
		// if ($data != NULL) {
		// 	$query = "UPDATE persons SET lastname=?, name=?, middlename=?, date_of_birth=? WHERE id=?";
		// 	$stmt = $conn->prepare($query);
		// 	$stmt->bind_param("sssdi", $data["lastname"], $data["name"], $data["middlename"], $data["date_of_birth"], $_PUT["id"]);
		// 	if (!$stmt->execute()) {
		// 		die(json_encode(["error" => $stmt->error]));
		// 	}
		// 	header("Content-Type: application/json");
		// 	http_response_code(201);
		// 	$data = json_encode(["id" => $stmt->insert_id]); //в insert_id находится сгенерированный ИД
		// 	echo $data;
		// } else {
		// 	http_response_code(204);
		// 	die(json_encode(["error" => "No Сontent"]));
		// }
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
