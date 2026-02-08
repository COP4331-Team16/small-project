<?php
	$inData = getRequestInfo();
	
	$conn = new mysqli("localhost", "user", "password", "database");
	if ($conn->connect_error) 
	{
		returnWithError($conn->connect_error);
	} 
	else
	{
		// Get and validate input
		$contactId = intval($inData['contactId'] ?? 0);
		$userId = intval($inData['userId'] ?? 0);
		$firstName = trim($inData['firstName'] ?? '');
		$lastName = trim($inData['lastName'] ?? '');
		$phone = trim($inData['phone'] ?? '');
		$email = trim($inData['email'] ?? '');

		if($contactId === 0 || $userId === 0 || $firstName === '' || $lastName === '' || $phone === '' || $email === '')
		{
			returnWithError("Missing required fields");
			$conn->close();
			exit;
		}

		// Verify the contact exists AND belongs to this user
		$stmt = $conn->prepare("SELECT ID FROM Contacts WHERE ID = ? AND UserID = ?");
		$stmt->bind_param("ii", $contactId, $userId);
		$stmt->execute();
		$result = $stmt->get_result();

		if($result->num_rows === 0)
		{
			$stmt->close();
			$conn->close();
			returnWithError("Contact not found or access denied");
			exit;
		}
		$stmt->close();

		// Update the contact 
		$stmt = $conn->prepare("UPDATE Contacts 
								SET FirstName=?, LastName=?, Phone=?, Email=? 
								WHERE ID=? AND UserID=?");

		$stmt->bind_param("sssiii", $firstName, $lastName, $phone, $email, $contactId, $userId);
		$stmt->execute();
		$stmt->close();
		$conn->close();

		returnWithInfo("Contact updated successfully");
	}

	function getRequestInfo()
	{
		return json_decode(file_get_contents('php://input'), true);
	}

	function sendResultInfoAsJson($obj)
	{
		header('Content-type: application/json');
		echo $obj;
	}
	
	function returnWithError($err)
	{
		$retValue = json_encode(["success" => false, "error" => $err]);
		sendResultInfoAsJson($retValue);
	}

	function returnWithInfo($message)
	{
		$retValue = json_encode(["success" => true, "error" => "", "message" => $message]);
		sendResultInfoAsJson($retValue);
	}
?>