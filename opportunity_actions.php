<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Database connection
require_once 'includes/db_connection.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $opportunity_id = $_POST['id'] ?? null;
    $action = $_POST['action'] ?? '';

    // Verify the opportunity belongs to the current user
    if ($opportunity_id) {
        $stmt = $conn->prepare("SELECT user_id FROM opportunities WHERE opportunity_id = ?");
        $stmt->bind_param("i", $opportunity_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $opportunity = $result->fetch_assoc();
        $stmt->close();

        // Convert both IDs to integers for comparison
        $session_user_id = (int)$_SESSION['user_id'];
        $opportunity_user_id = isset($opportunity['user_id']) ? (int)$opportunity['user_id'] : null;

        if (!$opportunity || $opportunity_user_id !== $session_user_id) {
            $_SESSION['error'] = "You don't have permission to modify this opportunity.";
            header('Location: shared_by_me.php');
            exit();
        }
    }

    // Handle Update
    if ($action === 'edit') {
        // Fetch the opportunity to edit
        $opportunity_id = $_GET['id'] ?? null;
        if (!$opportunity_id) {
            $_SESSION['error'] = "Opportunity ID is missing.";
            header('Location: shared_by_me.php');
            exit();
        }

        // Fetch the opportunity details
        $stmt = $conn->prepare("SELECT * FROM opportunities WHERE opportunity_id = ? AND user_id = ? AND is_deleted = 0");
        $stmt->bind_param("ii", $opportunity_id, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $opportunity = $result->fetch_assoc();
        $stmt->close();

        if (!$opportunity) {
            $_SESSION['error'] = "Opportunity not found or you do not have permission to edit it.";
            header('Location: shared_by_me.php');
            exit();
        }
    }

    // Handle Delete
    elseif ($action === 'soft_delete') {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // First delete related notifications (optional based on your system's needs)
            $stmt = $conn->prepare("DELETE FROM notifications WHERE related_opportunity_id = ?");
            $stmt->bind_param("i", $opportunity_id);
            $stmt->execute();
            $stmt->close();
            
            // Then mark the opportunity as deleted (soft delete)
            $stmt = $conn->prepare("UPDATE opportunities SET is_deleted = 1 WHERE opportunity_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $opportunity_id, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();
            
            // Commit the transaction
            $conn->commit();
            $_SESSION['success'] = "Opportunity soft-deleted successfully!";
        } catch (Exception $e) {
            // If anything fails, roll back the changes
            $conn->rollback();
            $_SESSION['error'] = "Error soft-deleting opportunity. Please try again.";
            error_log("Soft Delete Error: " . $e->getMessage());
        }
    }

    $conn->close();
    header('Location: shared_by_me.php');
    exit();
}
?>