@echo off
echo ====================================
echo  Laravel Performance Optimization
echo ====================================
echo.

echo [1/6] Clearing all caches...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo.

echo [2/6] Caching configuration...
php artisan config:cache
echo.

echo [3/6] Caching routes...
php artisan route:cache
echo.

echo [4/6] Caching views...
php artisan view:cache
echo.

echo [5/6] Caching events...
php artisan event:cache
echo.

echo [6/6] Running optimize...
php artisan optimize
echo.

echo ====================================
echo  Optimization complete!
echo ====================================
echo.
pause
