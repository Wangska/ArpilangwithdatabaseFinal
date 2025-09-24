<?php
require_once 'includes/auth.php';

// Ensure user is logged in
requireAuth();

$user = getCurrentUser();
$pdo = getDBConnection();

// Get user's archived/settled bills
$stmt = $pdo->prepare("
    SELECT 
        b.*,
        c.name as category_name,
        c.icon as category_icon,
        c.color as category_color,
        COUNT(DISTINCT bp.user_id) as participant_count
    FROM bills b
    LEFT JOIN categories c ON b.category_id = c.id
    LEFT JOIN bill_participants bp ON b.id = bp.bill_id
    WHERE b.status IN ('settled', 'archived') 
    AND (b.creator_id = ? OR b.id IN (SELECT bill_id FROM bill_participants WHERE user_id = ?))
    GROUP BY b.id
    ORDER BY b.updated_at DESC
");
$stmt->execute([$user['id'], $user['id']]);
$archived_bills = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Bills - SplitWise</title>
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
                <li><a href="archive.php" class="active">📁 Archive</a></li>
                <li><a href="profile.php">👤 Profile</a></li>
                <li><a href="logout.php">🚪 Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Archived Bills</h1>
                    <p class="page-subtitle">View your completed and archived bills</p>
                </div>
            </div>

            <!-- Bills Grid -->
            <div class="bills-grid">
                <?php if (empty($archived_bills)): ?>
                    <div style="text-align: center; padding: 4rem; color: #6b7280;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📁</div>
                        <h3>No Archived Bills</h3>
                        <p>Your settled and archived bills will appear here.</p>
                        <a href="dashboard.php" class="btn btn-primary mt-4">Back to Active Bills</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($archived_bills as $bill): ?>
                        <div class="bill-card">
                            <div class="bill-header">
                                <div style="display: flex; align-items: center; gap: 1rem; flex: 1;">
                                    <div class="bill-icon" style="background: <?php echo htmlspecialchars($bill['category_color'] ?? '#6366f1'); ?>;">
                                        <?php echo htmlspecialchars($bill['category_icon'] ?? '📋'); ?>
                                    </div>
                                    <div class="bill-info">
                                        <h3 class="bill-title"><?php echo htmlspecialchars($bill['name']); ?></h3>
                                        <div class="bill-details">
                                            <span><?php echo $bill['participant_count']; ?> participants</span>
                                            <span>Total: $<?php echo number_format($bill['total_amount'], 2); ?></span>
                                            <span>Created: <?php echo date('m/d/Y', strtotime($bill['created_at'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="bill-actions">
                                    <span class="bill-status settled"><?php echo $bill['status']; ?></span>
                                    <button class="bill-action" onclick="viewBill(<?php echo $bill['id']; ?>)" title="View Bill">👁️</button>
                                    <?php if ($bill['status'] === 'archived'): ?>
                                        <button class="bill-action" onclick="restoreBill(<?php echo $bill['id']; ?>)" title="Restore Bill">↩️</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Bill actions
        function viewBill(id) {
            window.location.href = `bill.php?id=${id}`;
        }

        function restoreBill(id) {
            if (confirm('Restore this bill to active status?')) {
                window.location.href = `restore-bill.php?id=${id}`;
            }
        }
    </script>
</body>
</html>
