<?php
// Connect to the SQLite database
$db = new SQLite3('PVP_PVE.db');

// Check if the database connection is successful
if (!$db) {
    $errorResponse = array('error' => 'Database connection failed.');
    echo json_encode($errorResponse);
    exit;
}

// Initialize an empty array to store the data
$data = array();

// Initialize an empty error array
$errors = array();

// Check if a 'type' parameter is provided in the URL
if (isset($_GET['type'])) {
    $type = $_GET['type'];

    // Build the base SQL query based on the specified 'type'
    if ($type === 'PVP') {
        $baseQuery = "SELECT * FROM PVP WHERE 1=1";
    } elseif ($type === 'PVE') {
        $baseQuery = "SELECT * FROM PVE WHERE 1=1";
    } else {
        $errors[] = "Invalid 'type' parameter.";
    }

    // Initialize an array to store filter conditions
    $conditions = array();

    // Check if 'Killer' parameter is provided and not empty
    if (isset($_GET['Killer']) && !empty($_GET['Killer'])) {
        $Killer = $_GET['Killer'];
        $conditions[] = "Killer = :Killer";
    }

    // Check if 'Victim' parameter is provided and not empty
    if (isset($_GET['Victim']) && !empty($_GET['Victim'])) {
        $Victim = $_GET['Victim'];
        $conditions[] = "Victim = :Victim";
    }

    // Check if 'limit' parameter is provided and not empty
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100000; // Default limit is 10

    // Combine the base query, filter conditions, and limit
    $query = $baseQuery;
    if (!empty($conditions)) {
        $query .= " AND " . implode(" AND ", $conditions);
    }
    
    // Add the 'LIMIT' clause to the query
    $query .= " LIMIT " . $limit;

    // Prepare the SQL query
    $stmt = $db->prepare($query);

    // Bind parameters to the prepared statement
    if (isset($Killer)) {
        $stmt->bindValue(':Killer', $Killer, SQLITE3_TEXT);
    }

    if (isset($Victim)) {
        $stmt->bindValue(':Victim', $Victim, SQLITE3_TEXT);
    }

    // Execute the prepared statement
    $result = $stmt->execute();

    // Fetch data from the result set and add it to the array as GeoJSON features
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
		
// Initialize 'name' variable
$name = '';

// Check if 'Killer' parameter is provided and not empty
if (isset($_GET['Killer']) && !empty($_GET['Killer'])) {
    $name = $_GET['Killer'];
} elseif (isset($_GET['Victim']) && !empty($_GET['Victim'])) {
    $name = $_GET['Victim'];
}

         if ($type == 'PVP'){
		    $feature = array(
            'type' => 'Feature',
            'properties' => array(
                'name' => $name ,
                'Killer' => $row['Killer'],
                'Victim' => $row['Victim'],
                'WeaponType' => $row['WeaponType'],
				'Distance' => $row['Distance'],
				'HitLocation' => $row['HitLocation'],
				'HitDamage' => $row['HitDamage'],
				'POS_X' => $row['POS_X'],
				'POS_Y' => $row['POS_Y'],
				'TimeAlive' => $row['Hours'] . 'H ' . $row['Minutes'] . 'M ' . $row['Seconds'] . 'S',	
                'Timestamp' => $row['Timestamp'],
                'InGameTime' => $row['InGameTime']
            ),
            'geometry' => array(
                'type' => 'Point',
                'coordinates' => array(floatval($row['POS_X']), floatval($row['POS_Y'])),
            ),
        );
	   }elseif ($type == 'PVE'){
		    $feature = array(
            'type' => 'Feature',
            'properties' => array(
                'name' => $name ,
                'Victim' => $row['Victim'],
				'POS_X' => $row['POS_X'],
				'POS_Y' => $row['POS_Y'],
				'TimeAlive' => $row['Hours'] . 'H ' . $row['Minutes'] . 'M ' . $row['Seconds'] . 'S',
                'Timestamp' => $row['Timestamp'],
                'InGameTime' => $row['InGameTime']
            ),
            'geometry' => array(
                'type' => 'Point',
                'coordinates' => array(floatval($row['POS_X']), floatval($row['POS_Y'])),
            ),
        );

	   }

        // Add the feature to the data array
        $data[] = $feature;
    }
} else {
    $errors[] = "Missing 'type' parameter.";
}

// Close the database connection
$db->close();

// Check for errors again before outputting the data
if (!empty($errors)) {
    $errorResponse = array('errors' => $errors);
    echo json_encode($errorResponse);
    exit;
}

// Create a GeoJSON feature collection
$featureCollection = array(
    'type' => 'FeatureCollection',
    'features' => $data,
);

// Set the response content type to JSON
header('Content-Type: application/json');

// Output the data as JSON
echo json_encode($featureCollection);
?>
