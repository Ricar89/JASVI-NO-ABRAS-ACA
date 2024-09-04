<?php
// se conecta a la base de datos del sensor de temperatura
$servername_sensor = "localhost";
$dBUsername_sensor = "id22114529_richardatomiccompany";
$dBPassword_sensor = "KLMklm4805!";
$dBName_sensor = "id22114529_dht22database";
$conn_sensor = mysqli_connect($servername_sensor, $dBUsername_sensor, $dBPassword_sensor, $dBName_sensor);

if (!$conn_sensor) {
    die("Connection failed: " . mysqli_connect_error());
}

// lee los datos del DHT22
$sql_sensor = "SELECT * FROM dht22_readings ORDER BY timestamp DESC LIMIT 1";
$result_sensor = mysqli_query($conn_sensor, $sql_sensor);
$temperature = "N/A";
$humidity = "N/A";

if ($result_sensor && mysqli_num_rows($result_sensor) > 0) {
    $row_sensor = mysqli_fetch_assoc($result_sensor);
    $temperature = $row_sensor['temperature'];
    $humidity = $row_sensor['humidity'];
}

echo json_encode(['temperature' => $temperature, 'humidity' => $humidity]);

mysqli_close($conn_sensor);
?>