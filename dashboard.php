<?php
require_once 'includes/auth.php';

// Ensure user is logged in
requireAuth();

$user = getCurrentUser();
$pdo = getDBConnection();

// Get user's active bills
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
    WHERE b.status = 'active' 
    AND (b.creator_id = ? OR b.id IN (SELECT bill_id FROM bill_participants WHERE user_id = ?))
    GROUP BY b.id
    ORDER BY b.created_at DESC
");
$stmt->execute([$user['id'], $user['id']]);
$bills = $stmt->fetchAll();

// Get categories for the modal
$stmt = $pdo->prepare("SELECT * FROM categories ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Bills - SplitWise</title>
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
                <li><a href="dashboard.php" class="active">📋 Bills</a></li>
                <li><a href="archive.php">📁 Archive</a></li>
                <li><a href="profile.php">👤 Profile</a></li>
                <li><a href="logout.php">🚪 Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Your Bills</h1>
                    <p class="page-subtitle">Manage and track your shared expenses</p>
                </div>
                <div style="display: flex; gap: 0.75rem; align-items: center;">
                    <button onclick="openCategoriesModal()" class="btn btn-outline">Categories</button>
                    <button onclick="openCreateBillModal()" class="btn btn-primary">Create Bill</button>
                </div>
            </div>

            <!-- Bills Grid -->
            <div class="bills-grid">
                <?php if (empty($bills)): ?>
                    <div style="text-align: center; padding: 4rem; color: #6b7280;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📋</div>
                        <h3>No Active Bills</h3>
                        <p>Create your first bill to start splitting expenses with friends.</p>
                        <button onclick="openCreateBillModal()" class="btn btn-primary mt-4">Create Your First Bill</button>
                    </div>
                <?php else: ?>
                    <?php foreach ($bills as $bill): ?>
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
                                    <span class="bill-status active">active</span>
                                    <button class="bill-action" onclick="viewBill(<?php echo $bill['id']; ?>)" title="View Bill">👁️</button>
                                    <button class="bill-action" onclick="editBill(<?php echo $bill['id']; ?>)" title="Edit Bill">✏️</button>
                                    <button class="bill-action" onclick="deleteBill(<?php echo $bill['id']; ?>)" title="Delete Bill">🗑️</button>
                                    <button class="bill-action" onclick="archiveBill(<?php echo $bill['id']; ?>)" title="Archive Bill">📁</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Create Bill Modal -->
    <div id="createBillModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Create New Bill</h3>
                <button class="modal-close" onclick="closeCreateBillModal()">✕</button>
            </div>
            
            <form id="createBillForm" method="POST" action="create-bill.php">
                <div class="form-group">
                    <label for="bill_name">Bill Name</label>
                    <input type="text" id="bill_name" name="bill_name" class="form-control" placeholder="Enter bill name..." required>
                </div>

                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" class="form-control" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>">
                                <?php echo htmlspecialchars($category['icon'] . ' ' . $category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="invitation_code">Invitation Code</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="text" id="invitation_code" name="invitation_code" class="form-control" value="<?php echo generateInvitationCode(); ?>" readonly>
                        <button type="button" class="btn btn-outline" onclick="generateNewCode()">🔄</button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Add Participants</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="email" id="participant_email" class="form-control" placeholder="Email or username...">
                        <button type="button" class="btn btn-primary" onclick="addParticipant()">+</button>
                    </div>
                    <div id="participantsList" style="margin-top: 0.5rem;"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeCreateBillModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Bill</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Categories Modal -->
    <div id="categoriesModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Manage Categories</h3>
                <button class="modal-close" onclick="closeCategoriesModal()">✕</button>
            </div>
            
            <div style="max-height: 400px; overflow-y: auto;">
                <?php foreach ($categories as $category): ?>
                    <div class="category-item">
                        <div class="category-icon" style="background: <?php echo htmlspecialchars($category['color']); ?>;">
                            <?php echo htmlspecialchars($category['icon']); ?>
                        </div>
                        <div class="category-info">
                            <div class="category-name"><?php echo htmlspecialchars($category['name']); ?></div>
                            <?php if ($category['is_default']): ?>
                                <span class="category-default">Default</span>
                            <?php endif; ?>
                        </div>
                        <div class="category-actions">
                            <button class="bill-action" onclick="editCategory(<?php echo $category['id']; ?>)">✏️</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary">+ Add Category</button>
                <button type="button" class="btn btn-outline" onclick="closeCategoriesModal()">Close</button>
            </div>
        </div>
    </div>

    <script>
        // Modal functions
        function openCreateBillModal() {
            document.getElementById('createBillModal').classList.add('active');
        }

        function closeCreateBillModal() {
            document.getElementById('createBillModal').classList.remove('active');
            document.getElementById('createBillForm').reset();
            document.getElementById('participantsList').innerHTML = '';
        }

        function openCategoriesModal() {
            document.getElementById('categoriesModal').classList.add('active');
        }

        function closeCategoriesModal() {
            document.getElementById('categoriesModal').classList.remove('active');
        }

        // Generate new invitation code
        function generateNewCode() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let code = '';
            for (let i = 0; i < 9; i++) {
                code += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            document.getElementById('invitation_code').value = code;
        }

        // Participant management
        let participants = [];

        function addParticipant() {
            const email = document.getElementById('participant_email').value.trim();
            if (email && !participants.includes(email)) {
                participants.push(email);
                updateParticipantsList();
                document.getElementById('participant_email').value = '';
            }
        }

        function removeParticipant(email) {
            participants = participants.filter(p => p !== email);
            updateParticipantsList();
        }

        function updateParticipantsList() {
            const list = document.getElementById('participantsList');
            list.innerHTML = participants.map(email => 
                `<div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem; background: #f9fafb; border-radius: 6px; margin-top: 0.5rem;">
                    <span>${email}</span>
                    <button type="button" onclick="removeParticipant('${email}')" style="background: none; border: none; color: #ef4444; cursor: pointer;">✕</button>
                </div>`
            ).join('');
        }

        // Bill actions
        function viewBill(id) {
            window.location.href = `bill.php?id=${id}`;
        }

        function editBill(id) {
            window.location.href = `bill.php?id=${id}&edit=1`;
        }

        function deleteBill(id) {
            if (confirm('Are you sure you want to delete this bill? This action cannot be undone.')) {
                window.location.href = `delete-bill.php?id=${id}`;
            }
        }

        function archiveBill(id) {
            if (confirm('Archive this bill? You can find it in the Archive section later.')) {
                window.location.href = `archive-bill.php?id=${id}`;
            }
        }

        // Close modals when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                e.target.classList.remove('active');
            }
        });

        // Handle participant email input
        document.getElementById('participant_email').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addParticipant();
            }
        });

        // Handle form submission
        document.getElementById('createBillForm').addEventListener('submit', function(e) {
            // Add participants to form data
            const form = this;
            participants.forEach((email, index) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `participants[${index}]`;
                input.value = email;
                form.appendChild(input);
            });
        });
    </script>
</body>
</html>
