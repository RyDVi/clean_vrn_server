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
			$id_organizator = $_GET['id'];
			$stmt = $conn->prepare("SELECT id, lastname, firstname, middlename, email, phone FROM users WHERE id=? and id_type=2");
			$stmt->bind_param("i", $id_organizator); //Заменяем ? на переменную id_organizator типа i (integer)
			$stmt->execute(); //Выполняем запрос
			$stmt->bind_result($id,  $lastname, $firstname, $middlename, $email, $phone); //называем переменные, куда заносятся данные
			if ($stmt->fetch()) { // Выбираем следующие значения (следующую строку), при это возвращается bool значение
				header("Content-Type: application/json"); //Указываем, что возвращаем данные в виде JSON объекта
				http_response_code(200);
				//Сериализация данных в JSON формат
				$data = json_encode([
					"id" => $id, "lastname" => $lastname, "firstname" => $firstname,
					"middlename" => $middlename, "email" => $email, "phone" => $phone
				]);
				echo $data; //Возвращаем данные (отправляем данные клиенту, который производил запрос)
			} else {
				http_response_code(404);
			}
			$stmt->close(); //Завершение запроса
		} else {
			if (isset($_SESSION['id_game'])) {
				$stmt = $conn->prepare("SELECT u.id, u.lastname, u.firstname, u.middlename, u.email, u.phone FROM users u INNER JOIN game_users gu on u.id=gu.id_user WHERE id_type=2 AND gu.id_game=?");
				$stmt->bind_param('i', $_SESSION['id_game']);
				$stmt->execute();
				$stmt->bind_result($id,  $lastname, $firstname, $middlename, $email, $phone);
				$data = [];
				while ($stmt->fetch()) {
					array_push($data, [
						"id" => $id, "lastname" => $lastname, "firstname" => $firstname,
						"middlename" => $middlename, "email" => $email, "phone" => $phone
					]);
				}
				echo json_encode($data);
			} else {
				json_encode(['error' => "Session id of game not found"]);
			}
		}
		break;

	case "POST":
		$idUserType = $_SESSION('id_user_type');
		if(isset($idUserType))
		{
			if($idUserType == "1") {
				if ($_SERVER["CONTENT_TYPE"] !=  'application/json') {
					http_response_code(501);
					echo json_encode(["error" => "Server support only json request"]);
				} else {
					header("Content-Type: application/json");
					$postData = file_get_contents('php://input');
					$data = json_decode($postData, true);
					if (isset($data) && $_SESSION["id_game"]) {
						$stmt = $conn->prepare("INSERT INTO users(id_type, lastname, firstname, middlename, email, phone) 
					VALUES(2,?,?,?,?,?)");
						$stmt->bind_param("sssss", $data["lastname"], $data["firstname"], $data["middlename"], $data["email"], $data["phone"]);
						if (!$stmt->execute()) {
							echo json_encode(["error" => $stmt->error]);
						} else {
							$stmtGameUser = $conn->prepare("INSERT INTO game_users(id_game, id_user) VALUES (?,?)");
							$idCreatedUser = $stmt->insert_id;
							$stmtGameUser->bind_param("ii", $_SESSION["id_game"], $idCreatedUser);
							if (!$stmtGameUser->execute()) {
								echo json_encode(["error" => $stmtGameUser->error]);
							} else {
								http_response_code(201);
								echo json_encode([
									"id" => $stmt->insert_id,  "lastname" => $data["lastname"], "firstname" => $data["firstname"],
									"middlename" => $data["middlename"], "email" => $data["email"], "phone" => $data["phone"]
								]);
							}
						}
					} else {
						http_response_code(204);
						echo json_encode(["error" => "No Сontent"]);
					}
				}
			}
			else {
				http_response_code(403);
			}
		}
		break;
	case "PUT":
		$idUserType = $_SESSION('id_user_type');
		if(isset($idUserType))
		{
			if($idUserType == "1") {
				if ($_SERVER["CONTENT_TYPE"] !=  'application/json') {
					http_response_code(501);
					echo json_encode(["error" => "Server support only json request"]);
				} else {
					header("Content-Type: application/json");
					$postData = file_get_contents('php://input');
					$data = json_decode($postData, true);
					if (isset($data) && $_GET["id"]) {
						$stmt = $conn->prepare("UPDATE users SET lastname=?, firstname=?, middlename=?, email=?, phone=? WHERE id=?");
						$stmt->bind_param("sssssi", $data["lastname"], $data["firstname"], $data["middlename"], $data["email"], $data["phone"], $_GET["id"]);
						if (!$stmt->execute()) {
							echo json_encode(["error" => $stmt->error]);
						} else {
							http_response_code(201);
							echo json_encode(['session_id' => 'PHPSESSID=' . session_id(), 'id_user_type' => null, 'id_user' => null]);
						}
					} else {
						http_response_code(204);
						echo json_encode(["error" => "No Сontent"]);
					}
				}
			}
			else {
				http_response_code(403);
			}
		}
		break;
		case "DELETE":
			$idUserType = $_SESSION('id_user_type');
			if(isset($idUserType))
			{
				if($idUserType == "1") {
					header("Content-type: application/json");
					if (isset($_GET['id'])) {
						$stmt = $conn->prepare("DELETE FROM users WHERE id=?");
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
				}
				else {
					http_response_code(403);
				}
			}
			break;
	default:
		http_response_code(405);
		echo 'Method not implemented'; //Это в случае, если используется другой метод, который не реализован
}
$conn->close(); //Закрытие соединения (необходимо делать после окончания работы с БД)
