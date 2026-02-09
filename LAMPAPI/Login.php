<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

        $inData = getRequestInfo();

        $conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
        if( $conn->connect_error )
        {
                returnWithError( $conn->connect_error );
        }
        else
        {
                $stmt = $conn->prepare("SELECT userId, firstName, lastName, password FROM Users WHERE login=?");
                $stmt->bind_param("s", $inData["login"]);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();

                if(password_verify($inData["password"], $row["password"]))
                {
                        returnWithInfo($row["firstName"], $row["lastName"], $row["userId"]);
                }
                else
                {
                        returnWithError("Unable to sign in");
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
                $retValue = '{"id":0,"firstName":"","lastName":"","error":"' . $err . '"}';
                sendResultInfoAsJson( $retValue );
        }

        function returnWithInfo( $firstName, $lastName, $id )
        {
                $retValue = '{"id":' . $id . ',"firstName":"' . $firstName . '","lastName":"' . $lastName . '","error":""}';
                sendResultInfoAsJson( $retValue );
        }

?>