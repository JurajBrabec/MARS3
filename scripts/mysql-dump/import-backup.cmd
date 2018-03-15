@echo off
setlocal enabledelayedexpansion
pushd %~dp0
set db=mars_backup
set root=%~dp0\..\..
set logfile=%root%\logs\mysql-import.log

if exist %db%.sql goto :IMPORT
if not exist %db%.sql.gz  goto :ERROR
call :ECHO %date% %time% Extracting '%db%.sql.gz' archive ...
%root%\bin\gzip.exe -d %db%.sql.gz
if exist %db%.sql goto :IMPORT
:ERROR
call :ECHO %date% %time% Error: Files '%db%.sql' or '%db%.sql.gz' not found.
goto :END
:IMPORT
call :ECHO %date% %time% Importing '%db%.sql' dump ...
%root%\mysql\bin\mysql.exe --defaults-file=%root%\mysql\dump.cnf <%db%.sql >>%logfile%
call :ECHO %date% %time% Finished.

:END
endlocal
popd
goto :EOF

:ECHO
echo %*>>%logfile%
echo %*
goto :EOF

