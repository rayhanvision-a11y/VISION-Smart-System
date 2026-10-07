#!/bin/bash
# ==============================================================================
# VISION Technologies Limited - BIND9 DNS Record Update Script
# Server: cdncache.visiontech.com.bd (172.30.20.50)
# Purpose: Add portal.visiontech.com.bd -> 103.118.78.250 to local DNS
# ==============================================================================

set -e

DOMAIN="visiontech.com.bd"
SUBDOMAIN="portal"
TARGET_IP="103.118.78.250"
RECORD_LINE="portal    IN    A    103.118.78.250"

echo "[*] Searching for BIND zone file for ${DOMAIN}..."

# Common zone file paths in BIND9 (Debian/Ubuntu/CentOS/cPanel)
ZONE_FILES=(
    "/var/named/${DOMAIN}.db"
    "/var/named/${DOMAIN}.zone"
    "/var/named/zones/${DOMAIN}.db"
    "/etc/bind/zones/${DOMAIN}.db"
    "/etc/bind/zones/${DOMAIN}.zone"
    "/etc/bind/db.${DOMAIN}"
    "/var/cache/bind/db.${DOMAIN}"
    "/etc/named.rfc1912.zones"
)

FOUND_ZONE=""

for f in "${ZONE_FILES[@]}"; do
    if [ -f "$f" ]; then
        FOUND_ZONE="$f"
        break
    fi
done

# If not found in standard paths, search dynamically
if [ -z "$FOUND_ZONE" ]; then
    FOUND_ZONE=$(grep -rl "masterdns.${DOMAIN}" /etc/bind/ /var/named/ /var/cache/bind/ 2>/dev/null | head -n 1 || true)
fi

# Check RPZ zone files as well
if [ -z "$FOUND_ZONE" ]; then
    RPZ_FILE=$(grep -rl "rpz" /etc/bind/ /var/named/ 2>/dev/null | grep -E '\.(db|zone)$' | head -n 1 || true)
    if [ -n "$RPZ_FILE" ]; then
        echo "[*] Found RPZ zone file: $RPZ_FILE"
        FOUND_ZONE="$RPZ_FILE"
        RECORD_LINE="portal.${DOMAIN}    A    ${TARGET_IP}"
    fi
fi

if [ -n "$FOUND_ZONE" ]; then
    echo "[+] Found zone file: ${FOUND_ZONE}"
    
    # Backup before modifying
    cp "${FOUND_ZONE}" "${FOUND_ZONE}.bak.$(date +%Y%m%d_%H%M%S)"
    echo "[+] Backup created at ${FOUND_ZONE}.bak.*"

    # Check if record already exists
    if grep -q "portal" "${FOUND_ZONE}"; then
        echo "[!] 'portal' already exists in ${FOUND_ZONE}. Updating IP..."
        sed -i -E "s/portal[[:space:]]+(IN[[:space:]]+)?A[[:space:]]+[0-9.]+/portal    IN    A    ${TARGET_IP}/g" "${FOUND_ZONE}"
    else
        echo "[+] Appending record: ${RECORD_LINE}"
        echo "${RECORD_LINE}" >> "${FOUND_ZONE}"
    fi

    # Update serial number if standard zone
    CURRENT_SERIAL=$(grep -E '[0-9]{10}' "${FOUND_ZONE}" | head -n 1 | awk '{print $1}' || true)
    if [ -n "$CURRENT_SERIAL" ]; then
        NEW_SERIAL=$((CURRENT_SERIAL + 1))
        sed -i "s/${CURRENT_SERIAL}/${NEW_SERIAL}/g" "${FOUND_ZONE}"
        echo "[+] Updated zone serial to ${NEW_SERIAL}"
    fi

    echo "[*] Reloading and flushing BIND DNS..."
    if command -v rndc >/dev/null 2>&1; then
        rndc reload || true
        rndc flush || true
    fi

    if systemctl is-active --quiet named; then
        systemctl reload named || systemctl restart named
    elif systemctl is-active --quiet bind9; then
        systemctl reload bind9 || systemctl restart bind9
    fi

    echo "[✓] SUCCESS: portal.${DOMAIN} -> ${TARGET_IP} successfully added to local DNS!"
    echo "[*] Verifying local resolution:"
    dig +short @127.0.0.1 portal.${DOMAIN} || nslookup portal.${DOMAIN} 127.0.0.1
else
    echo "[-] Could not automatically locate the zone file."
    echo "[*] Please run this manual command on the server:"
    echo "    echo '${RECORD_LINE}' >> /etc/bind/zones/${DOMAIN}.db"
    echo "    rndc reload && rndc flush"
    exit 1
fi
