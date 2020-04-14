<?php

/**
 * $errorCode - код ошибки в приложении
 */
function echoError($errorCode)
{
    switch ($errorCode) {
        case 4001:
            http_response_code(400);
            echoCustomError("Неверные входные данные", 4001);
            break;
        case 4002:
            http_response_code(400);
            echoCustomError("Игра не выбрана", 4002);
            break;
        case 4003:
            http_response_code(400);
            echoCustomError("Игра начата", 4003);
            break;
        case 4004:
            http_response_code(400);
            echoCustomError("Почта или телефон уже используются", 4004);
            break;
        case 4005:
            http_response_code(400);
            echoCustomError("Номер команды уже используется", 4005);
            break;
        case 4011:
            http_response_code(401);
            echoCustomError("Логин или пароль введен неверно", 4011);
            break;
        case 4012:
            http_response_code(401);
            echoCustomError("Не введен логин или пароль", 4012);
            break;
        case 4013:
            http_response_code(401);
            echoCustomError("Не авторизован", 4013);
            break;
        case 4031:
            http_response_code(403);
            echoCustomError("Отсутствует доступ на выполнение данной операции", 4031);
            break;
        case 4041:
            http_response_code(404);
            echoCustomError("Объект с таким id не найден", 4041);
            break;
        case 4051:
            http_response_code(405);
            echoCustomError("Метод не реализован", 4051);
            break;
        case 5001:
            http_response_code(500);
            echoCustomError("Внутренняя ошибка", 5001);
            break;
        case 5002:
            http_response_code(500);
            echoCustomError("Ошибка выполнения запроса к базе данных", 5002);
            break;
        case 5003:
            http_response_code(500);
            echoCustomError("Не удалось соедениться с базой данных", 5003);
            break;
        case 5011:
            http_response_code(501);
            echoCustomError("Сервер поддерживает только JSON запросы", 5011);
            break;
        default:
            http_response_code(520);
            echoCustomError("Неизвестная ошибка", 5201);
    }
}

/**
 * $msg - сообщение ошибки
 * $code - код ошибки в приложении
 */
function echoCustomError($msg, $code)
{
    echo json_encode(["msg" => $msg, "code" => $code]);
}
