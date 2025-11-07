@echo off 
cd /d C:\xampp\htdocs\feedback-system 
echo [%07-11-2025% %12:59:40.21%] Starting email scheduler... 
C:\xampp\php\php.exe send_scheduled_emails.php 
echo [%07-11-2025% %12:59:40.24%] Completed. 
pause 
