<?php
require_once 'includes/auth.php';

// Ensure user is logged in
requireAuth();

if ($_POST) {
    $user = getCurrentUser();
    $pdo = getDBConnection();
    
    $bill_name = trim($_POST['bill_name'] ?? '');
    $category_id = $_POST['category_id'] ?? null;
    $invitation_code = trim($_POST['invitation_code'] ?? '');
    $participants = $_POST['participants'] ?? [];
    
    if (empty($bill_name) || empty($category_id) || empty($invitation_code)) {
        header('Location: dashboard.php?error=missing_fields');
        exit();
    }
    
    try {
        $pdo->beginTransaction();
        
        // Create the bill
        $stmt = $pdo->prepare("INSERT INTO bills (name, category_id, creator_id, invitation_code, status) VALUES (?, ?, ?, ?, 'active')");
        $stmt->execute([$bill_name, $category_id, $user['id'], $invitation_code]);
        $bill_id = $pdo->lastInsertId();
        
        // Add creator as participant
        $stmt = $pdo->prepare("INSERT INTO bill_participants (bill_id, user_id) VALUES (?, ?)");
        $stmt->execute([$bill_id, $user['id']]);
        
        // Add other participants if they exist as users
        if (!empty($participants)) {
            foreach ($participants as $participant_email) {
                $participant_email = trim($participant_email);
                if (!empty($participant_email)) {
                    // Check if user exists
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
                    $stmt->execute([$participant_email, $participant_email]);
                    $participant = $stmt->fetch();
                    
                    if ($participant) {
                        // Add as participant if not already added
                        try {
                            $stmt = $pdo->prepare("INSERT INTO bill_participants (bill_id, user_id) VALUES (?, ?)");
                            $stmt->execute([$bill_id, $participant['id']]);
                        } catch (PDOException $e) {
                            // Ignore duplicate entries
                        }
                    }
                }
            }
        }
        
        $pdo->commit();
        header('Location: bill.php?id=' . $bill_id . '&created=1');
        exit();
        
    } catch (PDOException $e) {
        $pdo->rollback();
        header('Location: dashboard.php?error=creation_failed');
        exit();
    }
} else {
    header('Location: dashboard.php');
    exit();
}
?>
