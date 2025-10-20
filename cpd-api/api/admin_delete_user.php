<?php

session_start();

include_once '../config/database.php';
include_once '../helpers/log_helper.php';
if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["message" => "Access denied. Admins only."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') { // Only allow DELETE
    http_response_code(405);
    echo json_encode(["message" => "Method invalid. Use DELETE."]);
    exit();
}

$userID = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($userID <= 0) {
    http_response_code(400);
    echo json_encode(['message' => 'invalid user id']);
    exit();
}

// *** Prevent admin from deleting themselves ***
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userID) {
    http_response_code(400);
    echo json_encode(['message' => 'You cannot delete your own account.']);
    exit();
}

$database = new Database();
$db = $database->getConn();

// *** Get admin details from session for logging ***
$adminUserId = $_SESSION['user_id'] ?? null;
$adminUserEmail = $_SESSION['user_email'] ?? 'Unknown Admin'; // Get email stored during login

// *** Fetch email of user being deleted for better logging ***
$emailToLog = 'Unknown (User not found)';
try {
    $fetchEmailQuery = "SELECT email FROM users WHERE id = :id";
    $fetchStmt = $db->prepare($fetchEmailQuery);
    $fetchStmt->bindParam(':id', $userID, PDO::PARAM_INT);
    $fetchStmt->execute();
    if ($fetchStmt->rowCount() > 0) {
        $emailToLog = $fetchStmt->fetchColumn();
    }
} catch (PDOException $e) {
    error_log("Failed to fetch email before delete: " . $e->getMessage());
}
// *** End fetch email ***

try {
    $query = "DELETE FROM users WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindValue(":id", $userID, PDO::PARAM_INT);

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            // *** Log successful deletion ***
            $details = "Admin deleted user ID: {$userID}, Email: {$emailToLog}";
            log_activity($db, $adminUserId, $adminUserEmail, 'admin_delete_user_success', $details);
            // *** End log ***

            http_response_code(200);
            echo json_encode(["message" => "User deleted successfully"]);
        } else {
            // *** Log attempt to delete non-existent user ***
            $details = "Admin attempted to delete non-existent user ID: {$userID}";
            log_activity($db, $adminUserId, $adminUserEmail, 'admin_delete_user_failed_notfound', $details);
            // *** End log ***

            http_response_code(404);
            echo json_encode(["message" => "User not found or already deleted"]);
        }
    } else {
        // *** Log database execution failure ***
        $details = "Database error executing delete for user ID: {$userID}";
        log_activity($db, $adminUserId, $adminUserEmail, 'admin_delete_user_failed_execution', $details);
        // *** End log ***

        http_response_code(503);
        echo json_encode(["message" => "Unable to delete user."]);
    }
} catch (PDOException $e) {
    // *** Log general database error ***
    $details = "Database exception deleting user ID {$userID}: " . $e->getMessage();
    log_activity($db, $adminUserId, $adminUserEmail, 'admin_delete_user_error', $details);
    // *** End log ***

    http_response_code(503);
    error_log("Database error during delete: " . $e->getMessage()); // Log detailed error server-side
    echo json_encode(["message" => "Database error occurred during deletion."]);
}
