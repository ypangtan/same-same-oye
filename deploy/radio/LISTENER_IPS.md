# Listener IP storage on Ubuntu 22.04

This setup keeps the existing Live Listeners connection count and adds sampled
online unique IPs and unique IPs seen today (Malaysia time) to the admin page.
It records actual Icecast listener connections, not visitors to `/now-playing`.
IPs are stored in `radio_listener_sessions`; the public radio API does not expose
IPs or the admin-only statistics. No listener-side JavaScript/API call is needed.

## 1. Upgrade Icecast

The server reported Ubuntu 22.04.5 amd64 and Icecast 2.4.4-4build1. The official
Xiph OBS repository was checked on 2026-09-14: it contains `icecast2=2.5.0-1`
and `libigloo0=0.9.5-1` for Ubuntu 22.04 amd64.

From the deployed project directory:

```bash
sudo bash deploy/radio/upgrade-icecast-ubuntu22.sh
```

The script backs up Icecast/Nginx configuration and downloads the installed
Icecast rollback package before adding the signed, package-scoped repository.
It retains the existing Icecast configuration. Installation/restart interrupts
the broadcast briefly; Liquidsoap should reconnect automatically. Confirm that
the stream plays again before proceeding. If the script fails, stop and inspect
the error; do not run the later steps against a still-running 2.4 installation.

## 2. Add the trusted Nginx listener

Edit `/etc/icecast2/icecast.xml`. Add the two `<listen-socket>` sections from
`icecast-2.5-proxy.xml.example` inside the existing `<icecast>` element.
Keep the existing port 8000, credentials, mount and burst-size settings.
The virtual socket identifies the trusted local proxy; the real socket binds
port 8001 to loopback only. Do not expose port 8001 on a public interface.

```bash
sudo systemctl restart icecast2
sudo systemctl status icecast2 --no-pager
sudo ss -ltnp 'sport = :8001'
```

Expect `127.0.0.1:8001`, not `0.0.0.0:8001`. Review startup errors with:

```bash
sudo journalctl -u icecast2 -n 50 --no-pager
```

## 3. Update the Nginx stream location

Replace only the `/radio-stream/` location in the existing HTTPS vhost with
`nginx-radio-stream.conf.example`. It changes the upstream to local port 8001
and overwrites `X-Forwarded-For` with `$remote_addr` (not a browser-supplied value).
Leave the Laravel/PHP locations and `/download` redirect intact.

```bash
sudo nginx -t && sudo systemctl reload nginx
```

This assumes listeners connect directly to Nginx. If a CDN/load balancer is in
front, first configure Nginx real-IP handling for that provider's trusted source
addresses; otherwise `$remote_addr` will be the CDN IP. Do not trust arbitrary
incoming `X-Forwarded-For` headers. The Icecast admin interface stays internal;
do not add a public Nginx proxy for `/admin/listclients`.

Open the public stream on a phone using mobile data. While it remains connected,
run this on the server (curl prompts for the Icecast admin password):

```bash
curl --fail --user admin 'http://127.0.0.1:8000/admin/listclients?mount=/radio.mp3'
```

Confirm the listener `ip`/`IP` is the phone's public IP, not `127.0.0.1`, and a
spoofed incoming X-Forwarded-For header cannot change it. Do not paste the password
into chat. This checks an active streaming connection, not just a HEAD request.

## 4. Enable Laravel recording

Deploy the code before running the new migration. Add to the server `.env`:

```dotenv
RADIO_LISTENER_TRACKING=true
RADIO_ICECAST_ADMIN_URL=http://127.0.0.1:8000/admin/listclients
RADIO_ICECAST_ADMIN_USER=admin
RADIO_ICECAST_ADMIN_PASSWORD="YOUR_EXISTING_ICECAST_ADMIN_PASSWORD"
RADIO_ICECAST_MOUNT=/radio.mp3
```

Use the Icecast **admin password**, not its source password or RADIO_ENGINE_KEY.

```bash
php artisan migrate --path=database/migrations/2026_09_14_000001_create_radio_listener_sessions_table.php --force
php artisan config:cache
php artisan radio:sync-listeners
```

Expected example: `Recorded 2 connections, 1 unique IPs.` Run Artisan as the same
deployment/scheduler user normally used by this app to keep file ownership valid.
The existing Laravel scheduler runs this command every minute with an overlap
lock. Ensure the existing `php artisan schedule:run` cron is active; do not add a
second cron if it is already installed. Restart long-lived PHP workers if used.

Verify: two streams on one network give two connections and one IP; a phone on
mobile data adds another IP. Closing a stream marks its session disconnected at
the next successful sample. Reload the Radio Queue page to see IP statistics.

## What the stored data means

- `client_id`: Icecast connection ID; repeated polls update the same session.
- `ip`: canonical IPv4/IPv6 address. One IP does not necessarily mean one person.
- `connected_at`: estimated using Icecast's connected-seconds value.
- `first_seen_at` / `last_seen_at`: when the poller observed this connection.
- `disconnected_at`: when a successful poll first found the connection absent.
- `source_key`: identifies the configured admin endpoint and mount.
- `radio_listener_syncs`: tracks freshness, including successful empty samples.

This is a one-minute sample, not a complete access log: connections entirely
between polls may be missed. Today's total counts IPs observed since midnight
MYT, including sessions continuing from yesterday. Icecast ID reuse after restart
is detected by changed IP or estimated connection start (10-second tolerance).
Very rapid ID reuse with the same IP/start window may still be indistinguishable.

HTTP errors, invalid XML, missing mounts and loopback IPs do not wipe live records.
They mark the sample unavailable. Counts show `-` after a failed sync or when no
successful sample has arrived for 120 seconds. An empty successful mount response
does close all previously active sessions. A missing mount is treated as unknown
rather than claiming an exact disconnection time. History is kept; no automatic
IP retention/deletion policy is enabled.

## Rollback

Set `RADIO_LISTENER_TRACKING=false` and run `php artisan config:cache` to disable
recording and hide the IP statistics without deleting history.

For an Icecast downgrade, use the timestamped directory printed by the upgrade
script. Restore the backed-up Icecast configuration and the original Nginx
stream location (port 8000); do not leave 2.5-only proxy sockets in a 2.4 config.
Install the saved old `icecast2_*.deb` using `apt-get install --allow-downgrades`
with its exact path, retain the restored config, disable the added Xiph repository,
then restart Icecast and run `nginx -t` before reloading Nginx. Verify streaming.
The PHP code is inert when tracking is disabled; the history tables can remain.

## Sources and validation

- [Icecast official package distribution](https://icecast.org/download/)
- [Ubuntu 22.04 package index](https://download.opensuse.org/repositories/multimedia:/xiph/xUbuntu_22.04/Packages)
- [Icecast 2.5 proxy header processing](https://github.com/xiph/Icecast-Server/blob/v2.5.0/src/connection_handle.c)
- [Icecast 2.5 socket configuration parser](https://github.com/xiph/Icecast-Server/blob/v2.5.0/src/cfgfile.c)
- [Icecast listener XML fields](https://github.com/xiph/Icecast-Server/blob/v2.5.0/src/admin.c)

PHP sync tests use an isolated in-memory database and fake HTTP responses. The
upgrade script and proxy configuration require validation on the Linux server;
they have not been executed against the live stream by Codex.
