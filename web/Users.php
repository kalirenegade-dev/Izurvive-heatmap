<?php
// Connect to the SQLite database
$db = new SQLite3('PVP_PVE.db');

// Check if the database connection is successful
if (!$db) {
    die("Database connection failed.");
}

// Use a prepared statement to prevent SQL injection
$id = $_GET['term'];
$query = "SELECT DISTINCT Person FROM (
              SELECT Victim as Person FROM PVE WHERE Person LIKE :id
              UNION 
              SELECT Killer as Person FROM PVP WHERE Person LIKE :id
          )
          LIMIT 10;";
$stmt = $db->prepare($query);
$stmt->bindValue(':id', '%' . $id . '%', SQLITE3_TEXT);
$result = $stmt->execute();

// Define an array to store the distinct names
$uniqueNames = [];

// Fetch and process the data from the database
if ($result) {
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        // Add each distinct name to the $uniqueNames array
        $uniqueNames[] = $row['Person'];
    }
} else {
    echo 'No data found.';
}

// Close the database connection
$db->close();

// Encode the $uniqueNames array as JSON and return it
header('Content-Type: application/json');
echo json_encode($uniqueNames);
?>