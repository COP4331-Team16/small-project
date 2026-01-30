<?php
	$inData = getRequestInfo();

	$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");
	if ($conn->connect_error)
	{
		returnWithError($conn->connect_error);
		exit;
	}
	else
	{

		
	$userId = $inData['userId'] ?? null;

	// Validate input
	if (!$userId || !is_numeric($userId))
	{
		$conn->close();
		returnWithError("Invalid or missing userId");
		exit;
	}

	// Check if contact exists
	$stmt = $conn->prepare("SELECT userId FROM Contacts WHERE userId = ?");
	$stmt->bind_param("i", $userId);
	$stmt->execute();
	$result = $stmt->get_result();

	if ($result->num_rows === 0)
	{
		$stmt->close();
		$conn->close();
		returnWithError("Contact not found");
		exit;
	}

	$stmt->close();

	// Delete contact
	$stmt = $conn->prepare("DELETE FROM Contacts WHERE userId = ?");
	$stmt->bind_param("i", $userId);
	$stmt->execute();
	$stmt->close();
	$conn->close();

	returnWithInfo("Contact deleted successfully", $userId);
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
			"success" => "",
			"user" => "",
			"error" => $err
		]));
	}

	function returnWithInfo($message,$id)
	{
		sendResultInfoAsJson(json_encode([
			"success" => $message,
			"user" => $id,
			"error" => ""
		]));
	}
?>
