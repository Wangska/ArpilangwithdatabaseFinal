<?php
require_once 'includes/auth.php';

requireAuth();

$user = getCurrentUser();
$pdo = getDBConnection();
$error = '';
$success = '';

// Handle form submission
if ($_POST) {
    $invitation_code = trim($_POST['invitation_code'] ?? '');
    
    if (empty($invitation_code)) {
        $error = 'Please enter an invitation code.';
    } else {
        // Find bill with this invitation code
        $stmt = $pdo->prepare("SELECT id, name, creator_id FROM bills WHERE invitation_code = ? AND status = 'active'");
        $stmt->execute([$invitation_code]);
        $bill = $stmt->fetch();
        
        if (!$bill) {
            $error = 'Invalid or expired invitation code.';
        } else {
            // Check if user is already a participant
            $stmt = $pdo->prepare("SELECT id FROM bill_participants WHERE bill_id = ? AND user_id = ?");
            $stmt->execute([$bill['id'], $user['id']]);
            
            if ($stmt->fetch()) {
                $error = 'You are already a participant in this bill.';
            } else {
                try {
                    // Add user as participant
                    $stmt = $pdo->prepare("INSERT INTO bill_participants (bill_id, user_id) VALUES (?, ?)");
                    $stmt->execute([$bill['id'], $user['id']]);
                    
                    header('Location: bill.php?id=' . $bill['id'] . '&joined=1');
                    exit();
                    
                } catch (PDOException $e) {
                    $error = 'Failed to join bill. Please try again.';
                }
            }
        }
    }
}

// Handle invitation code from URL
$url_code = $_GET['code'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Bill - SplitWise</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <a href="dashboard.php" class="logo">
                <div class="logo-icon">📊</div>
                SplitWise
            </a>
            <nav class="nav-links">
                <span>Welcome, <?php echo htmlspecialchars($user['first_name']); ?>!</span>
                <a href="profile.php">Profile</a>
                <a href="logout.php" class="btn btn-outline">Logout</a>
            </nav>
        </div>
    </header>

    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <ul class="sidebar-nav">
                <li><a href="dashboard.php">📋 Bills</a></li>
                <li><a href="archive.php">📁 Archive</a></li>
                <li><a href="profile.php">👤 Profile</a></li>
                <li><a href="logout.php">🚪 Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Join Bill</h1>
                    <p class="page-subtitle">Enter an invitation code to join a bill</p>
                </div>
            </div>

            <div style="max-width: 500px;">
                <div class="bill-card">
                    <?php if ($error): ?>
                        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label for="invitation_code">Invitation Code</label>
                            <input 
                                type="text" 
                                id="invitation_code" 
                                name="invitation_code" 
                                class="form-control" 
                                placeholder="Enter 9-character invitation code" 
                                value="<?php echo htmlspecialchars($url_code); ?>"
                                maxlength="9"
                                style="text-transform: uppercase; letter-spacing: 0.1em;"
                                required
                            >
                            <small style="color: #6b7280; font-size: 0.875rem;">
                                Ask the bill creator for the invitation code to join their bill.
                            </small>
                        </div>

                        <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                            <button type="submit" class="btn btn-primary">Join Bill</button>
                            <a href="dashboard.php" class="btn btn-outline">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Auto-uppercase invitation code
        document.getElementById('invitation_code').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });

        // Auto-focus input
        document.getElementById('invitation_code').focus();
    </script>
</body>
</html>
