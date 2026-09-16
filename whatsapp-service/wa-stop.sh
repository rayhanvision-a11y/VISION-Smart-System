#!/bin/bash

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PID_FILE="$SCRIPT_DIR/wa.pid"

if [ -f "$PID_FILE" ]; then
    PID=$(cat "$PID_FILE")
    if kill -0 "$PID" 2>/dev/null; then
        echo "Stopping WhatsApp server (PID $PID)..."
        kill "$PID"
        rm -f "$PID_FILE"
        echo "WhatsApp server stopped."
    else
        echo "No running WhatsApp process found for PID $PID."
        rm -f "$PID_FILE"
    fi
else
    echo "WhatsApp server is not running."
fi
