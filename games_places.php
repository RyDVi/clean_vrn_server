<?php
include("connect_db.php");

switch ($_SERVER['REQUEST_METHOD']) {
    case "GET":
        header("Content-type: application/json");
        if (isset($_SESSION["id_game"])) {
            $stmt = $conn->prepare("SELECT description, id, point, ST_AsGeoJSON(polygon) FROM games_places WHERE id_game=?");
            $stmt->bind_param("i", $_SESSION["id_game"]);
            if (!$stmt->execute()) {
                echoError(5002);
            }
            $stmt->bind_result($description, $id_place, $point, $polygon);
            $data = [];
            while ($stmt->fetch()) {
                if (isset($point) && isset($polygon)) {
                    $ppoint = unpack('x4/c/L/dlatitude/dlongitude', $point);
                    array_push($data, [
                        "description" => $description, "id_place_type" => $id_place, "point" => ["latitude" => $ppoint['latitude'], "longitude" => $ppoint['longitude']], "polygon" => $polygon
                    ]);
                } else if (!isset($point) && isset($polygon)) {
                    array_push($data, [
                        "description" => $description, "id_place_type" => $id_place, "point" => "", "polygon" => $polygon
                    ]);
                } else if (isset($point) && !isset($polygon)) {
                    $ppoint = unpack('x4/c/L/dlatitude/dlongitude', $point);
                    array_push($data, [
                        "description" => $description, "id_place_type" => $id_place, "point" => ["latitude" => $ppoint['latitude'], "longitude" => $ppoint['longitude']], "polygon" => ""
                    ]);
                } else {
                    array_push($data, [
                        "description" => $description, "id_place_type" => $id_place, "point" => "", "polygon" => ""
                    ]);
                }
            }
            http_response_code(200);
            echo json_encode($data);
        } else {
            echoError(4002);
        }
        break;

    case "POST":
        if (isset($_SESSION['id_user_type'])) {
            if ($_SESSION['id_user_type'] === 1) {
                if ($_SERVER["CONTENT_TYPE"] !=  'application/json') {
                    echoError(5011);
                } else {
                    $postData = file_get_contents('php://input');
                    $data = json_decode($postData, true);
                    if (isset($data)) {
                        if ($data["id_place_type"] === 5) {
                            if (!checkPoly($conn, $data["polygon"])) {
                                $stmt = $conn->prepare("INSERT INTO games_places(description, id_place_type, polygon) VALUES(?, ?, GeomFromText('POLYGON(polygon)'))");
                                $stmt->bind_param("sis", $data["description"], $data["id_place_type"], $data["polygon"]);
                                if (!$stmt->execute()) {
                                    echoError(5002);
                                } else {
                                    http_response_code(201);
                                    echo json_encode([
                                        "id" => $stmt->insert_id, "id_game" => $_SESSION["id_game"],
                                        "id_place" => $data["id_place_type"], "description" => $data["description"]
                                    ]);
                                }
                            } else {
                                echoError(4007);
                            }
                        } else if ($data["id_place_type"] === 3) {
                            if (checkPoint($conn, $data["point"])) {
                                echoError(4006);
                            }
                        } else {
                            $stmt = $conn->prepare("INSERT INTO games_places(description, id_place_type, point) VALUES(?, ?, PointFromText('POINT(" . $data["point"]["latitude"] . " " . $data["point"]["longitude"] . ")'))");
                            $stmt->bind_param("si", $data["description"], $data["id_place_type"]);
                            if (!$stmt->execute()) {
                                echoError(5002);
                            } else {
                                http_response_code(201);
                                echo json_encode([
                                    "id" => $stmt->insert_id, "id_game" => $_SESSION["id_game"],
                                    "id_place" => $data["id_place_type"], "description" => $data["description"]
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
    case "PUT":
        if (isset($_SESSION['id_user_type'])) {
            if ($_SESSION['id_user_type'] === 1) {
                if ($_SERVER["CONTENT_TYPE"] !=  'application/json') {
                    echoError(5011);
                } else {
                    header("Content-Type: application/json");
                    $postData = file_get_contents('php://input');
                    $data = json_decode($postData, true);
                    if (isset($data)) {
                        if (checkNumber($conn, $data['number'])) {
                            $stmt = $conn->prepare("UPDATE games_places SET description WHERE id=?");
                            $stmt->bind_param("isi", $data["description"], $_GET['id']);
                            if (!$stmt->execute()) {
                                echoError(5002);
                            } else {
                                http_response_code(201);
                                if (checkPoint($conn, $data["point"])) {
                                    echoError(4006);
                                } else {
                                    $stmt = $conn->prepare("UPDATE coordinate SET description WHERE id_place=?");
                                    $stmt->bind_param("isi", $data["description"], $_GET['id']);
                                    if (!$stmt->execute()) {
                                        echoError(5002);
                                    } else {
                                        echo json_encode([
                                            "id" => $_GET['id'], "description" => $data['description'], "point" => $data['point']
                                        ]);
                                    }
                                }
                            }
                        } else {
                            echoError(4001);
                        }
                    } else {
                        echoError(4005);
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
            if ($_SESSION['id_user_type'] === 1) {
                header("Content-type: application/json");
                if (isset($_GET['id'])) {
                    $stmt = $conn->prepare("DELETE FROM games_places WHERE id=?");
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

function checkPoint($conn, $stPoint)
{
    $point = "";
    $stmt = $conn->prepare("SELECT point FROM games_places WHERE id_place_type=3");
    if (!$stmt->execute()) {
        echoError(5002);
    }
    $stmt->bind_result($point);
    $ppoint = unpack('x4/c/L/dlatitude/dlongitude', $point);
    $data = [];
    while ($stmt->fetch()) {
        if ($point["latitude"] == $stPoint["latitude"] && $point["longitude"] == $stPoint["longitude"]) {
            return true;
            break;
        }
    }
}

function checkPoly($conn, $poly)
{
    $polyg = [];
    $stmt = $conn->prepare("SELECT ST_AsGeoJSON(polygon) FROM games_places WHERE id_place_type=5");
    if (!$stmt->execute()) {
        echoError(5002);
    }
    $stmt->bind_result($polyg);
    $data = [];
    $i = 1;
    while ($stmt->fetch()) {
        if ($polyg[$i]["latitude"] == $poly["coordinates"][$i] && $polyg[1]["longitude"] == $poly["coordinates"][2]) {
            return true;
            break;
        }
        $i++;
    }
}
