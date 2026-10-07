@echo off
chcp 65001 >nul
title VISION Technologies - Local DNS 172.30.20.50 Updater
echo ===================================================================
echo   VISION Technologies Limited - Local DNS (172.30.20.50) Updater
echo ===================================================================
echo.
echo Target Domain : portal.visiontech.com.bd
echo Target IP     : 103.118.78.250
echo Local DNS     : 172.30.20.50
echo.

echo [1] Testing current status on 172.30.20.50...
nslookup portal.visiontech.com.bd 172.30.20.50
echo.

echo -------------------------------------------------------------------
echo [2] To apply this update directly to your DNS server (172.30.20.50):
echo -------------------------------------------------------------------
echo.
echo Option A: Run this SSH command from your terminal:
echo   ssh root@172.30.20.50 "bash -s" < update-dns-zone.sh
echo.
echo Option B: Or log into your DNS server (172.30.20.50) and run:
echo   rndc flush
echo.
echo -------------------------------------------------------------------
set /p DO_SSH="Do you want to run SSH update to 172.30.20.50 right now? (y/n): "
if /i "%DO_SSH%"=="y" (
    echo [*] Connecting via SSH to 172.30.20.50...
    scp -O update-dns-zone.sh root@172.30.20.50:/tmp/update-dns-zone.sh
    ssh root@172.30.20.50 "chmod +x /tmp/update-dns-zone.sh && /tmp/update-dns-zone.sh"
    echo.
    echo [*] Verifying update...
    nslookup portal.visiontech.com.bd 172.30.20.50
)

pause
