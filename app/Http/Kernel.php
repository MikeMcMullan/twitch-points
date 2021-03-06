<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \App\Http\Middleware\CorsHeaders::class
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\SetupBotCommands::class,
        ],
        'api' => [
            'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\SetupBotCommands::class,
        ],
        'twitch-auth' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.api'  => \App\Http\Middleware\AuthenticateApi::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'jwt.auth' => \Tymon\JWTAuth\Middleware\GetUserFromToken::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'featureDetection' => \App\Http\Middleware\FeatureDetection::class,
        'resolveTwitchUsername' => \App\Http\Middleware\ResolveTwitchUsername::class,
    ];
}
