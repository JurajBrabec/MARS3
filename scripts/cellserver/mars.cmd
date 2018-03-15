@echo off

REM MARS 3.0 MAIN SCRIPT
REM DON'T MODIFY ANYTHING BELOW THIS LINE -------------------------------------------------------------------------------

setlocal enabledelayedexpansion
pushd %~dp0
set _timestamp=%date% %time%
set _mars_home=%~dp0
for /F "tokens=1,* delims== " %%i in ('type "%_mars_home%config.ini"') do if "%%i" EQU "PHP_HOME" set _php_home=%%j\

if not exist "%_mars_home%log" mkdir "%_mars_home%log"
if not exist "%_mars_home%queue" mkdir "%_mars_home%queue"
if not exist "%_mars_home%tmp" mkdir "%_mars_home%tmp"

set _actionlog=%_mars_home%log\actions.log
set _errorlog=%_mars_home%log\errors.log

if "%1" equ "" (
    call :USAGE
    goto :END
)
if "%1" equ "-n" (
    echo Executing notification...
    call :EXECUTE NOTIFICATION
    goto :END
)
if "%1" equ "-s" (
    echo Executing scheduler...
    call :EXECUTE SCHEDULER
    goto :END
)
if "%1" equ "-r" (
    echo Executing routine '%2'...
    call :EXECUTE ROUTINE %2
    goto :END
)
if "%1" equ "-t" (
    echo Executing test...
    call :EXECUTE TEST
    goto :END
)
echo Error: Unknown/incomplete parameter '%1' provided.
echo %_timestamp% Error: Unknown/incomplete parameter '%1' provided.>>"%_errorlog%"
call :USAGE
goto :END

:USAGE
    echo.
    echo MARS 3.0 usage: mars.cmd [ -r routine_name ^| -n ^| -s ^| -t ]
    echo -r  manual routine start (requires routine name)
    echo     Routine names:
    echo       libraries
    echo       devices
    echo       media
    echo       specifications
    echo       check_backups
    echo       locked_objects
    echo       omnistat
    echo -n  triggered from DP Notification
    echo -s  used in crontab/scheduler
    echo -t  manual installation test
    echo.
    goto :EOF

:EXECUTE
    powershell "Get-Process omnistat -ea SilentlyContinue | Where { $_.StartTime -lt (Get-Date).AddMinutes(-4) } | Stop-Process -Force" >nul 2>&1
    "%_php_home%php.exe" "%_mars_home%mars.php" %*
    if not exist "%_mars_home%mars.lock" (
        if exist "%_mars_home%tmp\*.tmp" del "%_mars_home%tmp\*.tmp"
    )
    goto :EOF

:END
    endlocal
    popd
