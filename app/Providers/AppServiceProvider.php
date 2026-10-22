<?php

namespace App\Providers;

use App\Listeners\RecordUserLogin;
use App\Listeners\SendWelcomeEmail;
use App\Models\Badge;
use App\Models\CommunityReport;
use App\Models\Forum;
use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\LegacyProfile;
use App\Models\LegacyProfileClaim;
use App\Models\User;
use App\Policies\BadgePolicy;
use App\Policies\CommunityReportPolicy;
use App\Policies\ForumCategoryPolicy;
use App\Policies\ForumPolicy;
use App\Policies\ForumPostPolicy;
use App\Policies\ForumThreadPolicy;
use App\Policies\LegacyProfileClaimPolicy;
use App\Policies\LegacyProfilePolicy;
use App\Services\CommunityRankResolver;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Verified;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
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
        Paginator::useBootstrapFive();

        RateLimiter::for('registration', function (Request $request): array {
            $cloudflareAddress = $request->header('CF-Connecting-IP');
            $visitorAddress = filter_var($cloudflareAddress, FILTER_VALIDATE_IP)
                ? $cloudflareAddress
                : $request->ip();
            $email = Str::lower(trim((string) $request->input('email')));

            return [
                Limit::perMinutes(10, 3)->by('registration:ip:'.$visitorAddress),
                Limit::perDay(3)->by('registration:email:'.hash('sha256', $email)),
            ];
        });

        Event::listen(Login::class, RecordUserLogin::class);
        Event::listen(Verified::class, fn (Verified $event) => app(SendWelcomeEmail::class)->send($event->user));

        Gate::policy(Badge::class, BadgePolicy::class);
        Gate::policy(CommunityReport::class, CommunityReportPolicy::class);
        Gate::policy(ForumCategory::class, ForumCategoryPolicy::class);
        Gate::policy(Forum::class, ForumPolicy::class);
        Gate::policy(ForumThread::class, ForumThreadPolicy::class);
        Gate::policy(ForumPost::class, ForumPostPolicy::class);
        Gate::policy(LegacyProfile::class, LegacyProfilePolicy::class);
        Gate::policy(LegacyProfileClaim::class, LegacyProfileClaimPolicy::class);

        Gate::before(function (User $user): ?bool {
            return $user->isAdmin() ? true : null;
        });
    }
}
