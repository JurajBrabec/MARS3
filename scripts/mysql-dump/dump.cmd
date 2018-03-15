@echo off
setlocal enabledelayedexpansion
pushd %~dp0
set db=mars30
set root=%~dp0\..\..
set logfile=%root%\logs\mysql-dump.log

call :ECHO %date% %time% starting MySQL dump of database '%db%'...
%root%\mysql\bin\mysqldump.exe --defaults-file=%root%\mysql\dump.cnf --no-create-info --flush-logs --flush-privileges --log-error=%logfile% --replace --databases %db% --ignore-table=%db%.config_scripts --ignore-table=%db%.config_reports | %root%\bin\gzip.exe --force --best > %db%.sql.gz
dir %db%.sql.gz | findstr %db%>>%logfile%
call :ECHO %date% %time% Finished.

endlocal
popd
goto :EOF

:ECHO
echo %*>>%logfile%
echo %*
goto :EOF

