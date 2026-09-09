#!/usr/bin/liquidsoap
# ---------------------------------------------------------------------------
# same-same-oye — real-time radio engine
#
# Pulls the play queue from the Laravel backoffice one track at a time,
# broadcasts it live via Icecast, and reports each track back to Laravel the
# moment it starts airing so the R2 file gets deleted and a play-history row
# is kept (see App\Services\RadioQueueService::next / markPlayed).
#
# This is the version actually verified working end-to-end (see the notes
# below on why it looks the way it does — each one was a real failure mode
# hit during deployment, not a hypothetical):
#
# - Uses `process.read("curl ...")` instead of Liquidsoap's own http.get/post.
#   Some Liquidsoap builds (this one included) are missing the optional
#   `ocurl` binding: http.get/http.post fail immediately with
#   `error(kind="not_found", message="Please install ocurl to enable http.get")`.
#   Shelling out to curl sidesteps that entirely.
# - /next and /played are plain text, not JSON — Liquidsoap's JSON API has
#   changed shape across versions (some need a typed `default` argument);
#   string.split/list.nth have been stable forever.
# - The R2 URL is downloaded to a local file with curl BEFORE handing it to
#   request.create — Liquidsoap resolving a remote https:// URL directly hit
#   the same missing-ocurl wall. request.create() then just plays a local
#   file, no network resolution needed on Liquidsoap's side at all.
# - Every curl call has its arguments in single quotes. The R2 URL contains
#   unescaped `&` (multiple query params) — without quotes, the shell splits
#   it into several background commands at each `&`, silently truncating the
#   download to garbage. This one is easy to reintroduce by accident if you
#   edit this file — keep the quotes.
# ---------------------------------------------------------------------------

settings.log.level.set(4)
settings.log.file.path.set("/var/log/liquidsoap/radio.log")

# ---- Config — fill these in for your server ----
api_base = "https://your-domain.example.com/api/v1/radio" # public HTTPS URL, not 127.0.0.1 — see README
engine_key = "REPLACE_WITH_RADIO_ENGINE_KEY" # must match RADIO_ENGINE_KEY in the app's .env

icecast_host = "localhost"
icecast_port = 8000
icecast_source_password = "REPLACE_WITH_ICECAST_SOURCE_PASSWORD" # must match icecast.xml
icecast_mount = "/radio.mp3"
# --------------------------------------------------

# Ask Laravel for the next track. Body is "{id}\n{url}\n{title}", or empty
# when the queue is empty. Returns [] for "nothing to play", or
# [(id, url, title)] on success — a list instead of an optional/nullable
# value, so this doesn't depend on Liquidsoap's optional-type API either.
def fetch_next() =
  try
    response = process.read("curl -s -H 'X-Radio-Engine-Key: #{engine_key}' '#{api_base}/next'")
    body = string.trim(response)
    if body != "" then
      lines = string.split(separator="\n", body)
      if list.length(lines) >= 3 then
        [(list.nth(default="", lines, 0), list.nth(default="", lines, 1), list.nth(default="", lines, 2))]
      else
        []
      end
    else
      []
    end
  catch err do
    print("radio: fetch_next failed: #{err}")
    []
  end
end

# Tell Laravel a track has started airing — it deletes the R2 file and logs
# the play in radio_queue_items. Safe to call as soon as the track starts:
# Liquidsoap has already downloaded the file locally by then, so deleting the
# R2 copy does not interrupt playback. id travels as a query param, not a
# JSON body — Laravel reads it either way, and it means this call needs no
# body-encoding function at all. Also cleans up the local temp download —
# without this /tmp fills up with one leftover mp3 per track played, forever.
def mark_played(id) =
  if id != "" then
    ignore(process.read("curl -s -X POST -H 'X-Radio-Engine-Key: #{engine_key}' '#{api_base}/played?id=#{id}'"))
    ignore(process.read("rm -f /tmp/radio-#{id}.mp3"))
  end
end

# Liquidsoap calls this whenever it needs another track for the queue. The
# track id and title travel with the request as "annotate:" metadata:
# - radio_id lets on_track below report the *exact* track that just started,
#   regardless of how far ahead Liquidsoap prefetches.
# - title is what output.icecast forwards to Icecast as the live ICY
#   metadata update, which is what the backoffice's "now playing" panel
#   reads back (via IcecastService) — see RadioQueueService::nowPlaying().
#   Not escaped: a title containing a literal `"` or `,` could confuse the
#   annotate: parser for that one track. Rare enough in practice not to be
#   worth the risk of an unproven escaping function breaking something else.
def next_request() =
  next = fetch_next()
  if list.length(next) > 0 then
    let (id, url, title) = list.nth(default=("", "", ""), next, 0)
    local_path = "/tmp/radio-#{id}.mp3"
    ignore(process.read("curl -s -o '#{local_path}' '#{url}' && echo OK"))
    [request.create("annotate:radio_id=\"#{id}\",title=\"#{title}\":#{local_path}")]
  else
    []
  end
end

queue = request.dynamic.list(next_request)

# Fallback silence so the stream never drops even if the queue runs dry
# (e.g. nothing uploaded yet, or fetch_next errors out). Carries no title
# metadata, which is what makes nowPlaying() correctly go blank while this
# is what's actually on air.
silence = blank(duration=5.)

radio = fallback(track_sensitive=false, [queue, silence])

source.on_track(radio, fun (m) -> mark_played(m["radio_id"]))

output.icecast(
  %mp3(bitrate=128),
  host=icecast_host,
  port=icecast_port,
  password=icecast_source_password,
  mount=icecast_mount,
  radio
)
