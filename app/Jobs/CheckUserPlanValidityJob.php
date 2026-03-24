<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class CheckUserPlanValidityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 2;
    protected $userId;

    public function __construct($userId) {
        $this->userId = $userId;
    }

    /**
     * 同一用户只保留一个 Job
     */
    public function uniqueId(): int
    {
        return $this->userId ?? 0;
    }

    /**
     * 执行 Job
     */
    public function handle(): void
    {

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
            return;
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