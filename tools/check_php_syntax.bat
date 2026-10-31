@echo off
REM Checks every PHP file for syntax errors using XAMPP's PHP.
REM Double-click this file, or run it from the project folder.
set PHP=C:\xampp\php\php.exe
cd /d "%~dp0.."
set ERRORS=0
for /r %%f in (*.php) do (
    "%PHP%" -l "%%f" | findstr /v /c:"No syntax errors" && set ERRORS=1
)
if "%ERRORS%"=="0" (echo.& echo All PHP files: no syntax errors.) else (echo.& echo Errors found above.)
pause
