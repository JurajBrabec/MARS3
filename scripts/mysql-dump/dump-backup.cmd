@echo off
setlocal enabledelayedexpansion
pushd %~dp0
set db=mars_backup
set root=%~dp0\..\..
set logfile=%root%\logs\mysql-dump.log

call :ECHO %date% %time% starting MySQL dump of database '%db%'...
%root%\mysql\bin\mysqldump.exe --defaults-file=%root%\mysql\dump.cnf --flush-logs --flush-privileges --log-error=%logfile% --replace --databases %db%  | %root%\bin\gzip.exe --force --best > %db%.sql.gz
dir %db%.sql.gz | findstr %db%>>%logfile%
call :ECHO %date% %time% Finished.

endlocal
popd
goto :EOF

:ECHO
echo %*>>%logfile%
echo %*
goto :EOF

