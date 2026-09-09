#!/usr/bin/liquidsoap
# ---------------------------------------------------------------------------
# same-same-oye — real-time radio engine
#
# Pulls the play queue from the Laravel backoffice one track at a time,
# broadcasts it live via Icecast, and reports each track back to Laravel the
# moment it starts airing so the R2 file gets deleted and a play-history row
# is kept (see App\Services\RadioQueueService::next / markPlayed).
#
# Targets Liquidsoap 2.2+. Older/newer installs may need small syntax tweaks
# to the http.get/http.post/json calls below — this was written and reviewed
# against the Laravel side, but not run against a live Liquidsoap install.
# Check `liquidsoap --list-functions | grep -E "http\.|json"` if it errors on
# startup and adjust accordingly.
# ---------------------------------------------------------------------------

settings.log.level.set(4)
settings.log.file.path.set("/var/log/liquidsoap/radio.log")

# ---- Config — fill these in for your server ----
api_base = "http://127.0.0.1/api/v1/radio"   # same server, so talk to it over localhost
engine_key = "REPLACE_WITH_RADIO_ENGINE_KEY" # must match RADIO_ENGINE_KEY in the app's .env

icecast_host = "localhost"
icecast_port = 8000
icecast_source_password = "REPLACE_WITH_ICECAST_SOURCE_PASSWORD" # must match icecast.xml
icecast_mount = "/radio.mp3"
# --------------------------------------------------

# Ask Laravel for the next track. Returns null() when the queue is empty.
def fetch_next() =
  try
    response = http.get(
      headers=[("X-Radio-Engine-Key", engine_key)],
      "#{api_base}/next"
    )
    if response.status_code == 200 then
      json.parse(response) ?? null()
    else
      null()
    end
  catch err do
    log("radio: fetch_next failed: #{err}")
    null()
  end
end

# Tell Laravel a track has started airing — it deletes the R2 file and logs
# the play in radio_queue_items/stream_logs. Safe to call as soon as the
# track starts: Liquidsoap has already downloaded the file locally by then,
# so deleting the R2 copy does not interrupt playback.
def mark_played(id) =
  if id != "" then
    try
      ignore(http.post(
        headers=[("X-Radio-Engine-Key", engine_key), ("Content-Type", "application/json")],
        data=json.stringify({id = id}),
        "#{api_base}/played"
      ))
    catch err do
      log("radio: mark_played(#{id}) failed: #{err}")
    end
  end
end

# Liquidsoap calls this whenever it needs another track for the queue.
# The track id travels with the request as "annotate:" metadata so on_track
# below can report the *exact* track that just started, regardless of how
# far ahead Liquidsoap prefetches.
def next_request() =
  data = fetch_next()
  if null.defined(data) then
    d = null.get(data)
    if null.defined(d) then
      id = d["id"]
      url = d["url"]
      [request.create("annotate:radio_id=\"#{id}\":#{url}")]
    else
      []
    end
  else
    []
  end
end

queue = request.dynamic.list(next_request)

# Fallback silence so the stream never drops even if the queue runs dry
# (e.g. nothing uploaded yet, or fetch_next errors out).
silence = blank(duration=5.)

radio = fallback(track_sensitive=false, [queue, silence])

radio = source.on_track(radio, fun (m) -> mark_played(m["radio_id"] ?? ""))

output.icecast(
  %mp3(bitrate=128),
  host=icecast_host,
  port=icecast_port,
  password=icecast_source_password,
  mount=icecast_mount,
  radio
)
