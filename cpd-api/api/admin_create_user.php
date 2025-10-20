<?php
session_start();
include_once '../config/database.php';

function log_activity(PDO $db, ?int $userId, ?string $userEmail, string $action, ?string $details = null): void {
    // Get IP address
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
    }

    try {
        $query = "INSERT INTO activity_log (user_id, user_email, action, details, ip_address)
                  VALUES (:user_id, :user_email, :action, :details, :ip_address)";
        $stmt = $db->prepare($query);

        $stmt->bindParam(':user_id', $userId, $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindParam(':user_email', $userEmail, $userEmail === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':details', $details, $details === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindParam(':ip_address', $ipAddress);

        $stmt->execute();
    } catch (PDOException $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}
// --- End log_activity function ---


// Security Check for admin
if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["message" => "Access Denied: Admin privileges required."]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

// Input validation
if (!isset($data->first_name) || !isset($data->email) || !isset($data->password) || !isset($data->access_level)) {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete data. First name, email, password, and access level are required."]);
    exit();
}

$database = new Database();
$db = $database->getConn();

// Get admin details for logging
$adminUserId = $_SESSION['user_id'] ?? null;
$adminUserEmail = $_SESSION['user_email'] ?? 'Unknown Admin'; // Assuming email stored in session on login

// Prepare insert query
$query = "INSERT INTO users (first_name, last_name, email, password, job_title, access_level)
          VALUES (:first_name, :last_name, :email, crypt(:password, gen_salt('bf')), :job_title, :access_level)
          RETURNING id"; // Add RETURNING id to get the new user's ID

$stmt = $db->prepare($query);

// Handle optional fields
$last_name = $data->last_name ?? '';
$job_title = $data->job_title ?? '';

// Bind parameters
$stmt->bindParam(':first_name', $data->first_name);
$stmt->bindParam(':last_name', $last_name);
$stmt->bindParam(':email', $data->email);
$stmt->bindParam(':password', $data->password);
$stmt->bindParam(':job_title', $job_title);
$stmt->bindParam(':access_level', $data->access_level);

try {
    if ($stmt->execute()) {
        $newUserId = $stmt->fetchColumn(); // Get the ID of the newly created user

        // *** Log successful creation ***
        $details = "Admin created user: {$data->email} (ID: {$newUserId}) with access level: {$data->access_level}";
        log_activity($db, $adminUserId, $adminUserEmail, 'admin_create_user_success', $details);
        // *** End log ***

        http_response_code(201); // Created
        echo json_encode(["message" => "User created successfully.", "newUserId" => $newUserId]);
    } else {
        // Log failure (though execute usually throws PDOException on failure)
         $details = "Failed attempt to create user: {$data->email}";
        log_activity($db, $adminUserId, $adminUserEmail, 'admin_create_user_failed', $details);
        http_response_code(503); // Service Unavailable
        echo json_encode(["message" => "Unable to create user."]);
    }
} catch (PDOException $e) {
     // Log database error during creation
     $details = "Database error creating user {$data->email}: " . $e->getMessage();
     log_activity($db, $adminUserId, $adminUserEmail, 'admin_create_user_error', $details);
     http_response_code(503);
     // Be cautious about echoing raw DB errors to the client in production
     echo json_encode(["message" => "Database error occurred during user creation."]);
}
?>