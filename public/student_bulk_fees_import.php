<?php

// Database connection
$host = 'localhost';
$dbname = '2024_2025';
$username = 'lvnschool_software';
$password = 'GallopBiz13#!';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Read CSV file
$csvFile = 'student_fee_data.csv';
$handle = fopen($csvFile, 'r');

// Skip the first line if it contains headers
fgetcsv($handle);

// Prepare SQL statements
$feesStructureStmt = $pdo->prepare("SELECT json_str FROM course_fees_structure_master WHERE class_name = ? ");
$feeBalanceStmt = $pdo->prepare("SELECT balance, term FROM student_fee_balances WHERE scholar_no = ? AND fee_type = ?");
$insertFeeBalanceStmt = $pdo->prepare("INSERT INTO student_fee_balances (scholar_no, fee_type, balance, term) VALUES (?, ?, ?, ?)");
$updateFeeBalanceStmt = $pdo->prepare("UPDATE student_fee_balances SET balance = ?, term = ? WHERE scholar_no = ? AND fee_type = ?");

$busBalanceStmt = $pdo->prepare("SELECT balance FROM student_bus_fees_balance WHERE scholar_no = ?");
$insertBusBalanceStmt = $pdo->prepare("INSERT INTO student_bus_fees_balance (scholar_no, balance) VALUES (?, ?)");
$updateBusBalanceStmt = $pdo->prepare("UPDATE student_bus_fees_balance SET balance = ? WHERE scholar_no = ?");

// Function to get the term from json_str based on account name and amount paid
function getTermAndUpdateBalance(&$terms, $accountName, $amountPaid) {
    foreach ($terms as $term => $balance) {
        if ($balance > 0) {
            if ($amountPaid >= $balance) {
                $amountPaid -= $balance;
                $terms[$term] = 0; // Mark this term as fully paid
            } else {
                $terms[$term] -= $amountPaid; // Deduct from the balance
                $amountPaid = 0;
                break;
            }
        }
    }
    return $amountPaid;
}

// Process each row of the CSV
while (($data = fgetcsv($handle)) !== FALSE) {
    list($feeRecNo, $studentName, $scholarNo, $class, $section, $accountName, $totalAmount, $postedNoDt, $term) = $data;

    // Check the account name and determine the term
    if (in_array($accountName, ["ADMISSION FEES", "ALUMNI FEES", "CAUTION MONEY"])) {
        $term = "1st";
    } elseif (in_array($accountName, ["TUITION FEES", "LUNCH FEES"])) {
		if(strlen($class)==1){$class="0".$class;}
		
        // Fetch fees structure
        $feesStructureStmt->execute([$class]); // Assuming session_name is "2024-2025"
        $feesStructure = json_decode($feesStructureStmt->fetchColumn(), true);

        // Initialize terms balance array
        $terms = array_combine($feesStructure['term'], $feesStructure['fees']);
        $remainingAmount = getTermAndUpdateBalance($terms, $accountName, $totalAmount);

        // Fetch current balance for the student and fee type
        $feeBalanceStmt->execute([$scholarNo, $accountName]);
        $feeBalance = $feeBalanceStmt->fetch(PDO::FETCH_ASSOC);

        if ($feeBalance) {
            // Update existing balance
            $newBalance = $feeBalance['balance'] - $remainingAmount;
            $newTerm = $feeBalance['term'];
            if ($remainingAmount > 0) {
                // Move to the next term if there is remaining amount
                $nextTermIndex = array_search($feeBalance['term'], array_keys($terms)) + 1;
                $newTerm = array_keys($terms)[$nextTermIndex];
            }
            $updateFeeBalanceStmt->execute([$newBalance, $newTerm, $scholarNo, $accountName]);
        } else {
            // Insert a new balance record
            $insertFeeBalanceStmt->execute([$scholarNo, $accountName, $remainingAmount, $term]);
        }
    } elseif ($accountName === "BUS FEES") {
        // Fetch bus fees balance for the student
        $busBalanceStmt->execute([$scholarNo]);
        $busBalance = $busBalanceStmt->fetchColumn();

        if ($busBalance === false) {
            // Insert a new balance record if not found
            $busBalance = $totalAmount;
            $insertBusBalanceStmt->execute([$scholarNo, $busBalance]);
        } else {
            // Update the balance
            $newBusBalance = $busBalance - $totalAmount;
            $updateBusBalanceStmt->execute([$newBusBalance, $scholarNo]);
        }
        $term = ""; // No term for BUS FEES
    }

    // Insert the data into the student_fees_data table
    $insertStmt = $pdo->prepare("INSERT INTO student_fees_data (FeeRecNo, StudentName, ScholarNo, Class, Section, AccountName, TotalAmount, PostedNoDt, Terms) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insertStmt->execute([$feeRecNo, $studentName, $scholarNo, $class, $section, $accountName, $totalAmount, $postedNoDt, $term]);
}

fclose($handle);
echo "CSV data processed and inserted into student_fees_data table successfully.";

?>
