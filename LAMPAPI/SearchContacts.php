<?php
header("Access-Control-Allow-Origin: http://portcall.cloud");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");


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
		// Get userId and search term
		$userId = intval($inData['userId'] ?? 0);
		$searchTerm = trim($inData['search'] ?? '');

		// Validate input
		if ($userId === 0)
		{
			returnWithError("Missing or invalid userId");
			$conn->close();
			exit;
		}

		if ($searchTerm === '')
		{
			// Return all contacts for this user
			$stmt = $conn->prepare("SELECT contactID, firstName, lastName, phone, email, userId 
									FROM Contacts 
									WHERE userId = ?");
			$stmt->bind_param("i", $userId);
			$stmt->execute();
			$result = $stmt->get_result();

			$resultsArray = [];
			while($row = $result->fetch_assoc()) 
			{
				$resultsArray[] = [
					"id" => $row["contactID"],
					"firstName" => $row["firstName"],
					"lastName" => $row["lastName"],
					"phone" => $row["phone"],
					"email" => $row["email"],
					"userId" => $row["userId"]
				];
			}

			if (count($resultsArray) === 0) 
			{
				returnWithInfo([]);
			} 
			else 
			{
				returnWithInfo($resultsArray);
			}

			$stmt->close();
			$conn->close();
			exit;
		}
		
		$stmt = $conn->prepare("SELECT contactID, firstName, lastName, phone, email, userId 
								FROM Contacts 
								WHERE userId = ? 
								AND (LOWER(firstName) LIKE LOWER(?) 
									OR LOWER(lastName) LIKE LOWER(?) 
									OR phone LIKE ? 
									OR email LIKE ?)");

		$searchPattern = "%" . $searchTerm . "%";
		
		$stmt->bind_param("issss", $userId, $searchPattern, $searchPattern, $searchPattern, $searchPattern);
		$stmt->execute();
		
		$result = $stmt->get_result();

		$resultsArray = [];

		while($row = $result->fetch_assoc()) 
		{
    		$resultsArray[] = [
				"id" => $row["contactID"],
				"firstName" => $row["firstName"],
				"lastName" => $row["lastName"],
				"phone" => $row["phone"],
				"email" => $row["email"],
				"userId" => $row["userId"]
			];
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
