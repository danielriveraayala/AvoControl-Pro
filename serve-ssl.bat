@echo off
echo Starting AvoControl Pro with SSL/HTTPS...
echo.
echo SSL Certificate: ssl/avocontrol.crt
echo SSL Private Key: ssl/avocontrol.key
echo.
echo Access your application at: https://127.0.0.1:8443
echo.

php -S 127.0.0.1:8443 -t public server.php --ssl-cert=ssl/avocontrol.crt --ssl-key=ssl/avocontrol.key

pause