<?php
require_once 'includes/auth.php';

requireAuth();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user = getCurrentUser();
    $pdo = getDBConnection();
    $bill_id = $_GET['id'];
    
    // Check if user owns this bill or is a participant
    $stmt = $pdo->prepare("
        SELECT id FROM bills 
        WHERE id = ? AND (creator_id = ? OR id IN (
            SELECT bill_id FROM bill_participants WHERE user_id = ?
        ))
    ");
    $stmt->execute([$bill_id, $user['id'], $user['id']]);
    
    if ($stmt->fetch()) {
        // Archive the bill
        $stmt = $pdo->prepare("UPDATE bills SET status = 'archived' WHERE id = ?");
        $stmt->execute([$bill_id]);
        
        header('Location: archive.php?archived=1');
        exit();
    }
}

header('Location: dashboard.php?error=access_denied');
exit();
?>
