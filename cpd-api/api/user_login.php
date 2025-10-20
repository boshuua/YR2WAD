<?php
session_start(); // Start the session at the very top
include_once '../config/database.php';
include_once '../helpers/log_helper.php';
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->email) || !isset($data->password)) {
    http_response_code(400); echo json_encode(["message" => "Email and password are required."]); exit();
}

$database = new Database();
$db = $database->getConn(); // Use the correct method name
// pgcrypto query
$query = "SELECT id, first_name, last_name, email, password, access_level FROM users WHERE email = :email AND password = crypt(:password, password)";
$stmt = $db->prepare($query);
$stmt->bindParam(':email', $data->email);
$stmt->bindParam(':password', $data->password);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // ===============================================================
    // Store user info in the session
    // ===============================================================
    $_SESSION['user_id'] = $row['id'];
    $_SESSION['access_level'] = $row['access_level'];
    $_SESSION['user_email'] = $row['email'];
    log_activity($db, $row['id'], $row['email'], 'login_success');
    http_response_code(200);
    echo json_encode([
        "message" => "Login successful.",
        "user" => [ 
            "id" => $row['id'], 
            "first_name" => $row['first_name'], 
            "access_level" => $row['access_level'] 
        ]
    ]);
} else {
    log_activity($db, null, $data->email, 'login_failed', 'Invalid credentials');

    http_response_code(401);
    echo json_encode(["message" => "Login failed. Invalid credentials."]);
}
?>