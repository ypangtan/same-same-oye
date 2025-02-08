<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Model\VendingMachine;

class VerifyVendingMachineKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-Vending-Machine-Key');

        if (!$apiKey || !VendingMachine::where('secret_key', $apiKey)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    
        return $next($request);
    }
}
