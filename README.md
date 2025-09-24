# SplitWise - Bill Splitting Application

A modern web application for splitting bills and managing shared expenses with friends, built with PHP and MySQL.

## Features

- 🏠 **Landing Page** - Modern hero section with feature overview
- 🔐 **User Authentication** - Secure login and registration system
- 📋 **Bill Management** - Create, view, edit, and archive bills
- 💰 **Expense Tracking** - Add and track individual expenses
- 👥 **Participant Management** - Invite friends via invitation codes
- 🏷️ **Categories** - Organize bills with predefined categories
- 📁 **Archive System** - View settled and archived bills
- 👤 **Profile Management** - Update personal information
- 📱 **Responsive Design** - Works on desktop and mobile devices

## Screenshots

The application includes:
- Modern landing page with gradient hero section
- Clean dashboard with bill overview
- Detailed bill view with expense tracking
- User-friendly authentication forms
- Category management system
- Archive page for completed bills

## Installation

### Prerequisites

- XAMPP, WAMP, or similar PHP development environment
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser

### Setup Instructions

1. **Clone/Download the Project**
   ```bash
   # Place the project files in your web server directory
   # For XAMPP: C:\xampp\htdocs\jameswithdatabase\
   # For WAMP: C:\wamp64\www\jameswithdatabase\
   ```

2. **Database Setup**
   - Start your MySQL server (through XAMPP/WAMP control panel)
   - Open phpMyAdmin or MySQL command line
   - Import the database structure:
     ```sql
     # Run the contents of database.sql file
     # This will create the database and sample data
     ```

3. **Configuration**
   - Open `config/database.php`
   - Update database credentials if needed:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', ''); // Your MySQL password
     define('DB_NAME', 'splitwise_db');
     ```

4. **File Permissions**
   - Ensure your web server has read/write permissions to the project directory

5. **Access the Application**
   - Open your browser and navigate to:
     ```
     http://localhost/jameswithdatabase/
     ```

## Default Login Credentials

The database comes with sample users for testing:

- **Email:** john@example.com | **Password:** password
- **Email:** jane@example.com | **Password:** password

## Usage Guide

### Getting Started

1. **Visit the Landing Page** - Browse the features and get started
2. **Register an Account** - Create your personal account
3. **Create Your First Bill** - Add a bill name, category, and invite participants
4. **Add Expenses** - Track individual expenses within the bill
5. **Invite Friends** - Share invitation codes for easy joining
6. **Settle Bills** - Mark bills as settled when expenses are resolved

### Key Features

- **Bill Creation**: Create bills with custom names and categories
- **Expense Management**: Add, view, and delete expenses
- **Participant System**: Invite friends using unique invitation codes
- **Automatic Calculations**: Equal splitting among all participants
- **Status Tracking**: Active, settled, and archived bill states
- **Profile Management**: Update personal information and passwords

## File Structure

```
jameswithdatabase/
├── assets/
│   └── css/
│       └── style.css          # Main stylesheet
├── config/
│   └── database.php           # Database configuration
├── includes/
│   └── auth.php              # Authentication functions
├── index.php                 # Landing page
├── login.php                 # Login page
├── register.php              # Registration page
├── dashboard.php             # Main dashboard
├── bill.php                  # Individual bill view
├── archive.php               # Archived bills
├── profile.php               # User profile
├── join-bill.php             # Join bill with invitation code
├── create-bill.php           # Bill creation handler
├── add-expense.php           # Add expense handler
├── delete-expense.php        # Delete expense handler
├── settle-bill.php           # Settle bill handler
├── archive-bill.php          # Archive bill handler
├── delete-bill.php           # Delete bill handler
├── restore-bill.php          # Restore bill handler
├── logout.php                # Logout handler
├── database.sql              # Database structure and sample data
└── README.md                 # This file
```

## Database Schema

### Main Tables

- **users** - User account information
- **categories** - Bill categories (Food, Transportation, etc.)
- **bills** - Main bill information
- **bill_participants** - Links users to bills
- **expenses** - Individual expense items
- **expense_splits** - How expenses are split among participants

## Customization

### Adding New Categories

1. Insert into the `categories` table:
   ```sql
   INSERT INTO categories (name, icon, color, is_default) 
   VALUES ('Custom Category', '🎯', '#ff6b6b', FALSE);
   ```

### Styling Changes

- Modify `assets/css/style.css` for visual customizations
- The design uses CSS Grid and Flexbox for responsive layouts
- Color scheme is based on modern gradients and clean typography

## Security Features

- Password hashing using PHP's `password_hash()`
- SQL injection prevention with prepared statements
- Session-based authentication
- Access control for bill operations
- CSRF protection through proper form handling

## Browser Support

- Chrome (recommended)
- Firefox
- Safari
- Edge
- Mobile browsers (responsive design)

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check MySQL service is running
   - Verify database credentials in `config/database.php`
   - Ensure database exists and is properly imported

2. **Page Not Found**
   - Check web server is running
   - Verify correct URL path
   - Ensure files are in correct directory

3. **Permission Errors**
   - Check file permissions
   - Ensure web server can read PHP files

4. **Styling Issues**
   - Clear browser cache
   - Check CSS file path is correct
   - Verify web server can serve static files

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Verify your PHP and MySQL versions meet requirements
3. Check browser console for JavaScript errors
4. Review server error logs

## License

This project is created for educational and demonstration purposes.

---

**Enjoy using SplitWise for managing your shared expenses!** 💰✨
