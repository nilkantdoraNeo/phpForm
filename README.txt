Feedback System - Documentation
=============================

Prerequisites
------------
1. XAMPP (Version 8.0 or higher)
   - PHP 8.0+
   - MySQL 8.0+
   - Apache Web Server

2. Web Browser (Chrome, Firefox, or Edge)

3. Mail Server Configuration
   - Gmail account with App Password enabled
   - SMTP access enabled

Installation Steps
-----------------
1. Database Setup:
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named 'advanced_feedback_system'
   - Import the SQL files from the sql/ directory in this order:
     a. create_users_table.sql
     b. create_forms_table.sql
     c. create_form_questions_table.sql
     d. create_form_responses_table.sql
     e. create_events_table.sql

2. Project Setup:
   - Place the entire 'feedback-system' folder in:
     C:\xampp\htdocs\feedback-system\

3. Configuration:
   - Open config.php and update the following:
     * Database credentials (if different from default)
     * SMTP settings for email
     * Site URL (if different from http://localhost/feedback-system)

4. Create Admin User:
   - Execute in phpMyAdmin:
     INSERT INTO users (username, email, password, role) 
     VALUES ('admin', 'your-email@example.com', 'your-password', 'admin');

5. File Permissions:
   - Ensure 'email_log.txt' is writable
   - All PHP files should be readable by web server

Accessible URLs
--------------
1. Public URLs:
   - Homepage: http://localhost/feedback-system/
   - Login: http://localhost/feedback-system/login.php
   - Register: http://localhost/feedback-system/register.php
   - Form Submission: http://localhost/feedback-system/form.php?code=[form_code]

2. User URLs (requires login):
   - User Dashboard: http://localhost/feedback-system/index.php
   - Form Submission: http://localhost/feedback-system/form.php?code=[form_code]

3. Admin URLs (requires admin login):
   - Admin Dashboard: http://localhost/feedback-system/admin.php
   - Event Manager: http://localhost/feedback-system/event_manager.php
   - Process Events: http://localhost/feedback-system/process_events.php
   - Form Responses: http://localhost/feedback-system/form_responses.php?form_id=[id]
   - Logout: http://localhost/feedback-system/logout.php

Email Configuration
-----------------
1. Gmail Setup:
   - Enable 2-Factor Authentication
   - Generate App Password
   - Update config.php with:
     * SMTP_HOST: smtp.gmail.com
     * SMTP_PORT: 587
     * SMTP_USERNAME: your-email@gmail.com
     * SMTP_PASSWORD: your-app-password
     * SMTP_FROM_EMAIL: your-email@gmail.com
     * SMTP_FROM_NAME: Your Name

Automated Tasks
--------------
1. Event Processing:
   - Set up Windows Task Scheduler to run:
     c:\xampp\php\php.exe -f "c:\xampp\htdocs\feedback-system\process_events.php"
   - Recommended interval: Every 5 minutes

Project Structure
---------------
/feedback-system/
├── admin.php           # Admin dashboard
├── config.php         # Configuration file
├── event_manager.php  # Event management
├── form.php          # Form submission
├── index.php         # User dashboard
├── login.php         # Login page
├── register.php      # Registration page
├── process_events.php # Event processor
├── style.css         # Main stylesheet
├── admin-nav.css     # Admin navigation styles
├── sql/              # Database schemas
├── email_log.txt     # Email sending logs
└── vendor/           # Dependencies

User Types
----------
1. Regular Users:
   - Can register accounts
   - View assigned forms
   - Submit feedback
   - View their submission history

2. Administrators:
   - Manage all forms and events
   - Create new feedback forms
   - Schedule events
   - View all responses
   - Process events manually
   - Access system logs

Security Notes
------------
1. Password Storage:
   - Passwords are stored as plain text (not recommended for production)
   - Consider enabling password hashing for production use

2. Access Control:
   - Admin routes are protected
   - Form submissions are validated
   - Email verification for form submissions

Troubleshooting
--------------
1. Emails not sending:
   - Check email_log.txt for errors
   - Verify SMTP credentials in config.php
   - Ensure PHP mail function is enabled
   - Check if Gmail App Password is correct

2. Database Errors:
   - Verify MySQL credentials
   - Check table permissions
   - Ensure all tables are created properly

3. Access Issues:
   - Clear browser cache
   - Check file permissions
   - Verify .htaccess settings

Support
-------
For issues or questions:
1. Check email_log.txt for email-related issues
2. Verify database connection in config.php
3. Check PHP error logs in xampp/php/logs/
4. Ensure all prerequisites are properly installed