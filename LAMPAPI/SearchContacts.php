<?php

	$inData = getRequestInfo();
	
	$searchResults = "";
	$searchCount = 0;

	$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");
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
			returnWithError("Missing search term");
			$conn->close();
			exit;
		}
		
		$stmt = $conn->prepare("SELECT ID, FirstName, LastName, Phone, Email, UserID 
								FROM Contacts 
								WHERE UserID = ? 
								AND (LOWER(FirstName) LIKE LOWER(?) 
									OR LOWER(LastName) LIKE LOWER(?) 
									OR Phone LIKE ? 
									OR Email LIKE ?)");

		$searchPattern = "%" . $searchTerm . "%";
		
		$stmt->bind_param("issss", $userId, $searchPattern, $searchPattern, $searchPattern, $searchPattern);
		$stmt->execute();
		
		$resultsArray = [];

		while($row = $result->fetch_assoc()) 
		{
    		$resultsArray[] = [
				"id" => $row["ID"],
				"firstName" => $row["FirstName"],
				"lastName" => $row["LastName"],
				"phone" => $row["Phone"],
				"email" => $row["Email"],
				"userId" => $row["UserID"]
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