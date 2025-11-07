@echo off
cd /d C:\xampp\htdocs\feedback-system
echo [%date% %time%] Starting email scheduler... > cron.log
C:\xampp\php\php.exe send_scheduled_emails.php >> cron.log 2>&1
echo [%date% %time%] Completed. >> cron.log
pause