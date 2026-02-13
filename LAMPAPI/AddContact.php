<?php
	require_once dirname(__DIR__) . '/vendor/autoload.php';

	use Dotenv\Dotenv;

	$dotenv = Dotenv::createImmutable(dirname(__DIR__));
	$dotenv->load();

	$inData = getRequestInfo();
	
	$firstName = "";
	$lastName = "";
	$phone = "";
	$email = "";


	$conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
	if ($conn->connect_error) 
	{
		returnWithError($conn->connect_error);
	} 
	else
	{
		// Get and validate input
		$userId = intval($inData['userId'] ?? 0);  
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

		// Check if THIS USER already has a contact with this phone
		$stmt = $conn->prepare("SELECT contactID FROM Contacts WHERE phone = ? AND userId = ?");  
		$stmt->bind_param("si", $phone, $userId);
		$stmt->execute();
		$result = $stmt->get_result();
		
		if($result->num_rows > 0)
		{
			$stmt->close();
			$conn->close();
			returnWithError("You already have a contact with this phone number");
			exit;
		}

		$stmt->close();

		// Insert with camelCase column names
		$stmt = $conn->prepare("INSERT INTO Contacts (firstName, lastName, phone, email, userId) VALUES(?,?,?,?,?)");  
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

	function sendResultInfoAsJson($obj)
	{
		header('Content-type: application/json');
		echo $obj;
	}
	
	function returnWithError($err)
	{
		$retValue = '{"id":0,"firstName":"","lastName":"","phone":"","email":"","error":"' . $err . '"}';
		sendResultInfoAsJson($retValue);
	}
	
	function returnWithInfo($firstName, $lastName, $id, $phone, $email)
	{
		$retValue = '{"id":' . $id . ',"firstName":"' . $firstName . '","lastName":"' . $lastName . '","phone":"' . $phone . '","email":"' . $email . '","error":""}';
		sendResultInfoAsJson($retValue);
	}
	
?>