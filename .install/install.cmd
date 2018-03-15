@echo off
setlocal enabledelayedexpansion
pushd %~dp0
set _file=%TEMP%\marsinst.tmp
call :ECHO Installing MARS 3.0 Web/DB server...
call :ECHO Checking processes...
call :CHECKPORT 80
call :CHECKPORT 3306
if exist %_file% goto :INSTALL
call :ECHO Exiting.
goto :EOF

:INSTALL
del %_file%
call :ECHO Installing Microsoft Visual C++ Redistributable Components...
start /wait ..\bin\vcredist_x86.exe  /passive /promptrestart /showfinalerror
call :ECHO Installing Apache service...
..\apache\bin\httpd.exe -k install -n MARS-Apache
call :ECHO Installing MySQL service...
..\mysql\bin\mysqld.exe --install MARS-MySQL
call :ECHO Initializing MARS database...
call ..\scripts\mysql-init\init.cmd
call :ECHO Configuring MARS scheduler...
SCHTASKS /Create /TN MARS-Scheduler /XML install.xml /F
call :ECHO Finished.
endlocal
popd
goto :EOF

:CHECKPORT
netstat -ano -p TCP | findstr LISTENING | findstr /c:":%1 " > %_file%
if %errorlevel% EQU 1 goto :EOF
for /f "tokens=1,2,3,4,5" %%i in (%_file%) do set _pid=%%m
tasklist /svc /fi "PID eq %_pid%" | findstr %_pid% > %_file%
for /f "tokens=1,2,3" %%i in (%_file%) do set _name=%%i && set _service=%%k
if %_pid% EQU 4 set _name=System&&set _service=HTTP
call :ECHO Error: A process with PID %_pid% (name "%_name%" service "%_service%") is already listening on port %1.
del %_file%
goto :EOF

:ECHO
echo %date% %time% %*
echo %date% %time% %*>>..\logs\install.log
goto :EOF