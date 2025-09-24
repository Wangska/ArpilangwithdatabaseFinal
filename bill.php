<?php
require_once 'includes/auth.php';

requireAuth();

$user = getCurrentUser();
$pdo = getDBConnection();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$bill_id = $_GET['id'];

// Get bill details with category
$stmt = $pdo->prepare("
    SELECT 
        b.*,
        c.name as category_name,
        c.icon as category_icon,
        c.color as category_color,
        u.first_name as creator_first_name,
        u.last_name as creator_last_name
    FROM bills b
    LEFT JOIN categories c ON b.category_id = c.id
    LEFT JOIN users u ON b.creator_id = u.id
    WHERE b.id = ? AND (b.creator_id = ? OR b.id IN (
        SELECT bill_id FROM bill_participants WHERE user_id = ?
    ))
");
$stmt->execute([$bill_id, $user['id'], $user['id']]);
$bill = $stmt->fetch();

if (!$bill) {
    header('Location: dashboard.php?error=bill_not_found');
    exit();
}

// Get participants
$stmt = $pdo->prepare("
    SELECT 
        u.id,
        u.first_name,
        u.last_name,
        u.nickname,
        u.email,
        bp.joined_at
    FROM bill_participants bp
    JOIN users u ON bp.user_id = u.id
    WHERE bp.bill_id = ?
    ORDER BY bp.joined_at
");
$stmt->execute([$bill_id]);
$participants = $stmt->fetchAll();

// Get expenses
$stmt = $pdo->prepare("
    SELECT 
        e.*,
        u.first_name as paid_by_first_name,
        u.last_name as paid_by_last_name
    FROM expenses e
    JOIN users u ON e.paid_by = u.id
    WHERE e.bill_id = ?
    ORDER BY e.created_at DESC
");
$stmt->execute([$bill_id]);
$expenses = $stmt->fetchAll();

// Calculate total amount
$total_amount = array_sum(array_column($expenses, 'amount'));

// Update bill total if needed
if ($total_amount != $bill['total_amount']) {
    $stmt = $pdo->prepare("UPDATE bills SET total_amount = ? WHERE id = ?");
    $stmt->execute([$total_amount, $bill_id]);
    $bill['total_amount'] = $total_amount;
}

$success = '';
$error = '';

if (isset($_GET['created'])) {
    $success = 'Bill created successfully!';
} elseif (isset($_GET['expense_added'])) {
    $success = 'Expense added successfully!';
} elseif (isset($_GET['expense_deleted'])) {
    $success = 'Expense deleted successfully!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($bill['name']); ?> - SplitWise</title>
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
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <a href="dashboard.php" style="color: #6b7280; text-decoration: none;">← Back to Bills</a>
                    </div>
                    <h1 class="page-title"><?php echo htmlspecialchars($bill['name']); ?></h1>
                    <div style="display: flex; align-items: center; gap: 1rem; margin-top: 0.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div class="bill-icon" style="background: <?php echo htmlspecialchars($bill['category_color'] ?? '#6366f1'); ?>; width: 24px; height: 24px; border-radius: 6px; font-size: 0.875rem;">
                                <?php echo htmlspecialchars($bill['category_icon'] ?? '📋'); ?>
                            </div>
                            <span style="color: #6b7280;"><?php echo htmlspecialchars($bill['category_name'] ?? 'Uncategorized'); ?></span>
                        </div>
                        <span class="bill-status <?php echo $bill['status']; ?>"><?php echo $bill['status']; ?></span>
                        <span style="color: #6b7280;">Code: <?php echo htmlspecialchars($bill['invitation_code']); ?></span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button onclick="openAddExpenseModal()" class="btn btn-primary">+ Add Expense</button>
                    <?php if ($bill['creator_id'] == $user['id']): ?>
                        <button onclick="openInviteModal()" class="btn btn-outline">👥 Invite</button>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($success): ?>
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 300px; gap: 2rem;">
                <!-- Left Column - Expenses -->
                <div>
                    <div class="bill-card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                            <h3 style="font-size: 1.25rem; font-weight: 600;">Expenses</h3>
                            <div style="font-size: 1.5rem; font-weight: 700; color: #059669;">
                                $<?php echo number_format($total_amount, 2); ?>
                            </div>
                        </div>

                        <?php if (empty($expenses)): ?>
                            <div style="text-align: center; padding: 2rem; color: #6b7280;">
                                <div style="font-size: 2rem; margin-bottom: 1rem;">💳</div>
                                <h3>No Expenses Yet</h3>
                                <p>Add your first expense to start tracking costs.</p>
                                <button onclick="openAddExpenseModal()" class="btn btn-primary mt-4">Add First Expense</button>
                            </div>
                        <?php else: ?>
                            <div style="space-y: 1rem;">
                                <?php foreach ($expenses as $expense): ?>
                                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f9fafb; border-radius: 8px; margin-bottom: 1rem;">
                                        <div>
                                            <div style="font-weight: 600; color: #1f2937;"><?php echo htmlspecialchars($expense['description']); ?></div>
                                            <div style="color: #6b7280; font-size: 0.875rem;">
                                                Paid by <?php echo htmlspecialchars($expense['paid_by_first_name'] . ' ' . $expense['paid_by_last_name']); ?>
                                                • <?php echo date('M j, Y', strtotime($expense['created_at'])); ?>
                                            </div>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 1rem;">
                                            <div style="font-size: 1.125rem; font-weight: 600; color: #059669;">
                                                $<?php echo number_format($expense['amount'], 2); ?>
                                            </div>
                                            <?php if ($expense['paid_by'] == $user['id'] || $bill['creator_id'] == $user['id']): ?>
                                                <button onclick="deleteExpense(<?php echo $expense['id']; ?>)" class="bill-action" title="Delete Expense">🗑️</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right Column - Participants & Summary -->
                <div>
                    <!-- Participants -->
                    <div class="bill-card" style="margin-bottom: 1.5rem;">
                        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem;">
                            Participants (<?php echo count($participants); ?>)
                        </h3>
                        
                        <?php foreach ($participants as $participant): ?>
                            <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0;">
                                <div style="width: 32px; height: 32px; background: #4f46e5; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                    <?php echo strtoupper(substr($participant['first_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div style="font-weight: 500;">
                                        <?php echo htmlspecialchars($participant['first_name'] . ' ' . $participant['last_name']); ?>
                                        <?php if ($participant['id'] == $bill['creator_id']): ?>
                                            <span style="color: #059669; font-size: 0.75rem;">(Owner)</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($participant['nickname']): ?>
                                        <div style="color: #6b7280; font-size: 0.875rem;"><?php echo htmlspecialchars($participant['nickname']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Bill Summary -->
                    <div class="bill-card">
                        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem;">Summary</h3>
                        
                        <div style="space-y: 0.5rem;">
                            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0;">
                                <span>Total Amount:</span>
                                <span style="font-weight: 600;">$<?php echo number_format($total_amount, 2); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0;">
                                <span>Per Person:</span>
                                <span style="font-weight: 600;">$<?php echo $total_amount > 0 ? number_format($total_amount / count($participants), 2) : '0.00'; ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-top: 1px solid #e5e7eb; margin-top: 1rem;">
                                <span>Created:</span>
                                <span><?php echo date('M j, Y', strtotime($bill['created_at'])); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0;">
                                <span>Creator:</span>
                                <span><?php echo htmlspecialchars($bill['creator_first_name'] . ' ' . $bill['creator_last_name']); ?></span>
                            </div>
                        </div>

                        <?php if ($bill['status'] === 'active'): ?>
                            <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                                <button onclick="settleBill()" class="btn btn-primary w-full">Mark as Settled</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Expense Modal -->
    <div id="addExpenseModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add Expense</h3>
                <button class="modal-close" onclick="closeAddExpenseModal()">✕</button>
            </div>
            
            <form method="POST" action="add-expense.php">
                <input type="hidden" name="bill_id" value="<?php echo $bill_id; ?>">
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <input type="text" id="description" name="description" class="form-control" placeholder="What was this expense for?" required>
                </div>

                <div class="form-group">
                    <label for="amount">Amount ($)</label>
                    <input type="number" id="amount" name="amount" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                </div>

                <div class="form-group">
                    <label for="paid_by">Paid by</label>
                    <select id="paid_by" name="paid_by" class="form-control" required>
                        <?php foreach ($participants as $participant): ?>
                            <option value="<?php echo $participant['id']; ?>" <?php echo $participant['id'] == $user['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($participant['first_name'] . ' ' . $participant['last_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeAddExpenseModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Expense</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Invite Modal -->
    <div id="inviteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Invite Participants</h3>
                <button class="modal-close" onclick="closeInviteModal()">✕</button>
            </div>
            
            <div style="margin-bottom: 1rem;">
                <label>Invitation Code</label>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($bill['invitation_code']); ?>" readonly>
                    <button class="btn btn-outline" onclick="copyInvitationCode()">📋</button>
                </div>
                <small style="color: #6b7280;">Share this code with friends to let them join this bill.</small>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeInviteModal()">Close</button>
            </div>
        </div>
    </div>

    <script>
        function openAddExpenseModal() {
            document.getElementById('addExpenseModal').classList.add('active');
        }

        function closeAddExpenseModal() {
            document.getElementById('addExpenseModal').classList.remove('active');
        }

        function openInviteModal() {
            document.getElementById('inviteModal').classList.add('active');
        }

        function closeInviteModal() {
            document.getElementById('inviteModal').classList.remove('active');
        }

        function copyInvitationCode() {
            const codeInput = document.querySelector('#inviteModal input[readonly]');
            codeInput.select();
            document.execCommand('copy');
            alert('Invitation code copied to clipboard!');
        }

        function deleteExpense(id) {
            if (confirm('Are you sure you want to delete this expense?')) {
                window.location.href = `delete-expense.php?id=${id}&bill_id=<?php echo $bill_id; ?>`;
            }
        }

        function settleBill() {
            if (confirm('Mark this bill as settled? This will move it to your archive.')) {
                window.location.href = `settle-bill.php?id=<?php echo $bill_id; ?>`;
            }
        }

        // Close modals when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                e.target.classList.remove('active');
            }
        });
    </script>
</body>
</html>
