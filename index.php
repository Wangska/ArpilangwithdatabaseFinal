<?php
require_once 'includes/auth.php';

// Redirect to dashboard if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SplitWise - Split Bills Effortlessly</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <a href="index.php" class="logo">
                <div class="logo-icon">📊</div>
                SplitWise
            </a>
            <nav class="nav-links">
                <a href="#features">Features</a>
                <a href="login.php">Log In</a>
                <a href="register.php" class="btn btn-primary">Get Started</a>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Split Bills <span class="highlight">Effortlessly</span></h1>
            <p>The modern way to share expenses with friends. Track spending, split costs fairly, and settle up seamlessly. No more awkward money conversations.</p>
            
            <div class="hero-buttons">
                <a href="register.php" class="btn btn-primary btn-lg">Start Splitting Bills</a>
                <a href="login.php" class="btn btn-secondary btn-lg">Sign In</a>
            </div>

            <div class="hero-image">
                <svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
                    <!-- Phone mockup -->
                    <rect x="50" y="20" width="200" height="260" rx="20" fill="white" stroke="#e5e7eb" stroke-width="2"/>
                    <rect x="60" y="40" width="180" height="220" rx="10" fill="#f8fafc"/>
                    
                    <!-- Header -->
                    <rect x="70" y="50" width="160" height="30" rx="5" fill="white"/>
                    <text x="150" y="70" text-anchor="middle" font-family="Inter" font-size="12" font-weight="600" fill="#1f2937">SplitWise Bills</text>
                    
                    <!-- Bill items -->
                    <rect x="70" y="90" width="160" height="40" rx="8" fill="white"/>
                    <circle cx="85" cy="110" r="8" fill="#ef4444"/>
                    <text x="100" y="108" font-family="Inter" font-size="10" font-weight="600" fill="#1f2937">Dinner at Restaurant</text>
                    <text x="100" y="120" font-family="Inter" font-size="8" fill="#6b7280">3 participants • $85.20</text>
                    
                    <rect x="70" y="140" width="160" height="40" rx="8" fill="white"/>
                    <circle cx="85" cy="160" r="8" fill="#06b6d4"/>
                    <text x="100" y="158" font-family="Inter" font-size="10" font-weight="600" fill="#1f2937">Grocery Shopping</text>
                    <text x="100" y="170" font-family="Inter" font-size="8" fill="#6b7280">2 participants • $156.75</text>
                    
                    <rect x="70" y="190" width="160" height="40" rx="8" fill="white"/>
                    <circle cx="85" cy="210" r="8" fill="#8b5cf6"/>
                    <text x="100" y="208" font-family="Inter" font-size="10" font-weight="600" fill="#1f2937">Weekend Trip</text>
                    <text x="100" y="220" font-family="Inter" font-size="8" fill="#6b7280">4 participants • $320.50</text>
                    
                    <!-- People illustrations -->
                    <circle cx="280" cy="80" r="25" fill="#fbbf24"/>
                    <circle cx="280" cy="80" r="15" fill="white"/>
                    <text x="280" y="85" text-anchor="middle" font-family="Inter" font-size="14">👤</text>
                    
                    <circle cx="330" cy="120" r="25" fill="#10b981"/>
                    <circle cx="330" cy="120" r="15" fill="white"/>
                    <text x="330" y="125" text-anchor="middle" font-family="Inter" font-size="14">👤</text>
                    
                    <!-- Connection lines -->
                    <path d="M 250 110 Q 270 100 280 80" stroke="#4f46e5" stroke-width="2" fill="none" stroke-dasharray="5,5"/>
                    <path d="M 250 110 Q 290 115 330 120" stroke="#4f46e5" stroke-width="2" fill="none" stroke-dasharray="5,5"/>
                    
                    <!-- Decorative elements -->
                    <circle cx="320" cy="50" r="3" fill="#4f46e5" opacity="0.5"/>
                    <circle cx="350" cy="200" r="3" fill="#10b981" opacity="0.5"/>
                    <circle cx="30" cy="150" r="3" fill="#ef4444" opacity="0.5"/>
                </svg>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="text-center">
                <h2 class="page-title">Everything You Need to Split Bills</h2>
                <p class="page-subtitle">Powerful features designed to make sharing expenses simple, fair, and stress-free.</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon smart">
                        📊
                    </div>
                    <h3>Smart Bill Splitting</h3>
                    <p>Automatically calculate and split expenses among friends with precision and ease. Handle complex splits, different amounts, and multiple participants effortlessly.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon invite">
                        👥
                    </div>
                    <h3>Guest Invitations</h3>
                    <p>Invite friends via email or share invitation codes for quick access to bills. No need for everyone to create accounts immediately.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon premium">
                        👑
                    </div>
                    <h3>Premium Features</h3>
                    <p>Upgrade to Premium for unlimited bills, advanced analytics, and priority support. Take your expense tracking to the next level.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <h2>Ready to Start Splitting Bills?</h2>
            <p>Join thousands of users who have simplified their shared expenses. Create your account today and start splitting bills the smart way.</p>
            
            <div class="cta-buttons">
                <a href="register.php" class="btn btn-primary btn-lg">Create Free Account</a>
                <a href="login.php" class="btn btn-secondary btn-lg">Sign In</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="logo">
                <div class="logo-icon">📊</div>
                SplitWise
            </div>
            <p>&copy; 2024 SplitWise. Making bill splitting simple and fair.</p>
        </div>
    </footer>

    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Mobile menu toggle (if needed)
        const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
        if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', function() {
                document.querySelector('.nav-links').classList.toggle('active');
            });
        }
    </script>
</body>
</html>
