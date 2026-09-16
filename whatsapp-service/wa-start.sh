#!/bin/bash

# Find Node binary path
if [ -f "/opt/cpanel/ea-nodejs16/bin/node" ]; then
    NODE_BIN="/opt/cpanel/ea-nodejs16/bin/node"
elif [ -f "/opt/cpanel/ea-nodejs20/bin/node" ]; then
    NODE_BIN="/opt/cpanel/ea-nodejs20/bin/node"
elif command -v node &> /dev/null; then
    NODE_BIN="node"
else
    NODE_BIN="/usr/local/bin/node"
fi

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BASE_DIR="$(dirname "$SCRIPT_DIR")"
PID_FILE="$SCRIPT_DIR/wa.pid"
LOG_FILE="$SCRIPT_DIR/wa.log"

cd "$BASE_DIR"

if [ -f "$PID_FILE" ]; then
    PID=$(cat "$PID_FILE")
    if kill -0 "$PID" 2>/dev/null; then
        echo "WhatsApp server is already running with PID $PID"
        exit 0
    fi
fi

echo "Starting WhatsApp server with $NODE_BIN..."
nohup $NODE_BIN "$BASE_DIR/whatsapp_server.cjs" > "$LOG_FILE" 2>&1 &
NEW_PID=$!
echo "$NEW_PID" > "$PID_FILE"
echo "Started whatsapp_server.cjs (PID $NEW_PID)"
