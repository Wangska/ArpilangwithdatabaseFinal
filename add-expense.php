<?php
require_once 'includes/auth.php';

requireAuth();

if ($_POST) {
    $user = getCurrentUser();
    $pdo = getDBConnection();
    
    $bill_id = $_POST['bill_id'] ?? null;
    $description = trim($_POST['description'] ?? '');
    $amount = $_POST['amount'] ?? null;
    $paid_by = $_POST['paid_by'] ?? null;
    
    if (empty($bill_id) || empty($description) || empty($amount) || empty($paid_by)) {
        header("Location: bill.php?id=$bill_id&error=missing_fields");
        exit();
    }
    
    // Validate amount
    if (!is_numeric($amount) || $amount <= 0) {
        header("Location: bill.php?id=$bill_id&error=invalid_amount");
        exit();
    }
    
    // Check if user has access to this bill
    $stmt = $pdo->prepare("
        SELECT id FROM bills 
        WHERE id = ? AND (creator_id = ? OR id IN (
            SELECT bill_id FROM bill_participants WHERE user_id = ?
        ))
    ");
    $stmt->execute([$bill_id, $user['id'], $user['id']]);
    
    if (!$stmt->fetch()) {
        header("Location: dashboard.php?error=access_denied");
        exit();
    }
    
    // Check if paid_by is a participant
    $stmt = $pdo->prepare("
        SELECT id FROM bill_participants 
        WHERE bill_id = ? AND user_id = ?
    ");
    $stmt->execute([$bill_id, $paid_by]);
    
    if (!$stmt->fetch()) {
        header("Location: bill.php?id=$bill_id&error=invalid_participant");
        exit();
    }
    
    try {
        $pdo->beginTransaction();
        
        // Add the expense
        $stmt = $pdo->prepare("INSERT INTO expenses (bill_id, description, amount, paid_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$bill_id, $description, $amount, $paid_by]);
        $expense_id = $pdo->lastInsertId();
        
        // Add expense splits (equal split among all participants)
        $stmt = $pdo->prepare("SELECT user_id FROM bill_participants WHERE bill_id = ?");
        $stmt->execute([$bill_id]);
        $participants = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $split_amount = $amount / count($participants);
        
        foreach ($participants as $participant_id) {
            $stmt = $pdo->prepare("INSERT INTO expense_splits (expense_id, user_id, amount) VALUES (?, ?, ?)");
            $stmt->execute([$expense_id, $participant_id, $split_amount]);
        }
        
        $pdo->commit();
        header("Location: bill.php?id=$bill_id&expense_added=1");
        exit();
        
    } catch (PDOException $e) {
        $pdo->rollback();
        header("Location: bill.php?id=$bill_id&error=add_failed");
        exit();
    }
} else {
    header('Location: dashboard.php');
    exit();
}
?>
