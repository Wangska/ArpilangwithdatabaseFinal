<?php
require_once 'includes/auth.php';

requireAuth();

if (isset($_GET['id']) && isset($_GET['bill_id']) && is_numeric($_GET['id']) && is_numeric($_GET['bill_id'])) {
    $user = getCurrentUser();
    $pdo = getDBConnection();
    $expense_id = $_GET['id'];
    $bill_id = $_GET['bill_id'];
    
    // Check if user can delete this expense (must be the person who paid or bill creator)
    $stmt = $pdo->prepare("
        SELECT e.id, b.creator_id
        FROM expenses e
        JOIN bills b ON e.bill_id = b.id
        WHERE e.id = ? AND e.bill_id = ? AND (e.paid_by = ? OR b.creator_id = ?)
    ");
    $stmt->execute([$expense_id, $bill_id, $user['id'], $user['id']]);
    
    if ($stmt->fetch()) {
        try {
            $pdo->beginTransaction();
            
            // Delete expense (splits will be deleted due to foreign key cascade)
            $stmt = $pdo->prepare("DELETE FROM expenses WHERE id = ?");
            $stmt->execute([$expense_id]);
            
            $pdo->commit();
            header("Location: bill.php?id=$bill_id&expense_deleted=1");
            exit();
            
        } catch (PDOException $e) {
            $pdo->rollback();
            header("Location: bill.php?id=$bill_id&error=delete_failed");
            exit();
        }
    }
}

header("Location: bill.php?id=" . ($_GET['bill_id'] ?? '') . "&error=access_denied");
exit();
?>
