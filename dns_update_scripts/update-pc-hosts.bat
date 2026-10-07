@echo off
:: Run this batch file as Administrator to add portal.visiontech.com.bd to local PC hosts
echo Updating local Windows hosts file...
findstr /i "portal.visiontech.com.bd" "%WINDIR%\System32\drivers\etc\hosts" >nul
if %errorlevel% neq 0 (
    echo. >> "%WINDIR%\System32\drivers\etc\hosts"
    echo 103.118.78.250 portal.visiontech.com.bd >> "%WINDIR%\System32\drivers\etc\hosts"
    echo [✓] Successfully added portal.visiontech.com.bd to hosts file!
) else (
    echo [!] Entry already exists in hosts file.
)
ipconfig /flushdns
echo [✓] DNS cache flushed.
pause
