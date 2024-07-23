<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$data_file = 'locations.json';

function getLocations() {
    global $data_file;
    if (!file_exists($data_file)) {
        return ["error" => "Data file not found"];
    }
    $data = file_get_contents($data_file);
    return json_decode($data, true);
}

function updateLocation($role, $latitude, $longitude, $address) {
    global $data_file;
    $data = getLocations();
    if (isset($data["error"])) {
        return $data;
    }
    if (!in_array($role, ['parent', 'child'])) {
        return ["error" => "Invalid role"];
    }
    $data[$role] = [
        "latitude" => $latitude,
        "longitude" => $longitude,
        "address" => $address
    ];
    file_put_contents($data_file, json_encode($data));
    return $data;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $role = $input['role'] ?? null;
    $latitude = $input['latitude'] ?? null;
    $longitude = $input['longitude'] ?? null;
    $address = $input['address'] ?? null;

    error_log("Received POST data: " . json_encode($input));

    if (!$role || !$latitude || !$longitude || !$address) {
        echo json_encode(["error" => "Missing parameters"]);
        exit;
    }

    $response = updateLocation($role, $latitude, $longitude, $address);
    echo json_encode($response);
} else {
    $response = getLocations();
    echo json_encode($response);
}
?>
