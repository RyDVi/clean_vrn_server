<?php
include('connect_db.php');

header('Content-type: application/json');
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (isset($_SESSION['id_game'])) {
            $stmt = $conn->prepare("SELECT id, id_place_type, description, ST_AsGeoJSON(point), ST_AsGeoJSON(polygon)"
                . "FROM games_places WHERE id_game={$_SESSION['id_game']}");
            if (!$stmt->execute()) {
                echoError(5002);
            }
            $stmt->bind_result($id, $id_place_type, $description, $point, $polygon);
            $data = [];
            while ($stmt->fetch()) {
                $googlePoint = null;
                if (isset($point)) {
                    $point = json_decode($point, true);
                    $googlePoint = [
                        'latitude' => $point['coordinates'][0],
                        'longitude' => $point['coordinates'][1]
                    ];
                }
                $googlePolygon = null;
                if (isset($polygon)) {
                    $polygon = json_decode($polygon, true);
                    $googlePolygon = [];
                    foreach ($polygon['coordinates'][0] as &$polygonCoordinate) {
                        array_push($googlePolygon, ['latitude' => $polygonCoordinate[0], 'longitude' => $polygonCoordinate[1]]);
                    }
                }
                array_push($data, [
                    "id" => $id, 'description' => $description, 'id_place_type' => $id_place_type,
                    'point' => $googlePoint, 'polygon' => $googlePolygon
                ]);
            }
            http_response_code(200);
            echo json_encode($data);
        } else {
            echoError(4002);
        }
        break;
    case 'POST':
        if (isset($_SESSION['id_user_type'])) {
            if ($_SESSION['id_user_type'] === 1) {
                if ($_SERVER['CONTENT_TYPE'] !=  'application/json') {
                    echoError(5011);
                } else {
                    $postData = file_get_contents('php://input');
                    $data = json_decode($postData, true);
                    if (isset($data)) {
                        //Почему-то ругается на description
                        if (!isset($data['description'])) {
                            $data['description'] = "";
                        }
                        if ($data['id_place_type'] === 5) {
                            $mysqlPolygon = "";
                            $mysqlPolygon .= "{$data['polygon'][0]['latitude']} {$data['polygon'][0]['longitude']}";
                            for ($iCoordinate = 1; $iCoordinate < count($data['polygon']); $iCoordinate++) {
                                $mysqlPolygon .= ",{$data['polygon'][$iCoordinate]['latitude']} {$data['polygon'][$iCoordinate]['longitude']}";
                            }
                            $idGameZone = getGameZoneId($conn, $_SESSION['id_game']);
                            if ($idGameZone) {
                                $stmt = $conn->prepare("UPDATE games_places"
                                    . " SET description='{$data['description']}'"
                                    . " , polygon=GeomFromText('POLYGON(($mysqlPolygon))')"
                                    . " WHERE id=$idGameZone");
                            } else {
                                $stmt = $conn->prepare("INSERT INTO games_places(id_game, description, id_place_type, polygon)"
                                    . "VALUES({$_SESSION['id_game']},'{$data['description']}',{$data['id_place_type']},"
                                    . "GeomFromText('POLYGON(($mysqlPolygon))'))");
                            }
                            if (!$stmt->execute()) {
                                echoError(5002);
                            } else {
                                http_response_code(201);
                                echo json_encode([
                                    'id' => $stmt->insert_id, 'id_game' => $_SESSION['id_game'],
                                    'id_place' => $data['id_place_type'], 'description' => $data['description'],
                                    'polygon' => $data['polygon']
                                ]);
                            }
                        } else {
                            if ($data['id_place_type'] === 4) {
                                $id_start_point = getPointOfStart($conn, $_SESSION['id_game']);
                                if ($id_start_point) {
                                    $stmt = $conn->prepare("UPDATE games_places"
                                        . " SET description='{$data['description']}', id_place_type={$data['id_place_type']}"
                                        . " ,point=PointFromText('POINT({$data['point']['latitude']} {$data['point']['longitude']})')"
                                        . " WHERE id=$id_start_point");
                                } else {
                                    $stmt = $conn->prepare("INSERT INTO games_places(id_game, description, id_place_type, point)"
                                        . "VALUES({$_SESSION['id_game']},'{$data['description']}',{$data['id_place_type']},"
                                        . "PointFromText('POINT({$data['point']['latitude']} {$data['point']['longitude']})'))");
                                }
                            } else {
                                $stmt = $conn->prepare("INSERT INTO games_places(id_game, description, id_place_type, point)"
                                    . "VALUES({$_SESSION['id_game']},'{$data['description']}', {$data['id_place_type']}, "
                                    . "PointFromText('POINT({$data['point']['latitude']} {$data['point']['longitude']})'))");
                            }
                            if (!$stmt->execute()) {
                                echoError(5002);
                            } else {
                                http_response_code(201);
                                echo json_encode([
                                    'id' => $stmt->insert_id, 'id_game' => $_SESSION['id_game'],
                                    'id_place' => $data['id_place_type'], 'description' => $data['description'],
                                    'point' => $data['point']
                                ]);
                            }
                        }
                    } else {
                        echoError(4001);
                    }
                }
            } else {
                echoError(4031);
            }
        } else {
            echoError(4013);
        }
        break;
    case 'PUT':
        if (isset($_SESSION['id_user_type'])) {
            if ($_SESSION['id_user_type'] === 1) {
                if ($_SERVER['CONTENT_TYPE'] !=  'application/json') {
                    echoError(5011);
                } else {
                    header('Content-Type: application/json');
                    $postData = file_get_contents('php://input');
                    $data = json_decode($postData, true);
                    if (isset($data) && isset($_GET['id']) && $data['id_place_type'] != 5) {
                        $stmt = $conn->prepare("UPDATE games_places"
                            . " SET description='{$data['description']}', id_place_type={$data['id_place_type']}"
                            . " ,point=PointFromText('POINT({$data['point']['latitude']} {$data['point']['longitude']})')"
                            . " WHERE id={$_GET['id']}");
                        if (!$stmt->execute()) {
                            echoError(5002);
                        } else {
                            http_response_code(200);
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
    case 'DELETE':
        if (isset($_SESSION['id_user_type'])) {
            if ($_SESSION['id_user_type'] === 1) {
                header('Content-type: application/json');
                if (isset($_GET['id'])) {
                    $stmt = $conn->prepare("DELETE FROM games_places WHERE id=?");
                    $stmt->bind_param('i', $_GET['id']);
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

function getPointOfStart($conn, $idGame)
{
    $stmt = $conn->prepare("SELECT id FROM games_places WHERE id_place_type=4 AND id_game=?");
    $stmt->bind_param('i', $idGame);
    if (!$stmt->execute()) {
        echoError(5002);
    }
    $id_point = null;
    $stmt->bind_result($id_point);
    if ($stmt->fetch()) {
        return $id_point;
    } else {
        return false;
    }
}

function getGameZoneId($conn, $idGame)
{
    $stmt = $conn->prepare("SELECT id FROM games_places WHERE id_place_type=5 AND id_game=?");
    $stmt->bind_param('i', $idGame);
    if (!$stmt->execute()) {
        echoError(5002);
    }
    $idGameZone = null;
    $stmt->bind_result($idGameZone);
    if ($stmt->fetch()) {
        return $idGameZone;
    } else {
        return false;
    }
}
