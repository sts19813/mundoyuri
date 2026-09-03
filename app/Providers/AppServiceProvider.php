<?php

namespace App\Providers;

use App\Listeners\RecordUserLogin;
use App\Listeners\SendWelcomeEmail;
use App\Models\User;
use App\Services\CommunityRankResolver;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(CommunityRankResolver::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Login::class, RecordUserLogin::class);
        Event::listen(Registered::class, SendWelcomeEmail::class);

        Gate::before(function (User $user): ?bool {
            return $user->isAdmin() ? true : null;
        });
    }
}
