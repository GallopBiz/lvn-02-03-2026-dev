<?php 
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $start_session = $_POST['start_session'];
    $end_session = $_POST['end_session'];

    $str = $start_session . '_' . $end_session;

    $table_values = ['classes','states','cities','model_has_roles','roles','role_has_permissions','caste_name','inquiry_registration','student_registration'];

    $servername = "localhost"; // Change this to your database server hostname
    $username = "lvnschool_software";       // Change this to your MySQL username
    $password = "GallopBiz13#!";    // Change this to your MySQL password
    
    // Create a connection to the MySQL server
    $conn = new mysqli($servername, $username, $password);
    
    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // SQL query to create a new database named "$str"
    $createDatabaseSQL = "CREATE DATABASE $str";

    // Execute the query to create the new database
    if ($conn->query($createDatabaseSQL) === TRUE) {
        $existingDatabaseName = "2023_2024"; // Replace with the name of your existing database

        // SQL query to list tables in the existing database
        $listTablesSQL = "SHOW TABLES FROM $existingDatabaseName";

        $result = $conn->query($listTablesSQL);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $tableName = $row['Tables_in_' . $existingDatabaseName];
                // SQL query to create a new table with the same structure in the new database
                $createTableSQL = "CREATE TABLE $str.$tableName LIKE $existingDatabaseName.$tableName";

                if ($conn->query($createTableSQL) === TRUE) {
                    // echo "Table '$tableName' copied successfully.<br>";
                    $key = array_search($tableName,$table_values);
                    if ($key !== false ){
                        // SQL query to copy data from the existing table to the new table
                        $copyDataSQL = "INSERT INTO $str.$tableName SELECT * FROM $existingDatabaseName.$tableName";

                        if ($conn->query($copyDataSQL) === TRUE) {
                            // echo "Data copied successfully for table '$tableName'.<br>";
                            if ($tableName === 'inquiry_registration') {
                                $servername = "localhost"; // Change this to your database server hostname
                                $username = "lvnschool_software";        // Change this to your MySQL username
                                $password = "GallopBiz13#!";            // Change this to your MySQL password
                                $conn_d = new mysqli($servername, $username, $password , $str);
                                if ($conn_d->connect_error) {
                                    die("Connection failed: " . $conn_d->connect_error);
                                }
                                // Delete records that belonged to the previous session (not marked for next_year)
                                $conn_d->query("DELETE FROM `inquiry_registration` WHERE next_year != 1 OR next_year IS NULL");
                                // Convert next_year inquiries to current-year inquiries for the new session
                                $conn_d->query("UPDATE `inquiry_registration` SET next_year = 0, session_name = '$str' WHERE next_year = 1");
                                $conn_d->close();
                            } else {
                                // skip
                            }
                        } else {
                            echo "Error copying data for table '$tableName': " . $conn->error . "<br>";
                        }
                    }
                    
                } else {
                    echo "Error copying table '$tableName': " . $conn->error . "<br>";
                }
            }
        } else {
            echo "No tables found in the existing database.";
        }
        echo "Session created successfully";
    } else {
        echo "Error creating database: " . $conn->error;
    }
    
    // Close the database connection
    $conn->close();
?>
