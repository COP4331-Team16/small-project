<?php
header("Access-Control-Allow-Origin: http://portcall.cloud");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

        $inData = getRequestInfo();

        $conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);

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
                $stmt = $conn->prepare("SELECT contactID FROM Contacts WHERE contactID = ? AND userId = ?");
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
                $stmt = $conn->prepare("UPDATE Contacts SET firstName=?, lastName=?, phone=?, email=? WHERE contactID=?");

                $stmt->bind_param("ssssi", $firstName, $lastName, $phone, $email, $contactId);
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
