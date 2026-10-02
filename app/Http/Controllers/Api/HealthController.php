<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Smoke test y monitoreo (P0-5). Es la única ruta de `/api` que no usa el
 * envelope `{data}`: su contrato es plano (`{"status":"ok"}`) para que
 * cualquier monitor o load balancer lo pueda consumir.
 */
class HealthController extends Controller
{
    public function show()
    {
        try {
            DB::connection()->getPdo();
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Database unavailable.',
            ], 503);
        }

        return response()->json(['status' => 'ok']);
    }
}
