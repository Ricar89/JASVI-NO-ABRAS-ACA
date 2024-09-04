<?php
$servername_control = "localhost";
$dBUsername_control = "id22114529_thegripisrolling";
$dBPassword_control = "KLMklm4805!";
$dBName_control = "id22114529_for_grip";


$conn_control = mysqli_connect($servername_control, $dBUsername_control, $dBPassword_control, $dBName_control);

if (!$conn_control) {
    die("Connection failed: " . mysqli_connect_error());
}

$response = array();


$sql_control = "SELECT name, status FROM control_status";
$result_control = mysqli_query($conn_control, $sql_control);

if ($result_control) {
    while ($row = mysqli_fetch_assoc($result_control)) {
        $response[$row['name']] = $row['status'];
    }
}


mysqli_close($conn_control);


$servername_sensor = "localhost";
$dBUsername_sensor = "id22114529_richardatomiccompany";
$dBPassword_sensor = "KLMklm4805!";
$dBName_sensor = "id22114529_dht22database";

$conn_sensor = mysqli_connect($servername_sensor, $dBUsername_sensor, $dBPassword_sensor, $dBName_sensor);

if (!$conn_sensor) {
    die("Connection failed: " . mysqli_connect_error());
}


$sql_sensor = "SELECT id, temperature, humidity FROM dht22_readings";
$result_sensor = mysqli_query($conn_sensor, $sql_sensor);

if ($result_sensor) {
    while ($row = mysqli_fetch_assoc($result_sensor)) {
        $response['sensor_' . $row['id']] = array(
            'temperature' => $row['temperature'],
            'humidity' => $row['humidity']
        );
    }
}


mysqli_close($conn_sensor);


header('Content-Type: application/json');
echo json_encode($response);
?>