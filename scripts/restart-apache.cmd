@echo off
setlocal enabledelayedexpansion
pushd %~dp0
echo %date% %time% stopping MARS-APACHE service...
net stop mars-apache
echo %date% %time% starting MARS-APACHE service...
net start mars-apache
echo %date% %time% Finished.
endlocal
popd
