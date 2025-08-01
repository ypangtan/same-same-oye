<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogCartOrderActivity
{
    public function handle( Request $request, Closure $next )
    {

        $prefix = explode('/', ltrim($request->path(), '/'))[2];

        // Mapping prefixes to log channels
        $channelMap = [
            'vouchers'   => 'voucher',
            'checkin'    => 'checkin',
            'points'    => 'points',
            'otp' => 'otp',
            'announcements' => 'announcement',
            'users' => 'user_pre_auth',
        ];
    
        // Log request data
        $channel = $channelMap[$prefix] ?? 'daily'; // fallback channel

        Log::channel($channel)->info("API Request - [$prefix]", [
            'uri'     => $request->getRequestUri(),
            'method'  => $request->getMethod(),
            'user_id' => auth()->user()->id ?? null,
            'payload' => $request->all(),
        ]);
    
        return $next($request);
    }
}

