@echo off
title Laragon VISION Smart System - Ngrok Tunnel
echo ===================================================
echo   Starting Ngrok Tunnel for VISION Smart System
echo   Public URL: https://omit-vanilla-aware.ngrok-free.dev
echo   Local Host: isp-tickets.Rayhan (Port 80)
echo ===================================================
echo.
ngrok http --url=omit-vanilla-aware.ngrok-free.dev --host-header=isp-tickets.Rayhan 80
pause
