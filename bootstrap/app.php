<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (QueryException $e, Request $request) {
            if ($request->is('api/*')) {
                $sqlState = $e->errorInfo[0] ?? null;

                if ($sqlState === '23505') {
                    return response()->json([
                        'message' => 'Data yang kamu masukkan sudah digunakan. Periksa kembali email atau nomor telepon kamu.',
                    ], 422);
                }

                return response()->json([
                    'message' => 'Terjadi kendala pada server. Silakan coba lagi nanti.',
                ], 500);
            }
        });
    })->create();
