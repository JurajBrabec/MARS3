@echo off
setlocal enabledelayedexpansion
pushd %~dp0
set scheduler=1
set root=%~dp0\..
set logfile=%root%\logs\scheduler.log
:start
echo %date% %time% Scheduler starting>>%logfile%
%root%\php\php.exe %root%\www\mars\index.php s=scheduler>>%logfile% 2>&1
if not exist %root%\upgrade.cmd goto :end
start /b /wait cmd /c %root%\upgrade.cmd>>%logfile%
echo %date% %time% E:%errorlevel%>>%logfile% 
del /q %root%\upgrade.cmd >nul 2>&1
:end
echo %date% %time% Scheduler stopping>>%logfile%
endlocal
popd