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
		$email = $inData['email'] ?? '';

		if($firstName === '' || $lastName === '' || $phone === '' || $email === '')
		{
			returnWithError("Missing required fields");
			$conn->close();
			exit;
		}


		$stmt = $conn->prepare("SELECT userId FROM Contacts WHERE phone = ?");
		$stmt->bind_param("s", $phone);
		$stmt->execute();
		$result = $stmt->get_result();

		if($result->num_rows === 0)
		{
    		$stmt->close();
    		$conn->close();
    		returnWithError("Contact not found");
    		exit;
		}

		$row = $result->fetch_assoc();
		$userId = $row['userId'];
		
		$stmt->close();


		$stmt = $conn->prepare("UPDATE Contacts 
								SET firstName=?, lastName=?, phone=?, email=? 
								WHERE userId=?");


		$stmt->bind_param("ssssi", $firstName, $lastName, $phone, $email, $userId);
		$stmt->execute();
		$stmt->close();
		$conn->close();

		returnWithInfo("Contact updated successfully");
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
		$retValue = json_encode(["success" => "", "error" => $err]);
		sendResultInfoAsJson($retValue);
	}

	function returnWithInfo($contact)
	{
    	$retValue = json_encode(["success" => "Contact updated successfully", "error" => "", "contact" => $contact]);
    	sendResultInfoAsJson($retValue);
	}

?>