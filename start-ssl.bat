@echo off
title AvoControl Pro - SSL Server
echo ========================================
echo        AvoControl Pro - SSL Server
echo ========================================
echo.
echo Starting servers...
echo.

:: Iniciar Laravel en background
echo [1] Starting Laravel server (port 8001)...
start /B php artisan serve --port=8001 --host=127.0.0.1

:: Esperar un poco
timeout /t 2 /nobreak >nul

:: Iniciar proxy SSL
echo [2] Starting SSL proxy (port 8443)...
echo.
echo Ready! Access your application at:
echo.
echo   https://127.0.0.1:8443
echo.
echo Note: You may see a security warning about the certificate.
echo Click "Advanced" then "Proceed to 127.0.0.1 (unsafe)" to continue.
echo.
echo Press Ctrl+C to stop both servers
echo.

node ssl-proxy.cjs