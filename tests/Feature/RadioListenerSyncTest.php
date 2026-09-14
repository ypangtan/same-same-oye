<?php

namespace Tests\Feature;

use App\Services\RadioListenerService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RadioListenerSyncTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'listener_test',
            'database.connections.listener_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
            'cache.default' => 'array',
            'services.radio.listener_tracking' => true,
            'services.radio.icecast_admin_url' => 'http://icecast.test/admin/listclients',
            'services.radio.icecast_admin_user' => 'admin',
            'services.radio.icecast_admin_password' => 'test-password',
            'services.radio.icecast_mount' => '/radio.mp3',
        ]);
        require_once database_path('migrations/2026_09_14_000001_create_radio_listener_sessions_table.php');
        (new \CreateRadioListenerSessionsTable)->up();
        Carbon::setTestNow(Carbon::parse('2026-09-14 10:00:00', 'UTC'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function xml($listeners): string
    {
        return '<icestats><source mount="/radio.mp3">'.$listeners.'</source></icestats>';
    }

    public function test_sessions_are_deduplicated_and_missing_connections_are_closed(): void
    {
        Http::fakeSequence()->push($this->xml(
            '<listener><id>1</id><ip>203.0.113.8</ip><connected>20</connected></listener>'.
            '<listener><ID>2</ID><IP>203.0.113.8</IP><Connected>10</Connected></listener>'
        ))->push($this->xml('<listener><id>1</id><ip>203.0.113.8</ip><connected>80</connected></listener>'))
          ->push($this->xml(''));
        $first = RadioListenerService::sync();
        $this->assertSame(2, $first['connections']);
        $this->assertSame(1, $first['unique_ips']);
        Carbon::setTestNow(now()->addMinute());
        $second = RadioListenerService::sync();
        $this->assertSame(1, $second['connections']);
        $this->assertSame(2, DB::table('radio_listener_sessions')->count());
        $this->assertNotNull(DB::table('radio_listener_sessions')->where('client_id', '2')->value('disconnected_at'));
        $last = RadioListenerService::sync();
        $this->assertSame(0, $last['unique_ips']);
        $this->assertSame(1, $last['today_unique_ips']);
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Basic '.base64_encode('admin:test-password')));
    }

    public function test_failed_sync_preserves_sessions_and_marks_counts_unknown(): void
    {
        Http::fakeSequence()->push($this->xml('<listener><id>1</id><ip>2001:db8::1</ip><connected>20</connected></listener>'))
            ->push('unauthorized', 401);
        RadioListenerService::sync();
        try {
            RadioListenerService::sync();
            $this->fail('Expected HTTP failure.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('401', $e->getMessage());
        }
        $this->assertSame(1, DB::table('radio_listener_sessions')->whereNull('disconnected_at')->count());
        $this->assertNull(RadioListenerService::summary()['unique_ips']);
    }

    public function test_reused_client_id_creates_new_session_and_stale_samples_are_unknown(): void
    {
        Http::fakeSequence()->push($this->xml('<listener><id>1</id><ip>203.0.113.8</ip><connected>600</connected></listener>'))
            ->push($this->xml('<listener><id>1</id><ip>203.0.113.8</ip><connected>5</connected></listener>'));
        RadioListenerService::sync();
        Carbon::setTestNow(now()->addMinute());
        RadioListenerService::sync();
        $this->assertSame(2, DB::table('radio_listener_sessions')->count());
        $this->assertSame(1, RadioListenerService::summary()['unique_ips']);
        Carbon::setTestNow(now()->addMinutes(3));
        $this->assertFalse(RadioListenerService::summary()['fresh']);
        $this->assertNull(RadioListenerService::summary()['unique_ips']);
    }

    /** @dataProvider invalidResponses */
    public function test_bad_responses_are_not_treated_as_zero_listeners($body): void
    {
        $this->expectException(\RuntimeException::class);
        RadioListenerService::parseListeners($body, '/radio.mp3');
    }

    public function invalidResponses(): array
    {
        return [
            ['<html>Login</html>'],
            ['<icestats><source mount="/other"/></icestats>'],
            ['<!DOCTYPE x [<!ENTITY x SYSTEM "file:///etc/passwd">]><icestats/>'],
            [$this->xml('<listener><id>1</id><ip>127.0.0.1</ip><connected>1</connected></listener>')],
            [$this->xml('<listener><id>1</id><ip>not-an-ip</ip><connected>1</connected></listener>')],
        ];
    }
}
