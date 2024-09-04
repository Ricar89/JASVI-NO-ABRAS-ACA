<?php
// se conecta a la base de datos de control
$servername_control = "localhost";
$dBUsername_control = "id22114529_thegripisrolling";
$dBPassword_control = "KLMklm4805!";
$dBName_control = "id22114529_for_grip";
$conn_control = mysqli_connect($servername_control, $dBUsername_control, $dBPassword_control, $dBName_control);

if (!$conn_control) {
    die("Connection failed: " . mysqli_connect_error());
}

// se conecta a la base de datos del sensor de temperatura
$servername_sensor = "localhost";
$dBUsername_sensor = "id22114529_richardatomiccompany";
$dBPassword_sensor = "KLMklm4805!";
$dBName_sensor = "id22114529_dht22database";
$conn_sensor = mysqli_connect($servername_sensor, $dBUsername_sensor, $dBPassword_sensor, $dBName_sensor);

if (!$conn_sensor) {
    die("Connection failed: " . mysqli_connect_error());
}

// función para alternar el estado
function toggleStatus($conn, $name) {
    $sql = "SELECT status FROM control_status WHERE name = '$name'";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $current_status = $row['status'];
        $new_status = $current_status == 1 ? 0 : 1;
        mysqli_query($conn, "UPDATE control_status SET status = $new_status WHERE name = '$name'");
    }
}

// acciones de los botones
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['avanzar'])) {
        toggleStatus($conn_control, 'motor_advance');
    } elseif (isset($_POST['izquierda'])) {
        toggleStatus($conn_control, 'servo0_left');
    } elseif (isset($_POST['derecha'])) {
        toggleStatus($conn_control, 'servo0_right');
    } elseif (isset($_POST['ir_atras'])) {
        toggleStatus($conn_control, 'motor_backward');
    } elseif (isset($_POST['grip_eje_derecha'])) {
        toggleStatus($conn_control, 'servo1_right');
    } elseif (isset($_POST['grip_eje_izquierda'])) {
        toggleStatus($conn_control, 'servo1_left');
    } elseif (isset($_POST['grip_horizontal_abajo'])) {
        toggleStatus($conn_control, 'servo2_down');
    } elseif (isset($_POST['grip_horizontal_arriba'])) {
        toggleStatus($conn_control, 'servo2_up');
    } elseif (isset($_POST['grip_cerrar'])) {
        toggleStatus($conn_control, 'servo3_close');
    } elseif (isset($_POST['grip_abrir'])) {
        toggleStatus($conn_control, 'servo3_open');
    }
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="styles.css">
    <title>Control Panel</title>
    <script>
        function fetchSensorData() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'fetch_sensor_data.php', true);
            xhr.onload = function() {
                if (this.status === 200) {
                    const data = JSON.parse(this.responseText);
                    document.getElementById('temperature').innerText = data.temperature + ' °C';
                    document.getElementById('humidity').innerText = data.humidity + ' %';
                }
            };
            xhr.send();
        }

        setInterval(fetchSensorData, 5000); // Actualiza cada 5 segundos
    </script>
</head>
<body>
    <div class="button-container">
        <form method="post">
            <input type="submit" name="avanzar" value="Avanzar">
            <input type="submit" name="izquierda" value="Izquierda">
            <input type="submit" name="derecha" value="Derecha">
            <input type="submit" name="ir_atras" value="Ir hacia atrás">
        </form>
        <form method="post">
            <input type="submit" name="grip_eje_derecha" value="Grip Eje Derecha">
            <input type="submit" name="grip_eje_izquierda" value="Grip Eje Izquierda">
            <input type="submit" name="grip_horizontal_abajo" value="Grip Horizontal Abajo">
            <input type="submit" name="grip_horizontal_arriba" value="Grip Horizontal Arriba">
            <input type="submit" name="grip_cerrar" value="Grip Cerrar">
            <input type="submit" name="grip_abrir" value="Grip Abrir">
        </form>
    </div>
    <div class="sensor-data">
        <h2>Temperatura: <span id="temperature"><?php echo $temperature; ?></span> °C</h2>
        <h2>Humedad: <span id="humidity"><?php echo $humidity; ?></span> %</h2>
    </div>
</body>
</html>