# SSC Quiz App

A comprehensive quiz application for SSC CGL, CHSL, and MTS exam preparation focusing on Mathematics topics.

## Features

### Quiz Features
- **Multiple Exam Support**: CGL, CHSL, MTS
- **Math Topics**: Number System, Percentage, Profit & Loss, Ratio & Proportion, Average, Time & Work, Time & Distance, Simple Interest, LCM & HCF, Algebra, Geometry, Trigonometry, Mensuration
- **Quiz Features**:
  - 20 questions per quiz
  - 20-minute timer
  - Instant results with explanations
  - Answer review
  - Progress tracking

### Secure Admin Panel
- **Authentication**: Username + password with bcrypt hashing
- **Role-based Access**: Super Admin and Admin roles
- **CRUD Operations**: Full management for subjects, topics, questions, quiz sets
- **Import/Export**: CSV and JSON import/export
- **Audit Logging**: Track all admin actions
- **Status Toggle**: Enable/disable questions and quiz sets
- **Protection**: Session management, rate limiting, CSRF protection

## Tech Stack

- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Backend**: PHP
- **Database**: MySQL

## Setup Instructions

### 1. Database Setup

1. Install XAMPP or WAMP (or any PHP/MySQL environment)
2. Start Apache and MySQL services
3. Open phpMyAdmin (http://localhost/phpmyadmin)
4. Create a new database named `ssc_quiz_db`
5. Import both SQL files in order:
   - First: `database.sql` (original schema + sample questions)
   - Second: `admin_database.sql` (admin tables, subjects, topics, quiz sets)

Or run the SQL directly in phpMyAdmin:

```sql
-- First run database.sql contents
-- Then run admin_database.sql contents
```

### 2. Configure Database

Edit `config.php` if you need to change database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ssc_quiz_db');
```

### 3. Run the Application

1. Place all files in your web server's htdocs folder (e.g., `C:\xampp\htdocs\ssc_quiz`)
2. Open browser:
   - Quiz App: `http://localhost/ssc_quiz/index.html`
   - Admin Panel: `http://localhost/ssc_quiz/secure_admin.php`

### 4. Default Admin Credentials

After importing the database, use these credentials:

- **Username**: admin
- **Password**: Admin@123

> ⚠️ **Security Note**: Change the default password after first login!

To create additional admins or change password, use the admin panel or directly insert into the database with bcrypt hash.

## File Structure

```
ssc_quiz/
├── index.html           # Main quiz interface
├── style.css           # Quiz styling
├── script.js           # Quiz frontend logic
├── api.php             # Quiz API endpoints
├── config.php          # Database configuration
├── database.sql        # Original database schema + sample questions
├── admin.php           # Old admin panel (legacy)
├── admin_api.php       # Old admin API (legacy)
├── secure_admin.php    # New secure admin panel (Bootstrap UI)
├── admin_frontend.js   # Admin panel JavaScript
├── secure_admin_api.php # Secure admin API with auth
├── admin_auth.php      # Authentication system
├── admin_database.sql  # Admin database schema
└── README.md           # This file
```

## Admin Panel Features

### Dashboard
- Total questions, subjects, quiz sets count
- Active vs inactive status
- Recent quiz attempts
- 24-hour activity stats

### Subjects Management
- Add/Edit/Delete subjects
- Toggle active/inactive status
- Assign icon and display order
- View topic count per subject

### Topics Management
- Add/Edit/Delete topics
- Link to subjects
- Toggle active/inactive status
- Set display order

### Questions Management
- Add/Edit/Delete questions
- Filter by exam type and difficulty
- Toggle active/inactive status
- Set marks per question
- Add explanations

### Quiz Sets Management
- Create custom quiz sets
- Set time limit, question count, negative marking
- Link to subjects
- Toggle active/inactive status

### Import/Export
- Import questions from JSON or CSV
- Export questions to JSON or CSV

### Audit Logs
- Track all admin actions
- View user, action, table, IP address, timestamp

## API Endpoints

### Public (No Auth)
- `?action=login` - Admin login
- `?action=logout` - Admin logout
- `?action=check_auth` - Check authentication status
- `?action=dashboard_stats` - Get dashboard statistics

### Protected (Requires Auth)
- Subjects: get_subjects, add_subject, update_subject, delete_subject, toggle_subject
- Topics: get_topics, add_topic, update_topic, delete_topic, toggle_topic
- Questions: get_questions, add_question, update_question, delete_question, toggle_question
- Quiz Sets: get_quiz_sets, add_quiz_set, update_quiz_set, delete_quiz_set, toggle_quiz_set
- Import/Export: export_questions, import_questions
- Audit Logs: get_audit_logs
- Results: get_quiz_results

## Security Features

1. **Password Hashing**: Bcrypt with cost factor 12
2. **Session Management**: Token-based with expiry
3. **Rate Limiting**: Login attempt tracking
4. **SQL Injection Prevention**: Prepared statements
5. **XSS Protection**: Output encoding
6. **CSRF Protection**: Token validation
7. **Role-based Access**: Super Admin vs Admin

## Troubleshooting

### MySQL Connection Error
- Check MySQL service is running
- Verify credentials in config.php
- Ensure database exists

### Session Issues
- Clear browser cookies
- Check PHP session configuration

### Import Errors
- Ensure CSV has correct headers
- Validate JSON format

## License

Free to use for educational purposes.
