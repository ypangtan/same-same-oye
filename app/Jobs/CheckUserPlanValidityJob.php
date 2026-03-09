<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;

class CheckUserPlanValidityJob implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 最多重试次数
     */
    public int $tries = 3;

    /**
     * 重试间隔（秒）
     */
    public int $backoff = 2;

    public function __construct(public int $userId) {}

    /**
     * 同一用户只保留一个 Job
     */
    public function uniqueId(): int
    {
        return $this->userId;
    }

    /**
     * 执行 Job
     */
    public function handle(): void
    {
        \DB::beginTransaction();
        try {
            $user = User::lockForUpdate()->find( $this->userId );
            $user->checkPlanValidity();
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error( 'update User plan validity user id'. $this->userId . ', error :' . $e->getMessage() );
            throw $e;
        }
    }

    /**
     * 只重试死锁错误
     */
    public function shouldRetry(\Throwable $e): bool
    {
        return $e instanceof \Illuminate\Database\QueryException
            && $e->getCode() === '40001';
    }
}