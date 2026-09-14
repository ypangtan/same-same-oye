<?php

namespace Tests\Feature;

use App\Models\RadioQueueItem;
use App\Services\RadioQueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RadioPlaybackStateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'radio_test',
            'database.connections.radio_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
            'services.radio.icecast_status_url' => 'http://icecast.test/status',
        ]);
        Schema::create('radio_queue_items', function ($table) {
            $table->id();
            $table->string('title');
            $table->string('file')->nullable();
            $table->string('image')->nullable();
            $table->integer('status');
            $table->timestamp('played_at')->nullable();
            $table->timestamps();
        });
        RadioQueueItem::flushEventListeners();
        Storage::fake('r2');
        Storage::disk('r2')->buildTemporaryUrlsUsing(fn ($path) => 'https://covers.test/'.$path);
        Http::fake(['icecast.test/*' => Http::response([
            'icestats' => ['source' => ['title' => 'Yesterday stale title', 'listeners' => 0]],
        ])]);
    }

    private function track($title)
    {
        $track = RadioQueueItem::create([
            'title' => $title, 'status' => RadioQueueItem::STATUS_RESERVED,
            'file' => $title.'.mp3', 'image' => $title.'.jpg',
        ]);
        Storage::disk('r2')->put($track->file, 'audio');
        Storage::disk('r2')->put($track->image, 'cover');
        return $track;
    }

    private function transition($id)
    {
        return RadioQueueService::markPlayed(Request::create('/', 'POST', ['id' => $id]));
    }

    public function test_transitions_keep_live_cover_and_complete_last_track_on_silence(): void
    {
        $first = $this->track('first');
        $second = $this->track('second');
        $this->transition($first->encrypted_id);
        $this->assertSame(25, $first->fresh()->status);
        Storage::disk('r2')->assertExists('first.jpg');
        $playing = RadioQueueService::nowPlaying()->getData(true);
        $this->assertSame('first', $playing['title']);
        $this->assertSame('https://covers.test/first.jpg', $playing['image']);
        $started = $first->fresh()->played_at->toDateTimeString();
        $this->transition($first->encrypted_id);
        $this->assertSame($started, $first->fresh()->played_at->toDateTimeString());

        $this->transition($second->encrypted_id);
        $this->assertSame(30, $first->fresh()->status);
        $this->assertSame(25, $second->fresh()->status);
        Storage::disk('r2')->assertMissing('first.mp3');
        Storage::disk('r2')->assertMissing('first.jpg');
        Storage::disk('r2')->assertExists('second.jpg');
        // A retry for an already completed track must not displace the live track.
        $this->transition($first->encrypted_id);
        $this->assertSame(25, $second->fresh()->status);
        $blocked = RadioQueueService::deleteItem(Request::create('/', 'POST', ['id' => $second->encrypted_id]));
        $this->assertSame(422, $blocked->getStatusCode());

        $this->transition(null);
        $this->assertSame(30, $second->fresh()->status);
        Storage::disk('r2')->assertMissing('second.jpg');
        $idle = RadioQueueService::nowPlaying()->getData(true);
        $this->assertNull($idle['title']);
        $this->assertNull($idle['image']);
        $this->transition('');
        $this->assertSame(0, RadioQueueItem::where('status', 25)->count());
    }
}
