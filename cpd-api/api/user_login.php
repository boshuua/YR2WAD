<?php
// Add this at the top to enable sessions
session_start();
include_once '../config/database.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->email) || !isset($data->password)) {
    http_response_code(400);
    echo json_encode(["message" => "Email and password are required."]);
    exit();
}

$database = new Database();
$db = $database->getConn();

$email = $data->email;
$password = $data->password;

// This is the corrected line: We are now selecting the 'password' column
$query = "SELECT id, first_name, last_name, password, access_level FROM users WHERE email = :email LIMIT 1";

$stmt = $db->prepare($query);
$stmt->bindParam(':email', $email);
$stmt->execute();

$num = $stmt->rowCount();

if ($num > 0) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $hashed_password = $row['password']; // This will now work correctly

    // Verify the password
    if (password_verify($password, $hashed_password)) {
        // Store user data in session
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['access_level'] = $row['access_level'];

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
        http_response_code(401); // Unauthorized
        echo json_encode(["message" => "Login failed. Incorrect password."]);
    }
} else {
    http_response_code(404); // Not Found
    echo json_encode(["message" => "Login failed. User not found."]);
}
?>