<?php

	header('Content-Type: application/json');
	$inData = getRequestInfo();
    
	$userId = 0;
	$firstName = "";
	$lastName = "";

	$conn = new mysqli("localhost", "user", "password", "database");     

	if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
	else
	{
		// Basic input validation
		$firstName = trim($inData['firstName'] ?? '');
		$lastName = trim($inData['lastName'] ?? '');
		$login = trim($inData['login'] ?? '');
		$password = $inData['password'] ?? '';

		if($firstName === '' || $lastName === '' || $login === '' || $password === '') {
			returnWithError("Missing required fields");
			$conn->close();
			exit;
		}

		// Check if user already exists
		$stmt = $conn->prepare("SELECT userId FROM Users WHERE login = ?");
		$stmt->bind_param("s", $login);
		$stmt->execute();
		$result = $stmt->get_result();

		if( $result->num_rows > 0 )
		{
			$stmt->close();
			$conn->close();
			returnWithError("User already exists");
			exit;
		}
		$stmt->close();

		// Hash the password and insert new user
		$passwordHash = password_hash($password, PASSWORD_DEFAULT);

		$stmt = $conn->prepare("INSERT INTO Users (firstName, lastName, login, password) VALUES (?, ?, ?, ?)");
		$stmt->bind_param("ssss", $firstName, $lastName, $login, $passwordHash);

		if( $stmt->execute() )
		{
			$userId = $conn->insert_id;
			returnWithInfo( $firstName, $lastName, $userId, $login );
		}
		else
		{
			returnWithError("Insert failed");
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
		echo $obj;
	}
    
	function returnWithError( $err )
	{
		$retValue = '{"id":0,"firstName":"","lastName":"","login":"","error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}
    
	function returnWithInfo( $firstName, $lastName, $id, $login )
	{
		$retValue = '{"id":' . $userId . ',"firstName":"' . $firstName . '","lastName":"' . $lastName . '","login":"' . $login . '","error":""}';
		sendResultInfoAsJson( $retValue );
	}
    
?>
