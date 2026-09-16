#!/bin/bash

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BASE_DIR="$(dirname "$SCRIPT_DIR")"
PID_FILE="$SCRIPT_DIR/wa.pid"

if [ -f "$PID_FILE" ]; then
    PID=$(cat "$PID_FILE")
    if kill -0 "$PID" 2>/dev/null; then
        exit 0
    fi
fi

# If PID not running, restart service
"$SCRIPT_DIR/wa-start.sh" > /dev/null 2>&1
