<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/*',
            'ajax/*',
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'crm.admin' => \App\Http\Middleware\EnsureCrmAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return response()->json([
                    'message' => 'Too many requests submitted. Please wait 60 seconds before trying again.',
                ], 429);
            }

            return back()->with('error', 'Too many requests submitted. Please wait 60 seconds before trying again.');
        });

        $exceptions->respond(function ($response, \Throwable $exception, Request $request) {
            $status = $response->getStatusCode();
            if (in_array($status, [403, 404, 419, 500, 503], true) && ! app()->environment(['local', 'testing'])) {
                if ($request->header('X-Inertia') || (! $request->expectsJson() && ! $request->is('api/*') && ! $request->is('ajax/*') && ! $request->is('downloads/*'))) {
                    return \Inertia\Inertia::render('Error', ['status' => $status])
                        ->toResponse($request)
                        ->setStatusCode($status);
                }
            }

            return $response;
        });
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
        // Loop 1: Poll market requirements every 5 minutes
        $schedule->command('market:poll-requirements')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground();

        // Loop 2: Process sales cadence daily at 09:30 AM IST
        $schedule->command('crm:process-cadence')
            ->dailyAt('09:30')
            ->withoutOverlapping();

        // Loop 3: Send Founder Morning Battle Card at 08:00 AM IST
        $schedule->command('crm:morning-battlecard')
            ->dailyAt('08:00')
            ->withoutOverlapping();
    })->create();
