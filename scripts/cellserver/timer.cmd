@echo off
setlocal enabledelayedexpansion
echo.
if "%*" equ "" echo Usage: timer [command] && goto :END
echo Measuring duration of command "%*"
echo BEGIN --------------------------------------------------------------------
set _t1=%time%
rem start /b /wait %*
cmd /c %*
set _t2=%time%
echo END ----------------------------------------------------------------------
echo.
echo Executed command   : %*
echo Execution started  : %_t1%
echo Execution finished : %_t2%
for /f "delims=.,: tokens=1-4" %%i in ("%_t1%") do set /a _u1=(3600000 * %%i)+(60000 * %%j)+(1000 * %%k)+%%l
if "%_u1%" lss "0" echo Error during start microtime calculation (result: "%_u1%") && goto :END
for /f "delims=.,: tokens=1-4" %%i in ("%_t2%") do set /a _u2=(3600000 * %%i)+(60000 * %%j)+(1000 * %%k)+%%l
if "%_u2%" lss "0" echo Error during finish microtime calculation (result: "%_u2%") && goto :END
set /a _d=%_u2%-%_u1%
if "%_d%" lss "0" echo Error during duration calculation (result: "%_d%") && goto :END
set /a _u=%_d%-1000*(%_d%/1000)
if "%_u%" lss "0" echo Error during duration microtime calculation (result: "%_u%") && goto :END
set /a _s=(%_d%/1000)-60*(%_d%/60000)
if "%_s%" lss "0" echo Error during duration seconds calculation (result: "%_s%") && goto :END
set /a _m=(%_d%/60000)-60*(%_d%/3600000)
if "%_m%" lss "0" echo Error during duration minutes calculation (result: "%_m%") && goto :END
set /a _h=%_d%/3600000
if "%_h%" lss "0" echo Error during duration hours calculation (result: "%_h%") && goto :END
if %_h% lss 10 set _h=0%_h%
if %_m% lss 10 set _m=0%_m%
if %_s% lss 10 set _s=0%_s%
if %_u% lss 10 set _u=0%_u%
echo Calculated duration: %_h%:%_m%:%_s%.%_u% (%_d%ms)
:END
endlocal
