<?php
session_start();
include_once '../config/database.php';

// SESSION SECURITY CHECK

if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(["message" => "Access Denied: Admin privileges required."]);
    exit();
}

$database = new Database();
$db = $database->getConn(); // Use the correct method name

$query = "SELECT id, email, first_name, last_name, job_title, access_level FROM users ORDER BY last_name ASC";
$stmt = $db->prepare($query);
$stmt->execute();

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

http_response_code(200);
echo json_encode($users);