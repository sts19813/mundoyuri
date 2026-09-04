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
