<?php
// Database connection parameters
$hostname = 'localhost';
$username = 'gallop_school_erp';
$password = 'GallopBiz13#!';
$database = '2023_2024';
$message = [];
if (isset($_POST['latitude'])) {
    try {
        // Create a PDO database connection
        $pdo = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // $json_data = file_get_contents('php://input');
        // $datas = json_decode($json_data, true);
        // print_r($datas);
        // foreach($datas as $data){
            // Process the GET request
            // if (isset($_POST['latitude'])) {
                $sql = "INSERT INTO take_lang_lot (latitude, longitude, location_name) VALUES (:latitude, :longitude, :location_name)";
                $stmt = $pdo->prepare($sql);

                $stmt->bindParam(':latitude', $_POST['latitude']);
                $stmt->bindParam(':longitude', $_POST['longitude']);
                $stmt->bindParam(':location_name', $_POST['location_name']);
                $stmt->execute();
                $message = ["message" => "Data inserted successfully."];
            // } else {
                // $message = ["message" => "Method not allowed 1."];
            // }
        // }
        
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
