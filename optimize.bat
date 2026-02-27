@echo off
echo ==========================================
echo   Laravel Performance Optimization
echo ==========================================
echo.

echo [1/5] Caching configuration...
php artisan config:cache
echo.

echo [2/5] Caching routes...
php artisan route:cache
echo.

echo [3/5] Caching views...
php artisan view:cache
echo.

echo [4/5] Caching events...
php artisan event:cache
echo.

echo [5/5] Running pending migrations...
php artisan migrate --force
echo.

echo ==========================================
echo   Optimization complete!
echo ==========================================
echo.
echo NOTE: If you change .env, routes, or views,
echo       run this script again.
echo.
pause
