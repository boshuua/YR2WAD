<?php

session_start();

include_once '../config/database.php';

if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["message" => "Access denied. Admins only."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Method invalid."]);
    exit();
}

$userID = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($userID <=0) {
    http_response_code(400);
    echo json_encode(['message'=> 'invalid user id']);
    exit();
}

if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userID) {
    http_response_code(400);
    echo json_encode(['message'=> 'You cannot delete your own account.']);
    exit();
}

$database = new Database();
$db = $database->getConn();

try {
    $query = "DELETE FROM users WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindValue(":id", $userID, PDO::PARAM_INT);

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode(["message"=> "user deleted successfully"]);

        } else {
            http_response_code(404);
            echo json_encode(["message"=> "user either not found or already deleted"]);
        }
    } else {
        http_response_code(503);
        echo json_encode(["message"=> "unable to delete user."]);
    }
} catch (PDOException $e) {
    http_response_code(503);
    echo json_encode(["message"=> "database error: " . $e->getMessage()]);
}