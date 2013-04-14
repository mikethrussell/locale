#!/usr/bin/php

<?php
//notes to self - need some way (text file?) of registering 'home' or 'away' to know if to run the script or not. away for how long?/.. hmmm 


//credit: http://www.nmcmahon.co.uk/getting-the-distance-between-two-locations-using-google-maps-api-and-php


//enter your user ID. Obtain from: https://latitude.google.com/latitude/b/0/apps

$userId = '7649347323393564615';

//enter Domoticz home address eg. 'LS14 6HG' or '24 Green Tree Avenue, York' etc.

$home = 'HG3 2DS';


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


 
$url = 'http://www.google.com/latitude/apps/badge/api?user='.$userId.'&type=json';
 
// We get the content
$content = file_get_contents( $url );
 
// We convert the JSON to an object
$json = json_decode( $content );
 
$coord = $json->features[0]->geometry->coordinates;
$timeStamp = $json->features[0]->properties->timeStamp;
 
if ( ! $coord ) 
	exit('This user doesn\'t exist.');
 

$lat = $coord[1];
$lon = $coord[0];

$start = array($lat,$lon);
$finish = getLatLong($home);
 
$distance = Haversine($start, $finish);


if ($distance<5) {

$ch = curl_init("http://192.168.1.42:8080/json.htm?type=command&param=switchscene&idx=1&switchcmd=On");
curl_exec($ch);
$ch = curl_init("http://192.168.1.42:8080/json.htm?type=command&param=switchscene&idx=1&switchcmd=Off");
curl_exec($ch);
$ch = curl_init("http://192.168.1.42:8080/json.htm?type=command&param=switchscene&idx=1&switchcmd=On");
curl_exec($ch);
$ch = curl_init("http://192.168.1.42:8080/json.htm?type=command&param=switchscene&idx=1&switchcmd=Off");
curl_exec($ch);


curl_close($ch);


}


?>


