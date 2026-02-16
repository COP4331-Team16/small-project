<?php
header("Access-Control-Allow-Origin: http://portcall.cloud");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

        $inData = getRequestInfo();

        $firstName = "";
        $lastName = "";
        $phone = "";
        $email = "";
        $userId = 0;


        $conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);

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
                $userId = $inData['userId'] ?? 0;

                if($firstName === '' || $lastName === '' || $phone === '' || $email === '' || $userId === 0) {
                        returnWithError("Missing required fields");
                        $conn->close();
                        exit;
                }

                // Check if user already exists
                $stmt = $conn->prepare("SELECT userId FROM Contacts WHERE phone = ?");
                $stmt->bind_param("s", $phone);
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

                $stmt = $conn->prepare("INSERT into Contacts (firstName,lastName,phone,email, userId) VALUES(?,?,?,?,?)");
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
