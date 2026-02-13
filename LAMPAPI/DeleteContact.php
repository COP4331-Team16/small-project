<?php
	$inData = getRequestInfo();

	$conn = new mysqli("localhost", "user", "password", "database");
	if ($conn->connect_error)
	{
		returnWithError($conn->connect_error);
		exit;
	}
	else
	{
		$contactID = $inData['contactID'] ?? null;
		$userId = $inData['userId'] ?? null;

		// Validate input
		if (!$contactID || !is_numeric($contactID) || !$userId || !is_numeric($userId))
		{
			$conn->close();
			returnWithError("Invalid or missing contactID or userId");
			exit;
		}

		// Check if contact exists AND belongs to this user (security check)
		$stmt = $conn->prepare("SELECT contactID FROM Contacts WHERE contactID = ? AND userId = ?");
		$stmt->bind_param("ii", $contactID, $userId);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows === 0)
		{
			$stmt->close();
			$conn->close();
			returnWithError("Contact not found or access denied");
			exit;
		}

		$stmt->close();

		// Delete the specific contact
		$stmt = $conn->prepare("DELETE FROM Contacts WHERE contactID = ? AND userId = ?");
		$stmt->bind_param("ii", $contactID, $userId);
		$stmt->execute();
		$stmt->close();
		$conn->close();

		returnWithInfo("Contact deleted successfully", $contactID);
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
		sendResultInfoAsJson(json_encode([
			"success" => false,
			"error" => $err
		]));
	}

	function returnWithInfo($message, $id)
	{
		sendResultInfoAsJson(json_encode([
			"success" => true,
			"message" => $message,
			"contactID" => $id,
			"error" => ""
		]));
	}
?>