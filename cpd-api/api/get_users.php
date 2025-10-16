<?php

session_start();

include_once '../config/database.php';

// TODO: use proper authentication and authorization here i.e JWT, OAuth, etc.

$database = new Database();

$db = $database->getConn();

$query = "SELECT id, email, first_name, last_name, job_title, access_level FROM users ORDER BY last_name ASC";
$stmt = $db->prepare($query);
$stmt->execute();

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

http_response_code(200);
echo json_encode($users);
