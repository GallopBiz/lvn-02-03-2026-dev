<?php
// Disable error reporting (recommended for production)
ini_set('display_errors', 0);

// Database connection
$host = 'localhost';
$dbname = '2024_2025';
$username = 'lvnschool_software';
$password = 'GallopBiz13#!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Prepare the output CSV file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=feesreceiptchallan.csv');
$output = fopen('php://output', 'w');

// Write the CSV column headers
fputcsv($output, array(
    'id', 
    'student_id', 
    'student_dob',
    'recpt_chain',
    'due_upto',
    'name_student',
    'head_name', 
    'head_due_amount', 
    'head_rec_ammount', 
    'term_str', 
    'head_to_date', 
    'head_due_date',
    'name_father',
    'name_classsection',
    'name_admdt',
    'name_formno',
    'feestype',
    'total_dueamount',
    'sub_total_due',
    'sub_total_received',
    'grand_total_due',
    'is_delete',
    'updated_at',
    'created_at'
));

// Fetch data from the database
$query = "SELECT * FROM feesreceiptchallan";
$stmt = $pdo->prepare($query);
$stmt->execute();

// Process each row
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Decode JSON column
    $jsonData = json_decode($row['str_json'], true);

    // Split comma-separated values
    $head_names = explode(',', $jsonData['head_name']);
    $head_due_amounts = explode(',', $jsonData['head_due_amount']);
    $head_rec_amounts = explode(',', $jsonData['head_rec_ammount']);
    $term_strs = explode(',', $jsonData['term_str']);
    $head_to_dates = explode(',', $jsonData['head_to_date']);
    $head_due_dates = explode(',', $jsonData['head_due_date']);

    // Calculate the maximum number of elements
    $max_count = max(
        count($head_names), 
        count($head_due_amounts), 
        count($head_rec_amounts), 
        count($term_strs), 
        count($head_to_dates), 
        count($head_due_dates)
    );

    // Create rows for CSV
    for ($i = 0; $i < $max_count; $i++) {
        fputcsv($output, array(
            $row['id'],
            $row['student_id'],
            $row['student_dob'],
            $row['recpt_chain'],
            $row['due_upto'],
            $row['name_student'],
            $head_names[$i] ?? '',
            $head_due_amounts[$i] ?? '',
            $head_rec_amounts[$i] ?? '',
            $term_strs[$i] ?? '',
            $head_to_dates[$i] ?? '',
            $head_due_dates[$i] ?? '',
            $jsonData['name_father'] ?? '',
            $jsonData['name_classsection'] ?? '',
            $jsonData['name_admdt'] ?? '',
            $jsonData['name_formno'] ?? '',
            $jsonData['feestype'] ?? '',
            $jsonData['total_dueamount'] ?? '',
            $jsonData['sub_total_due'] ?? '',
            $jsonData['sub_total_received'] ?? '',
            $jsonData['grand_total_due'] ?? '',
            $row['is_delete'],
            $row['updated_at'],
            $row['created_at']
        ));
    }
}

// Close the output
fclose($output);
?>
