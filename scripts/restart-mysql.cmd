@echo off
setlocal enabledelayedexpansion
pushd %~dp0
echo %date% %time% stopping MARS-MySQL service...
net stop mars-mysql
echo %date% %time% starting MARS-MySQL service...
net start mars-mysql
echo %date% %time% Finished.
endlocal
popd
