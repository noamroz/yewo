<?php
/*
	API Demo

	This script provides a RESTful API interface for a web application

	Input:

		$_GET['format'] = [ json | html | xml ]
		$_GET['method'] = []

	Output: A formatted HTTP response

	Author: Mark Roland

	History:
		11/13/2012 - Created

*/

// --- Step 1: Initialize variables and functions

/**
 * Deliver HTTP Response
 * @param string $format The desired HTTP response content type: [json, html, xml]
 * @param string $api_response The desired HTTP response data
 * @return void
 **/
function deliver_response($format, $api_response){

	// Define HTTP responses
	$http_response_code = array(
		200 => 'OK',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		403 => 'Forbidden',
		404 => 'Not Found'
	);

	// Set HTTP Response
	header('HTTP/1.1 '.$api_response['status'].' '.$http_response_code[ $api_response['status'] ]);

	// Process different content types
	if( strcasecmp($format,'json') == 0 ){

		// Set HTTP Response Content Type
		header('Content-Type: application/json; charset=utf-8');

		// Format data into a JSON response
		$json_response = json_encode($api_response);

		// Deliver formatted data
		echo $json_response;

	}elseif( strcasecmp($format,'xml') == 0 ){

		// Set HTTP Response Content Type
		header('Content-Type: application/xml; charset=utf-8');

		// Format data into an XML response (This is only good at handling string data, not arrays)
		$xml_response = '<?xml version="1.0" encoding="UTF-8"?>'."\n".
			'<response>'."\n".
			"\t".'<code>'.$api_response['code'].'</code>'."\n".
			"\t".'<data>'.$api_response['data'].'</data>'."\n".
			'</response>';

		// Deliver formatted data
		echo $xml_response;

	}else{

		// Set HTTP Response Content Type (This is only good at handling string data, not arrays)
		header('Content-Type: text/html; charset=utf-8');

		// Deliver formatted data
		echo $api_response['data'];

	}

	// End script process
	exit;

}

// Define whether an HTTPS connection is required
$HTTPS_required = FALSE;

// Define whether user authentication is required
$authentication_required = FALSE;

// Define API response codes and their related HTTP response
$api_response_code = array(
	0 => array('HTTP Response' => 400, 'Message' => 'Unknown Error'),
	1 => array('HTTP Response' => 200, 'Message' => 'Success'),
	2 => array('HTTP Response' => 403, 'Message' => 'HTTPS Required'),
	3 => array('HTTP Response' => 401, 'Message' => 'Authentication Required'),
	4 => array('HTTP Response' => 401, 'Message' => 'Authentication Failed'),
	5 => array('HTTP Response' => 404, 'Message' => 'Invalid Request'),
	6 => array('HTTP Response' => 400, 'Message' => 'Invalid Response Format')
);

// Set default HTTP response of 'ok'
$response['code'] = 0;
$response['status'] = 404;
$response['data'] = NULL;

// --- Step 2: Authorization

// Optionally require connections to be made via HTTPS
if( $HTTPS_required && $_SERVER['HTTPS'] != 'on' ){
	$response['code'] = 2;
	$response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
	$response['data'] = $api_response_code[ $response['code'] ]['Message'];

	// Return Response to browser. This will exit the script.
	deliver_response($_GET['format'], $response);
}

// Optionally require user authentication
if( $authentication_required ){

	if( empty($_POST['username']) || empty($_POST['password']) ){
		$response['code'] = 3;
		$response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
		$response['data'] = $api_response_code[ $response['code'] ]['Message'];

		// Return Response to browser
		deliver_response($_GET['format'], $response);

	}

	// Return an error response if user fails authentication. This is a very simplistic example
	// that should be modified for security in a production environment
	elseif( $_POST['username'] != 'foo' && $_POST['password'] != 'bar' ){
		$response['code'] = 4;
		$response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
		$response['data'] = $api_response_code[ $response['code'] ]['Message'];

		// Return Response to browser
		deliver_response($_GET['format'], $response);

	}

}

// --- Step 3: Process Request

// Method A: Say Hello to the API
if( strcasecmp($_GET['method'],'hello') == 0){
	$response['code'] = 1;
	$response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
	$response['data'] = 'Hello World';
} else if ( strcasecmp($_GET['method'],'events') == 0) {
	$response['code'] = 1;
	$response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
	
	$server = "us-cdbr-iron-east-03.cleardb.net";
  	$username = "b89bd9df8706e4";
  	$password = "9e505464";
  	$db = "heroku_372223b9430a291";
  	$conn = mysql_connect($server, $username, $password);
  	mysql_select_db($db);

  	$sql = "";
	if($_GET['action'] == 1) { 
  		$sql = "SELECT id,event_name,url,date,location FROM events JOIN companies_events ON events.id = companies_events.company_id WHERE company_id=".$_GET['id'];
  	}
  	else if ($_GET['action'] == 2) {
		$sql = "SELECT id,event_name,url,date,location FROM events JOIN freelancers_events ON events.id = freelancers_events.freelancer_id WHERE freelancer_id=".$_GET['id'];
  	} 

	$retval = mysql_query( $sql, $conn );
   
   	if(! $retval ) {
      die('Could not enter data: ' . mysql_error());
   	}
 
 	//create an array
    $output = array();
    $counter = 0;
    while($row =mysql_fetch_array($retval))
    {
        $output[$counter] = $row;
        $counter++;
    }

	mysql_close($conn);

	//die('Could not enter data: ' . $retval);
	$response['data'] = json_encode($output);
} else if ( strcasecmp($_GET['method'],'users') == 0) {
	$response['code'] = 1;
	$response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
	
	$server = "us-cdbr-iron-east-03.cleardb.net";
  	$username = "b89bd9df8706e4";
  	$password = "9e505464";
  	$db = "heroku_372223b9430a291";
  	$conn = mysql_connect($server, $username, $password);
  	mysql_select_db($db);

  	$sql = "";
	if($_GET['action'] == 1) { 
  		$sql = "SELECT id,email,company_name,website,signup_date FROM companies JOIN companies_events ON companies.id = companies_events.company_id WHERE companies_events.event_id=".$_GET['id'];
  	}
  	else if ($_GET['action'] == 2) {
		$sql = "SELECT id,email,full_name,linkedin,signup_date FROM freelancers JOIN freelancers_events ON freelancers.id = freelancers_events.freelancer_id WHERE freelancers_events.event_id=".$_GET['id'];
  	} 

	$retval = mysql_query( $sql, $conn );
   
   	if(! $retval ) {
      die('Could not enter data: ' . mysql_error());
   	}
 
 	//create an array
    $output = array();
    $counter = 0;
    while($row =mysql_fetch_array($retval))
    {
        $output[$counter] = $row;
        $counter++;
    }

	mysql_close($conn);

	//die('Could not enter data: ' . $retval);
	$response['data'] = json_encode($output);
} else if ( strcasecmp($_GET['method'],'login') == 0) {
	$response['code'] = 1;
	$response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
	
	$server = "us-cdbr-iron-east-03.cleardb.net";
  	$username = "b89bd9df8706e4";
  	$password = "9e505464";
  	$db = "heroku_372223b9430a291";
  	$conn = mysql_connect($server, $username, $password);
  	mysql_select_db($db);

  	$sql = "";
	if($_GET['action'] == 1) { 
  		$sql = "SELECT id,email,company_name,website,signup_date FROM companies WHERE email='".$_POST['email']."' AND password='".$_POST['password']."'";
  	}
  	else if ($_GET['action'] == 2) {
		$sql = "SELECT id,email,full_name,linkedin,signup_date FROM freelancers WHERE email='".$_POST['email']."' AND password='".$_POST['password']."'";
  	} 

	$retval = mysql_query( $sql, $conn );
   
   	if(! $retval ) {
      die('Could not enter data: ' . mysql_error());
   	}
 
 	//create an array
    $output = array();
    $counter = 0;
    while($row =mysql_fetch_array($retval))
    {
        $output[$counter] = $row;
        $counter++;
    }

	mysql_close($conn);

	//die('Could not enter data: ' . $retval);
	$response['data'] = json_encode($output);
} else if ( strcasecmp($_GET['method'],'signup') == 0) {
	$response['code'] = 1;
	$response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
	
	$server = "us-cdbr-iron-east-03.cleardb.net";
  	$username = "b89bd9df8706e4";
  	$password = "9e505464";
  	$db = "heroku_372223b9430a291";
  	$conn = mysql_connect($server, $username, $password);
  	mysql_select_db($db);

  	$sql = "";
	if($_GET['action'] == 1) { 
  		$sql = "INSERT INTO companies (email,company_name,website,password) VALUES ('".$_POST['email']."','".$_POST['company_name']."','".$_POST['website']."''".$_POST['password']."')";
  	}
  	else if ($_GET['action'] == 2) {
		  $sql = "INSERT INTO freelancers (email,full_name,linkedin,password) VALUES ('".$_POST['email']."','".$_POST['full_name']."','".$_POST['linkedin']."''".$_POST['password']."')";
  	} 

	$retval = mysql_query( $sql, $conn );
    
   	if(is_null($retval)) {
      //die('Could not enter data: ' . mysql_error());
   		$response['data'] = "error, email already exists. ".$retval."-".mysql_error();   		
   	} else {
   		$response['data'] = "output - ".$retval;
   	}

	mysql_close($conn);
} else if ( strcasecmp($_GET['method'],'attending') == 0) {

}

// --- Step 4: Deliver Response

// Return Response to browser
deliver_response($_GET['format'], $response);

?>