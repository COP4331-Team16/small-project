<?php
header("Access-Control-Allow-Origin: http://portcall.cloud");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$inData = getRequestInfo();

$conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
if ($conn->connect_error)
{
    error_log("DB Connection Error: " . $conn->connect_error);
    returnWithError("Database connection failed");
}

$contactId = intval($inData['contactId'] ?? 0);
$userId = intval($inData['userId'] ?? 0);

// Validate input
if ($contactId === 0 || $userId === 0)
{
    $conn->close();
    returnWithError("Invalid or missing contactId or userId");
}

// Check if contact exists AND belongs to this user (security check)
$stmt = $conn->prepare("SELECT contactID FROM Contacts WHERE contactID = ? AND userId = ?");
//                             ^^^^^^^^^                      ^^^^^^^^^  FIXED: Capital ID
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    $conn->close();
    returnWithError("Database error");
}

$stmt->bind_param("ii", $contactId, $userId);

if (!$stmt->execute()) {
    error_log("Execute failed: " . $stmt->error);
    $stmt->close();
    $conn->close();
    returnWithError("Database error");
}

$result = $stmt->get_result();

if ($result->num_rows === 0)
{
    $stmt->close();
    $conn->close();
    returnWithError("Contact not found or access denied");
}

$stmt->close();

// Delete the specific contact
$stmt = $conn->prepare("DELETE FROM Contacts WHERE contactID = ? AND userId = ?");
//                                                  ^^^^^^^^^  FIXED: Capital ID
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    $conn->close();
    returnWithError("Database error");
}

$stmt->bind_param("ii", $contactId, $userId);

if (!$stmt->execute()) {
    error_log("Execute failed: " . $stmt->error);
    $stmt->close();
    $conn->close();
    returnWithError("Database error");
}

$stmt->close();
$conn->close();

returnWithInfo("Contact deleted successfully", $contactId);

function getRequestInfo()
{
    return json_decode(file_get_contents('php://input'), true);
}

function sendResultInfoAsJson($obj)
{
    if (!headers_sent()) {
        header('Content-type: application/json');
    }
    echo $obj;
    exit;
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
        "contactId" => $id,
        "error" => ""
    ]));
}
?>