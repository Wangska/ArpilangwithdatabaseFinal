<?php
require_once 'includes/auth.php';

requireAuth();

$user = getCurrentUser();
$pdo = getDBConnection();
$success = '';
$error = '';

// Handle profile update
if ($_POST) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $nickname = trim($_POST['nickname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Check if new password is provided
            if (!empty($new_password)) {
                if (empty($current_password)) {
                    $error = 'Please enter your current password to change it.';
                } else {
                    // Verify current password
                    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                    $stmt->execute([$user['id']]);
                    $stored_password = $stmt->fetchColumn();
                    
                    if (!password_verify($current_password, $stored_password)) {
                        $error = 'Current password is incorrect.';
                    } elseif (strlen($new_password) < 6) {
                        $error = 'New password must be at least 6 characters long.';
                    } else {
                        // Update with new password
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, nickname = ?, email = ?, password = ? WHERE id = ?");
                        $stmt->execute([$first_name, $last_name, $nickname, $email, $hashed_password, $user['id']]);
                        $success = 'Profile and password updated successfully!';
                    }
                }
            } else {
                // Update without password change
                $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, nickname = ?, email = ? WHERE id = ?");
                $stmt->execute([$first_name, $last_name, $nickname, $email, $user['id']]);
                $success = 'Profile updated successfully!';
            }
            
            if (empty($error)) {
                $pdo->commit();
                // Refresh user data
                $user = getCurrentUser();
            } else {
                $pdo->rollback();
            }
            
        } catch (PDOException $e) {
            $pdo->rollback();
            if (strpos($e->getMessage(), 'email') !== false) {
                $error = 'Email address is already in use.';
            } else {
                $error = 'Failed to update profile. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - SplitWise</title>
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
                <li><a href="profile.php" class="active">👤 Profile</a></li>
                <li><a href="logout.php">🚪 Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Profile Settings</h1>
                    <p class="page-subtitle">Manage your account information</p>
                </div>
            </div>

            <div style="max-width: 600px;">
                <?php if ($error): ?>
                    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <div class="bill-card">
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="nickname">Nickname</label>
                            <input type="text" id="nickname" name="nickname" class="form-control" value="<?php echo htmlspecialchars($user['nickname'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                            <small style="color: #6b7280; font-size: 0.875rem;">Username cannot be changed</small>
                        </div>

                        <h3 style="margin: 2rem 0 1rem; color: #1f2937; font-size: 1.25rem;">Change Password</h3>
                        
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" placeholder="Enter current password to change">
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Enter new password (min. 6 characters)">
                        </div>

                        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="dashboard.php" class="btn btn-outline">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
