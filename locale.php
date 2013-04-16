#!/usr/bin/php

<?php

//credit: http://www.nmcmahon.co.uk/getting-the-distance-between-two-locations-using-google-maps-api-and-php


//enter your user ID. Obtain from: https://latitude.google.com/latitude/b/0/apps

$mike_lat = 'XXXXXXXXXXXXXXXXXXXX';
$caroline_lat = 'XXXXXXXXXXXXXXXXXXXX';

//enter home address eg. 'LS14 6HG' or '24 Green Tree Avenue, York' etc.

$home = 'PO5T C0D3';


//STATES: 0 = 'home', 1 = 'arriving', 2 = 'away', 3 = 'holiday'



//FUNCTIONS

//function to use human readable addresses

function getLatLong($address) {
 
    $address = str_replace(' ', '+', $address);
    $url = 'http://maps.googleapis.com/maps/api/geocode/json?address='.$address.'&sensor=false';
 
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $geoloc = curl_exec($ch);
 
    $json = json_decode($geoloc);
    return array($json->results[0]->geometry->location->lat, $json->results[0]->geometry->location->lng);
 
}

//function to calculate distance between 2 points

function Haversine($start, $finish) {
 
    $theta = $start[1] - $finish[1]; 
    $distance = (sin(deg2rad($start[0])) * sin(deg2rad($finish[0]))) + (cos(deg2rad($start[0])) * cos(deg2rad($finish[0])) * cos(deg2rad($theta))); 
    $distance = acos($distance); 
    $distance = rad2deg($distance); 
    $distance = $distance * 60 * 1.1515; 
 
    return round($distance, 2);
 
}


function getLatitude($userId) {
$url = 'http://www.google.com/latitude/apps/badge/api?user='.$userId.'&type=json';
 
// We get the content
$content = file_get_contents( $url );
 
// We convert the JSON to an object
$json = json_decode( $content );
 
$coord = $json->features[0]->geometry->coordinates;
$timeStamp = $json->features[0]->properties->timeStamp;

$lat = $coord[1];
$lon = $coord[0];

return array($lat,$lon);


}

//FUNCTIONS END


//setting things up
date_default_timezone_set('GMT');
$timestamp = time();
$states = array();

//SETUP SQLITE DB

date_default_timezone_set('GMT');

try {
    
    // Create or connect to SQLite database in file
    $file_db = new PDO('sqlite:states');
    // Set errormode to exceptions
    $file_db->setAttribute(PDO::ATTR_ERRMODE, 
                            PDO::ERRMODE_EXCEPTION);
 

//Create states table if not exists
$file_db->exec("CREATE TABLE IF NOT EXISTS states (
                    id INTEGER PRIMARY KEY, 
                    user VARCHAR UNIQUE, 
                    state VARCHAR, 
                    time DATETIME)");
                    
//set inital data (if none)
$initial_states = array (
array('user' => 'mike',
                        'state' => 0,
                        'time' => $timestamp),
array('user' => 'caroline',
                        'state' => 0,
                        'time' => $timestamp),
array('user' => 'home',
                        'state' => 0,
                        'time' => $timestamp)
);     

 	$insert = "INSERT OR IGNORE INTO states (user, state, time) 
                VALUES (:user, :state, :time)";
                
	$stmt = $file_db->prepare($insert);

// Bind parameters to statement variables
    $stmt->bindParam(':user', $user);
    $stmt->bindParam(':state', $state);
    $stmt->bindParam(':time', $timestamp);
 
    // Loop thru all messages and execute prepared insert statement
    foreach ($initial_states as $is) {
      // Set values to bound variables
      $user = $is['user'];
      $state = $is['state'];
      $timestamp = $is['time'];
 
      // Execute statement
      $stmt->execute();
    }

//END SETUP SQLITE DB


//convert everything to lon lat
$mike_loc = getLatitude($mike_lat);
$caroline_loc = getLatitude($caroline_lat);
$home_loc = getLatLong($home);


//get $c_prev_state

$stmt = $file_db->query('SELECT state FROM states WHERE user IS "caroline"');
$c_prev_state = $stmt->fetchColumn(0);

//get $m_prev_state

$stmt = $file_db->query('SELECT state FROM states WHERE user IS "mike"');
$m_prev_state = $stmt->fetchColumn(0);

//get $house_state

$stmt = $file_db->query('SELECT state FROM states WHERE user IS "home"');
$house_state = $stmt->fetchColumn(0);



//set caroline state

$distance = Haversine($caroline_loc, $home_loc);

if ($distance <= 0.5) {$c_state = 0;}
elseif ($distance >0.5 && $distance <= && $c_prev_state != 0) {$c_state = 1;}
elseif ($distance >0.5 && $distance <= 100) {$c_state = 2;}
else {$c_state = 3;}

$state =  array('user' => 'caroline',
                        'state' => $c_state,
                        'time' => $timestamp );
array_push($states, $state); 



//set mike state

$distance = Haversine($mike_loc, $home_loc);

if ($distance <= 0.5) {$m_state = 0;}
elseif ($distance >0.5 && $distance <= && $m_prev_state != 0) {$m_state = 1;}
elseif ($distance >0.5 && $distance <= 100) {$m_state = 2;}
else {$m_state = 3;}

$state =  array('user' => 'mike',
                        'state' => $m_state,
                        'time' => $timestamp );
array_push($states, $state); 



if ($c_state == 0 || $m_state == 0 && $house_state != 0) {

//do home functions

// ***INSERT HOME STATE FUNCTIONS HERE***

/* e.g. (for cURL code)

$ch = curl_init("http://192.168.1.42:8080/json.htm?type=command&param=switchscene&idx=1&switchcmd=Off");
curl_exec($ch);
curl_close($ch);

*/

//set house state to 'home'
$state =  array('user' => 'home',
                        'state' => 0,
                        'time' => $timestamp );
                        
array_push($states, $state); 

}


if ($m_state == 1 && $m_prev_state != 1) {
// do mike arriving functions

// ***INSERT MIKE ARRIVING FUNCTIONS HERE***

}

if ($c_state == 1 && $c_prev_state != 1) {
// do caroline arriving functions

// ***INSERT CAROLINE ARRIVING FUNCTIONS HERE***


}


if ($m_state == 3 && $c_state == 3) {

//do holiday functions

// ***INSERT HOLIDAY STATE FUNCTIONS HERE***

//set house state to 'holiday'
$state =  array('user' => 'home',
                        'state' => 3,
                        'time' => $timestamp );
                        
array_push($states, $state); 

}

elseif ($m_state >=2 && $c_state >=2 && $house_state != 2){

//do away functions

// ***INSERT AWAY STATE FUNCTIONS HERE***

//set house state to 'away'
$state =  array('user' => 'home',
                        'state' => 2,
                        'time' => $timestamp );
                        
array_push($states, $state); 

}


//UPDATE DB
 
    // Prepare INSERT statement to SQLite3 file db
    $insert = "REPLACE INTO states (user, state, time) 
                VALUES (:user, :state, :time)";
    $stmt = $file_db->prepare($insert);
 
    // Bind parameters to statement variables
    $stmt->bindParam(':user', $user);
    $stmt->bindParam(':state', $state);
    $stmt->bindParam(':time', $timestamp);
 
    // Loop thru all messages and execute prepared insert statement
    foreach ($states as $s) {
      // Set values to bound variables
      $user = $s['user'];
      $state = $s['state'];
      $timestamp = $s['time'];
 
      // Execute statement
      $stmt->execute();
    }



/*

//print states for debug
$sql = 'SELECT * FROM states';
    foreach ($file_db->query($sql) as $row) {
        print $row['user'] . "\t";
        print $row['state'] . "\t";
        print $row['time'] . "\n";
    }


echo 'm prev state = '.$m_prev_state. "\n";
echo 'c prev state = '.$c_prev_state;

*/

 
    // Close file db connection
    $file_db = null;

  }
  catch(PDOException $e) {
    // Print PDOException message
    echo $e->getMessage();
  }



?>


