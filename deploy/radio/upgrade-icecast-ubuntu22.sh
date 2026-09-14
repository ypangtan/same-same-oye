#!/usr/bin/env bash
# Run on the Ubuntu server: sudo bash deploy/radio/upgrade-icecast-ubuntu22.sh
# Package installation can restart Icecast and briefly interrupt the broadcast.
set -euo pipefail

if [ "$(id -u)" -ne 0 ]; then
    echo 'Run this script with sudo.' >&2
    exit 1
fi
. /etc/os-release
if [ "$ID" != ubuntu ] || [ "$VERSION_ID" != 22.04 ] || [ "$(dpkg --print-architecture)" != amd64 ]; then
    echo 'This procedure was prepared for Ubuntu 22.04 amd64 only.' >&2
    exit 1
fi

radio_backup="/var/backups/icecast-upgrade-$(date +%Y%m%d-%H%M%S)"
install -d -m 700 "$radio_backup"
cp -a /etc/icecast2 "$radio_backup/icecast2"
cp -a /etc/nginx "$radio_backup/nginx"
dpkg-query -W icecast2 > "$radio_backup/previous-version.txt"
systemctl cat icecast2 > "$radio_backup/icecast2-service.txt"
# Keep a rollback package before replacing the distro version.
(
    cd "$radio_backup"
    apt-get download "icecast2=$(dpkg-query -W -f='${Version}' icecast2)"
)

apt-get update
apt-get install -y ca-certificates curl gnupg
curl -fSL --retry 2 --connect-timeout 10 --max-time 60 \
    'https://download.opensuse.org/repositories/multimedia:/xiph/xUbuntu_22.04/Release.key' \
    -o "$radio_backup/Release.key"
install -d -m 755 /etc/apt/keyrings
gpg --batch --yes --dearmor --output /etc/apt/keyrings/icecast-xiph.gpg "$radio_backup/Release.key"
chmod 644 /etc/apt/keyrings/icecast-xiph.gpg
cat > /etc/apt/sources.list.d/icecast-xiph.list <<'EOF'
deb [arch=amd64 signed-by=/etc/apt/keyrings/icecast-xiph.gpg] https://download.opensuse.org/repositories/multimedia:/xiph/xUbuntu_22.04/ ./
EOF
# Do not upgrade other multimedia packages from this additional repository.
cat > /etc/apt/preferences.d/icecast-xiph <<'EOF'
Package: icecast2 libigloo0
Pin: origin download.opensuse.org
Pin-Priority: 600

Package: *
Pin: origin download.opensuse.org
Pin-Priority: -1
EOF
apt-get update
apt-cache policy icecast2
# Preserve passwords, mount settings, burst size and source configuration.
apt-get install -y -o Dpkg::Options::='--force-confold' icecast2=2.5.0-1
icecast2 -v
systemctl restart icecast2
systemctl --no-pager status icecast2
echo "Backup and rollback package: $radio_backup"
echo 'Next: follow LISTENER_IPS.md to enable trusted proxy forwarding and database sync.'
