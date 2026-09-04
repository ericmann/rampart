<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HealthController extends Controller
{
    public function index(): JsonResponse
    {
        $checks = ['database' => false, 'redis' => false];

        try {
            DB::select('select 1');
            $checks['database'] = true;
        } catch (\Throwable) {
        }

        try {
            Redis::ping();
            $checks['redis'] = true;
        } catch (\Throwable) {
        }

        $ok = ! in_array(false, $checks, true);

        return response()->json(['status' => $ok ? 'ok' : 'degraded', 'checks' => $checks], $ok ? 200 : 503);
    }
}
