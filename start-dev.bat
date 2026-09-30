@echo off
echo ===================================================
echo   POLINELA AGRO - MULTI-STORE E-COMMERCE
echo   Frontend: React + Vite (localhost:5173)
echo   Backend:  Laravel 12 REST API (localhost:8000)
echo ===================================================
echo.

echo Menjalankan Backend Laravel...
start "Polinela Agro - Laravel Backend" cmd /k "cd backend && php artisan serve --port=8000"

echo Menjalankan Frontend React Vite...
start "Polinela Agro - React Frontend" cmd /k "cd frontend && npm run dev"

echo.
echo Server telah dijalankan!
echo - Frontend: http://localhost:5173
echo - Backend API: http://localhost:8000/api
echo.
pause
