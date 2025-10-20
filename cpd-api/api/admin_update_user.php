<?php
session_start();
include_once '../config/database.php';

// --- Add log_activity function definition here if not using helper ---
function log_activity(PDO $db, ?int $userId, ?string $userEmail, string $action, ?string $details = null): void { /* ... function code ... */ }
// --- End log_activity function ---


// Security Check for admin
if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["message" => "Access Denied: Admin privileges required."]);
    exit();
}

// Get Input Data
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$data = json_decode(file_get_contents("php://input"));

// --- Security Check ---
if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(["message" => "Access Denied: Admin privileges required."]);
    exit();
}

$database = new Database();
$db = $database->getConn();

// Get admin details for logging
$adminUserId = $_SESSION['user_id'] ?? null;
$adminUserEmail = $_SESSION['user_email'] ?? 'Unknown Admin';

// Prepare Update Query (excluding password for now)
$query = "UPDATE users
          SET first_name = :first_name,
              last_name = :last_name,
              email = :email,
              job_title = :job_title,
              access_level = :access_level
          WHERE id = :id";

$stmt = $db->prepare($query);

// Handle optional fields safely
$last_name = $data->last_name ?? '';
$job_title = $data->job_title ?? '';

// Bind parameters
$stmt->bindParam(':first_name', $data->first_name);
$stmt->bindParam(':last_name', $last_name);
$stmt->bindParam(':email', $data->email);
$stmt->bindParam(':job_title', $job_title);
$stmt->bindParam(':access_level', $data->access_level);
$stmt->bindParam(':id', $userId, PDO::PARAM_INT);

// --- Execute and Respond ---
try {
    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
             // *** Log successful update ***
            $details = "Admin updated user ID: {$userId} (Email: {$data->email})";
            log_activity($db, $adminUserId, $adminUserEmail, 'admin_update_user_success', $details);
             // *** End log ***

            http_response_code(200);
            echo json_encode(["message" => "User updated successfully."]);
        } else {
            // Check if user exists but no changes were made or user not found
            // ... (keep existing check logic) ...
            if ($checkStmt->fetchColumn() > 0) {
                 // Optionally log 'no changes detected' if desired
                // log_activity($db, $adminUserId, $adminUserEmail, 'admin_update_user_nochange', "No changes for user ID: {$userId}");
                http_response_code(200);
                echo json_encode(["message" => "No changes detected for the user."]);
            } else {
                 // *** Log attempt to update non-existent user ***
                 $details = "Admin attempted to update non-existent user ID: {$userId}";
                 log_activity($db, $adminUserId, $adminUserEmail, 'admin_update_user_failed_notfound', $details);
                 // *** End log ***
                http_response_code(404);
                echo json_encode(["message" => "User not found."]);
            }
        }
    } else {
        // Log failure (though execute usually throws PDOException on failure)
        $details = "Failed attempt to update user ID: {$userId}";
        log_activity($db, $adminUserId, $adminUserEmail, 'admin_update_user_failed_execution', $details);
        http_response_code(503);
        echo json_encode(["message" => "Unable to update user."]);
    }
} catch (PDOException $e) {
    // *** Log database error during update ***
    $details = "Database error updating user ID {$userId}: " . $e->getMessage();
    log_activity($db, $adminUserId, $adminUserEmail, 'admin_update_user_error', $details);
    // *** End log ***
    http_response_code(503);
    error_log("Database error: " . $e->getMessage());
    echo json_encode(["message" => "Database error occurred during update."]);
}

?>