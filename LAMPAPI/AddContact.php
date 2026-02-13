<?php
	$inData = getRequestInfo();
	
	$firstName = "";
	$lastName = "";
	$phone = "";
	$email = "";


	$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
		// Basic input validation
		$firstName = trim($inData['firstName'] ?? '');
		$lastName = trim($inData['lastName'] ?? '');
		$phone = trim($inData['phone'] ?? '');
		$email = trim($inData['email'] ?? '');

		if($userId === 0 || $firstName === '' || $lastName === '' || $phone === '' || $email === '')
		{
			returnWithError("Missing required fields");
			$conn->close();
			exit;
		}

		// Check if user already exists
		$stmt = $conn->prepare("SELECT ID FROM Contacts WHERE Phone = ? AND UserID = ?");
		$stmt->bind_param("si", $phone, $userId);
		$stmt->execute();
		$result = $stmt->get_result();
		
		if( $result->num_rows > 0 )
		{
			$stmt->close();
			$conn->close();
			returnWithError("Contact already exists");
			exit;
		}

		$stmt->close();

		$stmt = $conn->prepare("INSERT INTO Contacts (FirstName, LastName, Phone, Email, UserID) VALUES(?,?,?,?,?)");
		$stmt->bind_param("ssssi", $firstName, $lastName, $phone, $email, $userId);
		$stmt->execute();

		$id = $conn->insert_id;
		
		$stmt->close();
		$conn->close();

		returnWithInfo($firstName, $lastName, $id, $phone, $email);
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
	
	function returnWithError($err)
	{
		$retValue = '{"id":0,"firstName":"","lastName":"","phone":"","email":"","error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}
	function returnWithInfo( $firstName, $lastName, $id, $phone, $email )
	{
		$retValue = '{"id":' . $id . ',"firstName":"' . $firstName . '","lastName":"' . $lastName . '","phone":"' . $phone . '","email":"' . $email . '","error":""}';
		sendResultInfoAsJson( $retValue );
	}
	
?>