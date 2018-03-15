@echo off

set _php_home=php

REM MARS 3.0 INSTALL SCRIPT
REM DON'T MODIFY ANYTHING BELOW THIS LINE -------------------------------------------------------------------------------

set _db_server=%1
if "%_db_server%" NEQ "" goto :PHP
echo Error: No DB server specified.
echo Usage: install.cmd DB_SERVER_FQDN.
goto :EOF

:PHP
if exist "%_php_home%\php.exe" goto :EXEC
echo PHP.EXE not found in path "%_php_home%\".
goto :EOF

:EXEC
echo Installing MARS files...
%_php_home%\php.exe install.php %_db_server%
echo Done.
