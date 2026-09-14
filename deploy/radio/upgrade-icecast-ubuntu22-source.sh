#!/usr/bin/env bash
# Run on the Ubuntu server: sudo bash deploy/radio/upgrade-icecast-ubuntu22-source.sh
#
# Why this script exists: upgrade-icecast-ubuntu22.sh installs icecast2 from the
# openSUSE Build Service "multimedia:xiph" repository. That repository's signing
# key (77EC2301F23C6AA3) expired on 2020-01-30 -- 6 years ago -- and has not been
# rotated since (see https://gitlab.xiph.org/xiph/icecast-server/-/issues/2434).
# `apt-get update` refuses it, and this script does not bypass that check.
#
# Instead this rebuilds Debian's own icecast2 2.5.0 *source* package locally
# against this machine's own libraries, so the result is ABI-compatible with
# Ubuntu 22.04 and uses Debian's maintained packaging (systemd unit, icecast2
# user/group, log/pid paths) rather than a hand-rolled `./configure` guess.
# Integrity: `dget` runs dscverify, which checks the .dsc file's GPG signature
# against the `debian-keyring` package (Debian Developer/Maintainer keys, installed
# below) and then checks the sha256 checksums of the .orig/.debian files it
# downloaded against the ones listed inside that signed .dsc. Nothing is
# extracted or built until both checks pass.
#
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

# Debian backports build of icecast2 2.5.0, chosen over the sid/trixie 2.5.0-2
# upload because backports uploads are the ones Debian expects people to rebuild
# against an older base like this.
pkg_version='2.5.0-1~bpo13+1'
dsc_url="https://deb.debian.org/debian/pool/main/i/icecast2/icecast2_${pkg_version}.dsc"

# Clean up the half-added OBS repo from the earlier failed attempt, if present,
# so `apt-get update` stops warning about its expired key on every run.
rm -f /etc/apt/sources.list.d/icecast-xiph.list /etc/apt/preferences.d/icecast-xiph /etc/apt/keyrings/icecast-xiph.gpg

radio_backup="/var/backups/icecast-upgrade-$(date +%Y%m%d-%H%M%S)"
install -d -m 700 "$radio_backup"
cp -a /etc/icecast2 "$radio_backup/icecast2"
cp -a /etc/nginx "$radio_backup/nginx"
dpkg-query -W icecast2 > "$radio_backup/previous-version.txt"
systemctl cat icecast2 > "$radio_backup/icecast2-service.txt"
# Keep a rollback package before replacing the installed version.
(
    cd "$radio_backup"
    apt-get download "icecast2=$(dpkg-query -W -f='${Version}' icecast2)"
)

apt-get update
apt-get install -y build-essential fakeroot devscripts dpkg-dev debian-keyring gnupg

# Cloud images commonly ship /etc/apt/sources.list with the `deb-src` lines
# commented out; `apt-get build-dep` needs them enabled. Only touch the main
# sources.list, and only if build-dep fails without them.
if ! apt-get build-dep -y --dry-run icecast2 >/dev/null 2>&1; then
    cp -a /etc/apt/sources.list "$radio_backup/sources.list.orig"
    sed -i -E 's/^#\s*(deb-src .*)/\1/' /etc/apt/sources.list
    apt-get update
fi
# Pulls the exact build dependencies Ubuntu's own icecast2 source package
# declares -- these are the jammy-native -dev packages, so the rebuild below
# links against this machine's actual runtime libraries.
apt-get build-dep -y icecast2

build_dir="$(mktemp -d)"
trap 'rm -rf "$build_dir"' EXIT
(
    cd "$build_dir"
    dget "$dsc_url"
    cd "icecast2-${pkg_version%%-*}"
    dpkg-buildpackage -us -uc -b
)

deb_path="$(find "$build_dir" -maxdepth 1 -name 'icecast2_*.deb' | head -n1)"
if [ -z "$deb_path" ]; then
    echo 'Build did not produce an icecast2 .deb; inspect the output above.' >&2
    exit 1
fi
cp "$deb_path" "$radio_backup/"

# --force-confold keeps the existing, admin-edited /etc/icecast2/icecast.xml
# instead of prompting (matches how the apt-based script installs 2.5.0-1).
dpkg -i -o Dpkg::Options::='--force-confold' "$deb_path" || apt-get install -f -y
apt-mark hold icecast2
icecast2 -v
systemctl restart icecast2
systemctl --no-pager status icecast2
echo "Backup, rollback package and built .deb: $radio_backup"
echo 'Next: follow LISTENER_IPS.md step 2 to enable trusted proxy forwarding and database sync.'
