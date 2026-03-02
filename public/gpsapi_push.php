<?php
// Database connection parameters
$hostname = 'localhost';
$username = 'gallop_school_erp';
$password = 'GallopBiz13#!';
$database = '2025_2026';
$message = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Create a PDO database connection
        $pdo = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);

        // Set PDO error mode to exceptions
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $json_data = file_get_contents('php://input');
        $datas = json_decode($json_data, true);
        // print_r($datas);
        foreach($datas as $data){
            // Process the GET request
            if (isset($data['vehicle_name'])) {
                // $data = json_decode($_GET['data'], true);
                // Insert data into the database
                $sql = "INSERT INTO gps_data1 (vehicle_name, gps, vehicle_no, branch, vehicletype, status, speed, ign, battery_percentage, power, location, latitude, longitude) VALUES (:vehicle_name, :gps, :vehicle_no, :branch, :vehicletype, :status, :speed, :ign, :battery_percentage, :power, :location, :latitude, :longitude)";
                $stmt = $pdo->prepare($sql);

                // Bind the values to the named placeholders
                $stmt->bindParam(':vehicle_name', $data['vehicle_name']);
                $stmt->bindParam(':gps', $data['gps']);
                $stmt->bindParam(':vehicle_no', $data['vehicle_no']);
                $stmt->bindParam(':branch', $data['branch']);
                $stmt->bindParam(':vehicletype', $data['vehicletype']);
                $stmt->bindParam(':status', $data['status']);
                $stmt->bindParam(':speed', $data['speed']);
                $stmt->bindParam(':ign', $data['ign']);
                $stmt->bindParam(':battery_percentage', $data['battery_percentage']);
                $stmt->bindParam(':power', $data['power']);
                $stmt->bindParam(':location', $data['location']);
                $stmt->bindParam(':latitude', $data['latitude']);
                $stmt->bindParam(':longitude', $data['longitude']);

                // Execute the SQL statement to insert the data
                $stmt->execute();

                $message = ["message" => "Data inserted successfully."];
            } else {
                $message = ["message" => "Method not allowed 1."];
            }
        }
        
    } catch (PDOException $e) {
        $message = ["message" => "Error: " . $e->getMessage()];
    }
} else {
    http_response_code(405); // Method Not Allowed
    $message = ["message" => "Method not allowed."];
}
header('Content-Type: application/json');

echo json_encode($message);
?>
