@echo off
setlocal enabledelayedexpansion
pushd %~dp0
set root=%~dp0..\..
set logfile=%root%\logs\mysql-init.log

call :ECHO %date% %time% MARS Initialization started
call :ECHO %date% %time%  1/7 Stopping MARS-Apache service...
net stop mars-apache > nul 2>&1
call :ECHO %date% %time%  2/7 Stopping MARS-MySQL service...
net stop mars-mysql >nul 2>&1
call :ECHO %date% %time%  3/7 Removing files...
del /q /f %root%\apache\logs\*.*  >nul 2>&1
rmdir /q /s %root%\mysql\data  >nul 2>&1
call :ECHO %date% %time%  4/7 Creating blank database...
xcopy /seq %root%\mysql\data.default %root%\mysql\data\ > nul 2>&1
call :ECHO %date% %time%  5/7 Starting MARS-MySQL service...
net start mars-mysql  >nul 2>&1
if "%errorlevel%" NEQ "0" call :ECHO %date% %time% Error %errorlevel% starting MARS MySQL service.
call :ECHO %date% %time%  6/7 Creating MARS database...
%root%\mysql\bin\mysql.exe -u root --password= <init.sql >nul
if "%errorlevel%" NEQ "0" call :ECHO %date% %time% Error %errorlevel% creating MARS database.
call :ECHO %date% %time%  7/7 Starting MARS-Apache service...
net start mars-apache  >nul 2>&1
if "%errorlevel%" NEQ "0" call :ECHO %date% %time% Error %errorlevel% starting MARS Apache service.
call :ECHO %date% %time% MARS Initialization finished.

:END
endlocal
popd
goto :EOF

:ECHO
echo %*>>%logfile%
echo %*
goto :EOF

