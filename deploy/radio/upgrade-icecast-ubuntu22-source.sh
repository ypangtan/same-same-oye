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
# Integrity: `dget` runs dscverify, which checks each .dsc file's GPG signature
# against the `debian-keyring` package (Debian Developer/Maintainer keys, installed
# below) and then checks the sha256 checksums of the .orig/.debian files it
# downloaded against the ones listed inside that signed .dsc. Nothing is
# extracted or built until both checks pass.
#
# icecast2 2.5.0 requires libigloo-dev >= 0.9.5, which Ubuntu 22.04 does not
# package (it only has 0.9.0-1). So this builds Debian's libigloo 0.9.5 backport
# first and installs it, the same verified way, before building icecast2 itself.
# libigloo's own .dsc declares `dpkg-dev (>= 1.22.5)`; jammy ships 1.21.1, and
# that requirement is a packaging-format nicety libigloo's actual build does not
# need, so only that one build is run with `-d` (skip build-dependency version
# check) after this script has already installed libigloo's real dependencies.
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

# Debian backports builds, chosen over the sid/trixie uploads because backports
# uploads are the ones Debian expects people to rebuild against an older base.
igloo_version='0.9.5-1~bpo13+1'
icecast_version='2.5.0-1~bpo13+1'
igloo_dsc_url="https://deb.debian.org/debian/pool/main/libi/libigloo/libigloo_${igloo_version}.dsc"
icecast_dsc_url="https://deb.debian.org/debian/pool/main/i/icecast2/icecast2_${icecast_version}.dsc"

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
apt-get install -y build-essential fakeroot devscripts dpkg-dev debhelper debian-keyring gnupg \
    libcurl4-openssl-dev libmaxminddb-dev libogg-dev librhash-dev libspeex-dev libssl-dev \
    libtheora-dev libvorbis-dev libxml2-dev libxslt1-dev pkgconf po-debconf

build_dir="$(mktemp -d)"
trap 'rm -rf "$build_dir"' EXIT

# --- Stage 1: libigloo (icecast2's missing build dependency) ---
(
    cd "$build_dir"
    dget "$igloo_dsc_url"
    cd "libigloo-${igloo_version%%-*}"
    dpkg-buildpackage -us -uc -b -d
)
igloo_debs=("$build_dir"/libigloo*_amd64.deb)
if [ ! -e "${igloo_debs[0]}" ]; then
    echo 'libigloo build did not produce any .deb; inspect the output above.' >&2
    exit 1
fi
cp "${igloo_debs[@]}" "$radio_backup/"
dpkg -i "${igloo_debs[@]}" || apt-get install -f -y

# --- Stage 2: icecast2 itself, now that libigloo-dev >= 0.9.5 is installed ---
(
    cd "$build_dir"
    dget "$icecast_dsc_url"
    cd "icecast2-${icecast_version%%-*}"
    dpkg-buildpackage -us -uc -b
)
deb_path="$(find "$build_dir" -maxdepth 1 -name 'icecast2_*.deb' | head -n1)"
if [ -z "$deb_path" ]; then
    echo 'icecast2 build did not produce a .deb; inspect the output above.' >&2
    exit 1
fi
cp "$deb_path" "$radio_backup/"

# --force-confold keeps the existing, admin-edited /etc/icecast2/icecast.xml
# instead of prompting.
dpkg -i -o Dpkg::Options::='--force-confold' "$deb_path" || apt-get install -f -y
apt-mark hold icecast2 libigloo0 libigloo-dev 2>/dev/null || apt-mark hold icecast2
icecast2 -v
systemctl restart icecast2
systemctl --no-pager status icecast2
echo "Backup, rollback package and built .debs: $radio_backup"
echo 'Next: follow LISTENER_IPS.md step 2 to enable trusted proxy forwarding and database sync.'
