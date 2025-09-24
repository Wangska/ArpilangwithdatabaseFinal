<?php
require_once 'includes/auth.php';

requireAuth();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user = getCurrentUser();
    $pdo = getDBConnection();
    $bill_id = $_GET['id'];
    
    // Check if user owns this bill
    $stmt = $pdo->prepare("SELECT id FROM bills WHERE id = ? AND creator_id = ?");
    $stmt->execute([$bill_id, $user['id']]);
    
    if ($stmt->fetch()) {
        try {
            $pdo->beginTransaction();
            
            // Delete will cascade to related tables due to foreign key constraints
            $stmt = $pdo->prepare("DELETE FROM bills WHERE id = ?");
            $stmt->execute([$bill_id]);
            
            $pdo->commit();
            header('Location: dashboard.php?deleted=1');
            exit();
            
        } catch (PDOException $e) {
            $pdo->rollback();
            header('Location: dashboard.php?error=delete_failed');
            exit();
        }
    }
}

header('Location: dashboard.php?error=access_denied');
exit();
?>
