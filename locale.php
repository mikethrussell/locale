#!/usr/bin/php

<?php
//notes to self - need some way (text file?) of registering 'home' or 'away' to know if to run the script or not. away for how long?/.. hmmm 


//credit: http://www.nmcmahon.co.uk/getting-the-distance-between-two-locations-using-google-maps-api-and-php


//enter your user ID. Obtain from: https://latitude.google.com/latitude/b/0/apps

$mike_lat = '7649347323393564615';
$caroline_lat = '7405538217839576382';

//enter home address eg. 'LS14 6HG' or '24 Green Tree Avenue, York' etc.

$home = 'HG3 2DS';

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


//setup sqlite db

date_default_timezone_set('GMT');

try {
    /**************************************
    * Create databases and                *
    * open connections                    *
    **************************************/
 
    // Create (connect to) SQLite database in file
    $file_db = new PDO('sqlite:states');
    // Set errormode to exceptions
    $file_db->setAttribute(PDO::ATTR_ERRMODE, 
                            PDO::ERRMODE_EXCEPTION);
 
    /**************************************
    * Create tables                       *
    **************************************/
 
    // Create table messages
    $file_db->exec("CREATE TABLE IF NOT EXISTS states (
                    id INTEGER PRIMARY KEY, 
                    user TEXT, 
                    state TEXT, 
                    time DATETIME)");
 
 
    /**************************************
    * Set sqlite data                    *
    **************************************/
 
    // Array with some test data to insert to database             
    $states = array(
                  array('user' => 'Hello!',
                        'state' => 'Just testing...',
                        'time' => 1327301464),
                  array('user' => 'Hello again!',
                        'state' => 'More testing...',
                        'time' => 1339428612),
                  array('user' => 'Hi!',
                        'state' => 'SQLite3 is cool...',
                        'time' => 1327214268)
                );
 

 


 
 


//convert everything to lon lat
$mike_loc = getLatitude($mike_lat);
$caroline_loc = getLatitude($caroline_lat);
$home_loc = getLatLong($home);


//STATES = 0 = 'home', 1 = 'away', 2 = 'holiday'

//set caroline state

//get $c_prev_state

$distance = Haversine($caroline_loc, $home_loc);

if ($distance <= 0.5) {$c_state = 1;}
//arriving
elseif ($distance >0.5 && $distance <= 100) {$c_state = 2;}
else {$c_state = 3;}


//set mike state

//get $m_prev_state

$distance = Haversine($mike_loc, $home_loc);

if ($distance <= 0.5) {$m_state = 1;}
//arriving
elseif ($distance >0.5 && $distance <= 100) {$m_state = 2;}
else {$m_state = 3;}


//get house state


if ($c_state == 1 || $m_state == 1 && $house_state != 'home') {

//do home functions

$ch = curl_init("http://192.168.1.42:8080/json.htm?type=command&param=switchscene&idx=1&switchcmd=Off");
curl_exec($ch);
$ch = curl_init("http://192.168.1.42:8080/json.htm?type=command&param=switchscene&idx=1&switchcmd=On");
curl_exec($ch);

curl_close($ch);

//set house state to 'home'

}


if ($m_state == 'arriving' && $m_prev_state != 'arriving') {
// do mike arriving functions

}

if ($c_state == 'arriving' && $c_prev_state != 'arriving') {
// do mike arriving functions

}


if ($m_state == 3 && $c_state == 3) {

//do holiday functions
//set house state to 'holiday'

}

elseif ($m_state >=2 && $c_state >=2 && $house_state != 'away'){

//do away functions
//set house state to 'away'

}





    /**************************************
    * Play with databases and tables      *
    **************************************/
 
    // Prepare INSERT statement to SQLite3 file db
    $insert = "INSERT INTO states (user, state, time) 
                VALUES (:user, :state, :time)";
    $stmt = $file_db->prepare($insert);
 
    // Bind parameters to statement variables
    $stmt->bindParam(':user', $user);
    $stmt->bindParam(':state', $state);
    $stmt->bindParam(':time', $time);
 
    // Loop thru all messages and execute prepared insert statement
    foreach ($states as $s) {
      // Set values to bound variables
      $user = $s['user'];
      $state = $s['state'];
      $time = $s['time'];
 
      // Execute statement
      $stmt->execute();
    }

    /**************************************
    * Close db connections                *
    **************************************/
 
    // Close file db connection
    $file_db = null;

  }
  catch(PDOException $e) {
    // Print PDOException message
    echo $e->getMessage();
  }



?>


