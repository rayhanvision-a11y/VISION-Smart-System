# Local DNS (172.30.20.50) Update Files

These files are created to update your local DNS server (`172.30.20.50` - BIND9) so that `portal.visiontech.com.bd` resolves correctly across your entire office LAN and Attendance devices.

### Target Record:
- **Domain:** `portal.visiontech.com.bd`
- **IP:** `103.118.78.250`
- **DNS Server:** `172.30.20.50`

---

### Files Included:

1. **`update-dns-zone.sh`**:
   - Bash script to run on your Linux DNS server `172.30.20.50`.
   - Automatically finds the zone file, creates a backup, appends `portal IN A 103.118.78.250`, increments serial, and runs `rndc reload && rndc flush`.

2. **`apply-to-dns-server.bat`**:
   - Windows double-click helper. Tests the current status and can SSH directly into `172.30.20.50` to apply the update.

3. **`update-pc-hosts.bat`**:
   - Adds the entry to your local Windows hosts file and flushes DNS on your PC.

---

### One-line SSH command to update 172.30.20.50 from your terminal:
```bash
ssh root@172.30.20.50 "bash -s" < update-dns-zone.sh
```
