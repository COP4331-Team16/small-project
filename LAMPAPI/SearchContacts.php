<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

	$inData = getRequestInfo();
	
	$searchResults = "";
	$searchCount = 0;

	$conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
		$stmt = $conn->prepare("SELECT * from Contacts 
								WHERE LOWER(firstName) LIKE LOWER(?) 
								OR LOWER(lastName) LIKE LOWER(?) 
								OR phone LIKE ? 
								OR email LIKE ?");


		$searchTerm = "%" . $inData["search"] . "%";
		
		$stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
		$stmt->execute();
		
		$result = $stmt->get_result();
		
		$resultsArray = [];

		while($row = $result->fetch_assoc()) 
		{
    		$resultsArray[] = [ "firstName" => $row["firstName"],
        					    "lastName" => $row["lastName"],
        					    "phone" => $row["phone"],
        						"email" => $row["email"],
								"userId" => $row["userId"] ];
		}

		if (count($resultsArray) === 0) 
		{
    		returnWithError("No Records Found");
		} 
		else 
		{
    		returnWithInfo($resultsArray);
		}
		
		$stmt->close();
		$conn->close();
	}

	function getRequestInfo()
	{
		return json_decode(file_get_contents('php://input'), true);
	}

	function sendResultInfoAsJson( $obj )
	{
		header('Content-type: application/json');
		echo $obj;
	}
	
	function returnWithError( $err )
	{
		$retValue = json_encode(["results" => [], "error" => $err]);
		sendResultInfoAsJson( $retValue );
	}
	
	function returnWithInfo($resultsArray)
	{
    	$retValue = json_encode(["results" => $resultsArray, "error" => ""]);
    	sendResultInfoAsJson($retValue);
	}
	
?>
