<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class CheckUserPlanValidityJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 2;
    public int $uniqueFor = 60;
    protected $userId;

    public function __construct( $userId ) {
        $this->userId = $userId;
    }

    public function uniqueId(): string {
        return (string) $this->userId;
    }

    public static function dispatchSafe( $userId ) {
        \Cache::put("check_plan_pending_{$userId}", true, now()->addMinutes(5));
        static::dispatch($userId)->delay(now()->addSeconds(1));
    }

    /**
     * 执行 Job
     */
    public function handle() {
        \Cache::forget("check_plan_pending_{$this->userId}");

        \DB::beginTransaction();
        try {
            $user = User::lockForUpdate()->find( $this->userId );
            if (!$user) {
                \Log::warning('CheckUserPlanValidityJob: User not found, id: ' . $this->userId);
                return;
            }
            $user->checkPlanValidity();
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error( 'update User plan validity user id'. $this->userId . ', error :' . $e->getMessage() );
            throw '';
        }

        if (\Cache::get("check_plan_pending_{$this->userId}")) {
            \Cache::forget("check_plan_pending_{$this->userId}");
            CheckUserPlanValidityJob::dispatch($this->userId);
        }
    }

    /**
     * 只重试死锁错误
     */
    public function shouldRetry(\Throwable $e) {
        return $e instanceof \Illuminate\Database\QueryException
            && $e->getCode() === '40001';
    }
}