<?php
require_once 'includes/auth.php';

requireAuth();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user = getCurrentUser();
    $pdo = getDBConnection();
    $bill_id = $_GET['id'];
    
    // Check if user has access to this bill and it's active
    $stmt = $pdo->prepare("
        SELECT id FROM bills 
        WHERE id = ? AND status = 'active' AND (creator_id = ? OR id IN (
            SELECT bill_id FROM bill_participants WHERE user_id = ?
        ))
    ");
    $stmt->execute([$bill_id, $user['id'], $user['id']]);
    
    if ($stmt->fetch()) {
        // Mark bill as settled
        $stmt = $pdo->prepare("UPDATE bills SET status = 'settled' WHERE id = ?");
        $stmt->execute([$bill_id]);
        
        header('Location: archive.php?settled=1');
        exit();
    }
}

header('Location: dashboard.php?error=access_denied');
exit();
?>
