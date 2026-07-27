@echo off
REM Start XAMPP's MariaDB on port 3307 for Quiz Master.
REM
REM Why 3307? A separate MySQL 8.0 Windows service already owns the default
REM port 3306 on this machine, and stopping it needs admin rights. Running
REM MariaDB on 3307 sidesteps that entirely. includes/config.php points at
REM 127.0.0.1;port=3307 to match.
REM
REM Run this once per reboot (leave the window closed - it runs hidden),
REM then start the app with:  php -S localhost:8000

echo Starting MariaDB on port 3307...
start "" /B "C:\xampp\mysql\bin\mysqld.exe" --defaults-file=C:\xampp\mysql\bin\my.ini --port=3307 --standalone

timeout /t 3 /nobreak >nul
echo.
echo If no error appeared, MariaDB is running on 127.0.0.1:3307.
echo Now run:  php -S localhost:8000
echo Then open http://localhost:8000/
