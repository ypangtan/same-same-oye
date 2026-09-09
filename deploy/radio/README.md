# Radio streaming engine (Icecast + Liquidsoap)

Runs on the same Linux server as this Laravel app. It has no code dependency
on the app — it only talks to it over HTTP (`/api/v1/radio/next` and
`/api/v1/radio/played`, guarded by the `RADIO_ENGINE_KEY` shared secret).

These files are a documented starting point, not a tested deploy — they
weren't run against a live Icecast/Liquidsoap install. Verify each step
against your actual server/versions before trusting it in production.

## 1. Install

```bash
sudo apt update
sudo apt install icecast2 liquidsoap
```

During the icecast2 install you'll be prompted for a hostname/passwords —
you can change them afterwards in `/etc/icecast2/icecast.xml`.

## 2. Configure Icecast

Merge [`icecast.xml.example`](./icecast.xml.example) into
`/etc/icecast2/icecast.xml` (limits/authentication/listen-socket/mount
sections — don't replace the whole file). Then:

```bash
sudo systemctl restart icecast2
sudo systemctl enable icecast2
```

## 3. Configure Liquidsoap

```bash
sudo mkdir -p /opt/same-same-oye-radio
sudo cp radio.liq /opt/same-same-oye-radio/radio.liq
sudo useradd --system --no-create-home liquidsoap
sudo mkdir -p /var/log/liquidsoap && sudo chown liquidsoap:liquidsoap /var/log/liquidsoap
```

Edit `/opt/same-same-oye-radio/radio.liq` and fill in:
- `engine_key` — same value as `RADIO_ENGINE_KEY` in the app's `.env`
- `icecast_source_password` — same value as `<source-password>` in icecast.xml
- `api_base` — if the app isn't served at `http://127.0.0.1/api/v1/radio` on
  this box (e.g. it's behind a vhost path), adjust accordingly

## 4. Run it as a service

```bash
sudo cp liquidsoap-radio.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now liquidsoap-radio
sudo journalctl -u liquidsoap-radio -f   # watch logs / diagnose startup errors
```

## 5. Point the app at the stream

The listener-facing stream URL is:

```
http://<your-server>:8000/radio.mp3
```

Put that URL wherever the app/website reads the "now playing" stream from
(env var, settings table, whatever you already use). Play it with a plain
audio element / native player pointed straight at that URL — no app-side
storage or download involved, and no HLS, so it starts in ~1-3s like a local
MP3 rather than radio.co's 6-7s.

## How the pieces fit together

1. Admin uploads an mp3 in the backoffice (**Radio → Radio Queue → Add**) —
   it goes straight to R2, never touches local disk.
2. Admin drags to reorder the queue. Order = broadcast order.
3. Liquidsoap polls `GET /api/v1/radio/next`. Laravel hands back the front of
   the queue and marks it "reserved" so a second poll doesn't get the same
   track twice.
4. Liquidsoap downloads that file and starts playing it on air.
5. The instant it starts, Liquidsoap calls `POST /api/v1/radio/played`.
   Laravel deletes the R2 file (it's already safely downloaded locally by
   Liquidsoap, so this doesn't interrupt playback) and keeps the title +
   timestamp as a permanent history row (**Radio → Play History**).
6. Repeat from step 3.

If the queue ever runs dry, Liquidsoap falls back to silence rather than
dropping the stream — upload more tracks and it picks back up automatically.

## Now playing + live listener count (backoffice)

The Radio Queue page also shows what's currently on air and how many people
are listening right now, plus a graph of listener count over time. No extra
Icecast config needed — `status-json.xsl` is public by default in a stock
Icecast install, same as the public stats page.

- **Now playing / current listener count**: read live from Icecast on every
  page load / 15s poll — nothing to deploy for this part, it just needs
  Icecast reachable at `RADIO_ICECAST_STATUS_URL` (defaults to
  `http://127.0.0.1:8000/status-json.xsl`, i.e. Icecast on the same box).
- **Listener graph**: Icecast has no history, only "right now", so a
  `radio:snapshot-listeners` command records a snapshot every 5 minutes into
  `radio_listener_snapshots`. It's already registered in
  `App\Console\Kernel::schedule()` — no extra cron entry needed as long as
  this server already runs `php artisan schedule:run` every minute (it does,
  for the existing notification/subscription jobs).
