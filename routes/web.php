<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminPermissionController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AssistantMessageController as AdminAssistantMessageController;
use App\Http\Controllers\Admin\AssistantSettingController;
use App\Http\Controllers\Admin\BackblazeB2SettingController;
use App\Http\Controllers\Admin\BadgeController as AdminBadgeController;
use App\Http\Controllers\Admin\CatalogSectionController as AdminCatalogSectionController;
use App\Http\Controllers\Admin\CommunityRankController as AdminCommunityRankController;
use App\Http\Controllers\Admin\CommunityReportController as AdminCommunityReportController;
use App\Http\Controllers\Admin\EpisodeController as AdminEpisodeController;
use App\Http\Controllers\Admin\ForumCategoryController as AdminForumCategoryController;
use App\Http\Controllers\Admin\ForumController as AdminForumController;
use App\Http\Controllers\Admin\ForumModerationController as AdminForumModerationController;
use App\Http\Controllers\Admin\GenreController as AdminGenreController;
use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\Admin\SeriesController as AdminSeriesController;
use App\Http\Controllers\Admin\UserBadgeController as AdminUserBadgeController;
use App\Http\Controllers\AssistantMessageController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\CommunityReactionController;
use App\Http\Controllers\CommunityReportController;
use App\Http\Controllers\ContentSubmissionController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\EmailEpisodeNotificationPreferenceController;
use App\Http\Controllers\EpisodeSourcePlayerController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\ForumModerationController;
use App\Http\Controllers\ForumPostController;
use App\Http\Controllers\ForumSubscriptionController;
use App\Http\Controllers\ForumThreadController;
use App\Http\Controllers\LegacyProfileClaimController;
use App\Http\Controllers\LegacyProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicCatalogController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\SeriesFavoriteController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\UserBlockController;
use App\Http\Controllers\UserFollowController;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicCatalogController::class, 'home'])->name('home');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/index', [PublicCatalogController::class, 'home'])->name('legacy.index');
Route::view('/quienes-somos', 'about')->name('about');
Route::get('/test', function () {
    $template = file_get_contents(resource_path('views/index.blade.20260524-191213.bak.php'));

    abort_if($template === false, 404);

    return Blade::render($template, deleteCachedView: true);
})->name('test.index-backup');

Route::get('/inicio-catalogo', [CatalogController::class, 'home'])->name('catalog.home');
Route::get('/series', [CatalogController::class, 'series'])->name('catalog.series.index');
Route::get('/generos', [CatalogController::class, 'genres'])->name('catalog.genres.index');
Route::get('/generos/{genre:slug}', [CatalogController::class, 'genre'])->name('catalog.genres.show');
Route::get('/series/{series:slug}', [CatalogController::class, 'showSeries'])->name('catalog.series.show');
Route::get('/series/{series:slug}/episodios/{episode:slug}', [CatalogController::class, 'showEpisode'])->name('catalog.episodes.show');
Route::get('/player/episode-sources/{source}', EpisodeSourcePlayerController::class)->name('episode-sources.player');
Route::post('/comentarios', [CommentController::class, 'store'])->name('comments.store');
Route::post('/asistente/mensajes', [AssistantMessageController::class, 'store'])
    ->middleware('throttle:5,10')
    ->name('assistant-messages.store');
Route::get('/episodios', [PublicCatalogController::class, 'episodes'])->name('legacy.episodios');
Route::get('/episodios/{episode:slug}', [PublicCatalogController::class, 'episodes'])->name('public.episodes.show');
Route::get('/comunidad', [CommunityController::class, 'index'])->name('community.index');
Route::get('/comunidad/miembros', [CommunityController::class, 'members'])->name('community.members');
Route::get('/comunidad/preguntas', [QuestionController::class, 'index'])->name('questions.index');
Route::get('/comunidad/preguntas/nueva', [QuestionController::class, 'create'])->middleware('auth')->name('questions.create');
Route::get('/comunidad/preguntas/{thread:slug}', [QuestionController::class, 'show'])->name('questions.show');
Route::get('/comunidad/foros', [ForumController::class, 'index'])->name('forums.index');
Route::get('/comunidad/foros/{forum:slug}', [ForumController::class, 'show'])->name('forums.show');
Route::get('/comunidad/tema/{thread:slug}', [ForumThreadController::class, 'show'])->name('forum.threads.show');
Route::get('/comunidad/historicos', [LegacyProfileController::class, 'index'])->name('legacy-profiles.index');
Route::get('/miembros/historicos/{legacyProfile:slug}', [LegacyProfileController::class, 'show'])->name('legacy-profiles.show');
Route::get('/usuarios/{user}/seguidores', [ProfileController::class, 'followers'])->name('profiles.followers');
Route::get('/usuarios/{user}/siguiendo', [ProfileController::class, 'following'])->name('profiles.following');
Route::get('/usuarios/{user}/favoritas', [ProfileController::class, 'favorites'])->name('profiles.favorites');
Route::get('/usuarios/{user}/{alias?}', [ProfileController::class, 'show'])->name('profiles.show');

Route::get('/dashboard', [AdminDashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'admin.panel'])
    ->name('dashboard');

Route::middleware(['auth'])
    ->group(function () {
        Route::get('/profile', [ProfileController::class, 'showCurrent'])->name('profile.show');
        Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::redirect('/perfil', '/profile')->name('profile.spanish');
        Route::patch('/preferencias/correos-de-episodios', [EmailEpisodeNotificationPreferenceController::class, 'update'])
            ->name('email-episode-notifications.update');

        Route::get('/comunidad/historicos/reclamar', [LegacyProfileClaimController::class, 'create'])
            ->name('legacy-profile-claims.create');
        Route::post('/comunidad/historicos/reclamar', [LegacyProfileClaimController::class, 'store'])
            ->middleware('throttle:5,10')
            ->name('legacy-profile-claims.store');

        Route::post('/series/{series}/favorita', [SeriesFavoriteController::class, 'store'])
            ->name('series.favorites.store');
        Route::delete('/series/{series}/favorita', [SeriesFavoriteController::class, 'destroy'])
            ->name('series.favorites.destroy');
        Route::post('/usuarios/{user}/seguir', [UserFollowController::class, 'store'])
            ->name('users.follow.store');
        Route::delete('/usuarios/{user}/seguir', [UserFollowController::class, 'destroy'])
            ->name('users.follow.destroy');
        Route::post('/usuarios/{user}/bloquear', [UserBlockController::class, 'store'])
            ->name('users.block.store');
        Route::delete('/usuarios/{user}/bloquear', [UserBlockController::class, 'destroy'])
            ->name('users.block.destroy');
        Route::get('/bloqueos', [UserBlockController::class, 'index'])
            ->name('blocks.index');

        Route::get('/mensajes', [ConversationController::class, 'index'])
            ->name('messages.index');
        Route::get('/mensajes/{user}', [ConversationController::class, 'show'])
            ->name('messages.show');
        Route::post('/mensajes/{user}', [ConversationController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('messages.store');

        Route::get('/notificaciones', [NotificationController::class, 'index'])
            ->name('notifications.index');
        Route::get('/notificaciones/{notification}', [NotificationController::class, 'open'])
            ->name('notifications.open');
        Route::patch('/notificaciones', [NotificationController::class, 'readAll'])
            ->name('notifications.read-all');

        Route::post('/comunidad/reacciones', [CommunityReactionController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('community.reactions.store');
        Route::post('/comunidad/reportes', [CommunityReportController::class, 'store'])
            ->middleware('throttle:10,5')
            ->name('community.reports.store');

        Route::get('/comunidad/foros/{forum:slug}/nuevo-tema', [ForumThreadController::class, 'create'])->name('forum.threads.create');
        Route::post('/comunidad/foros/{forum:slug}/temas', [ForumThreadController::class, 'store'])->middleware('throttle:10,1')->name('forum.threads.store');
        Route::get('/comunidad/tema/{thread:slug}/editar', [ForumThreadController::class, 'edit'])->name('forum.threads.edit');
        Route::patch('/comunidad/tema/{thread:slug}', [ForumThreadController::class, 'update'])->name('forum.threads.update');
        Route::delete('/comunidad/tema/{thread:slug}', [ForumThreadController::class, 'destroy'])->name('forum.threads.destroy');
        Route::post('/comunidad/tema/{thread:slug}/respuestas', [ForumPostController::class, 'store'])->middleware('throttle:30,1')->name('forum.posts.store');
        Route::get('/comunidad/mensaje/{post}/editar', [ForumPostController::class, 'edit'])->name('forum.posts.edit');
        Route::patch('/comunidad/mensaje/{post}', [ForumPostController::class, 'update'])->name('forum.posts.update');
        Route::delete('/comunidad/mensaje/{post}', [ForumPostController::class, 'destroy'])->name('forum.posts.destroy');
        Route::post('/comunidad/tema/{thread:slug}/suscripcion', [ForumSubscriptionController::class, 'store'])->name('forum.subscriptions.store');
        Route::delete('/comunidad/tema/{thread:slug}/suscripcion', [ForumSubscriptionController::class, 'destroy'])->name('forum.subscriptions.destroy');
        Route::patch('/comunidad/tema/{thread:slug}/moderacion', [ForumModerationController::class, 'updateThread'])->name('forum.moderation.thread.update');
        Route::patch('/comunidad/mensaje/{post}/ocultar', [ForumModerationController::class, 'hidePost'])->name('forum.moderation.post.hide');

        Route::post('/comunidad/preguntas', [QuestionController::class, 'store'])->middleware('throttle:10,1')->name('questions.store');
        Route::post('/comunidad/preguntas/{thread:slug}/respuestas', [QuestionController::class, 'answer'])->middleware('throttle:30,1')->name('questions.answers.store');
        Route::post('/comunidad/preguntas/{thread:slug}/respuestas/{post}/aceptar', [QuestionController::class, 'accept'])->name('questions.answers.accept');
        Route::post('/comunidad/preguntas/{thread:slug}/votos', [QuestionController::class, 'voteQuestion'])->middleware('throttle:30,1')->name('questions.votes.store');
        Route::post('/comunidad/preguntas/respuestas/{post}/votos', [QuestionController::class, 'voteAnswer'])->middleware('throttle:30,1')->name('questions.answers.votes.store');

        Route::get('/aportes/nuevo', [ContentSubmissionController::class, 'create'])->name('submissions.create');
        Route::post('/aportes', [ContentSubmissionController::class, 'store'])
            ->middleware('can:create series')
            ->name('submissions.store');
    });

Route::middleware(['auth', 'admin.panel'])->prefix('admin')->group(function () {
    Route::get('/profile', [AdminProfileController::class, 'show'])->name('admin.profile.show');
    Route::get('/profile/edit', fn () => redirect()->route('admin.profile.show'))->name('admin.profile.edit');
    Route::put('/profile', [AdminProfileController::class, 'update'])->name('admin.profile.update');
    Route::put('/profile/password', [AdminProfileController::class, 'updatePassword'])->name('admin.profile.updatePassword');
});

// El panel administrativo solo está disponible para administradores y moderadores.
Route::middleware(['auth', 'verified', 'admin.panel'])->prefix('admin')->group(function () {
    Route::redirect('/', '/admin/dashboard');

    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/mensajes-miyu', [AdminAssistantMessageController::class, 'index'])
        ->name('admin.assistant-messages.index');
    Route::patch('/mensajes-miyu/{assistantMessage}', [AdminAssistantMessageController::class, 'update'])
        ->name('admin.assistant-messages.update');
    Route::get('/configuracion/miyu', [AssistantSettingController::class, 'edit'])
        ->middleware('admin')
        ->name('admin.settings.assistant.edit');
    Route::put('/configuracion/miyu', [AssistantSettingController::class, 'update'])
        ->middleware('admin')
        ->name('admin.settings.assistant.update');

    Route::get('/configuracion/backblaze-b2', [BackblazeB2SettingController::class, 'edit'])->middleware('admin')->name('admin.settings.backblaze-b2.edit');
    Route::put('/configuracion/backblaze-b2', [BackblazeB2SettingController::class, 'update'])->middleware('admin')->name('admin.settings.backblaze-b2.update');
    Route::post('/configuracion/backblaze-b2/verificar', [BackblazeB2SettingController::class, 'verify'])->middleware('admin')->name('admin.settings.backblaze-b2.verify');

    Route::resource('secciones-catalogo', AdminCatalogSectionController::class)
        ->except(['show', 'destroy'])
        ->parameters(['secciones-catalogo' => 'catalogSection'])
        ->names([
            'index' => 'admin.catalog-sections.index',
            'create' => 'admin.catalog-sections.create',
            'store' => 'admin.catalog-sections.store',
            'edit' => 'admin.catalog-sections.edit',
            'update' => 'admin.catalog-sections.update',
        ]);

    Route::redirect('/users', '/admin/usuarios');
    Route::redirect('/genres', '/admin/generos');
    Route::redirect('/episodes', '/admin/episodios');
    Route::redirect('/moderation', '/admin/validacion');

    // Gestión de Usuarios
    Route::patch('/usuarios/{user}/notificaciones-por-correo', [AdminUserController::class, 'updateEmailNotifications'])
        ->name('admin.users.email-notifications.update');
    Route::delete('/usuarios/{user}/firma', [AdminUserController::class, 'destroySignature'])
        ->name('admin.users.signature.destroy');
    Route::patch('/usuarios/{user}/firma/suspension', [AdminUserController::class, 'updateSignatureSuspension'])
        ->name('admin.users.signature.suspension.update');
    Route::post('/usuarios/{user}/insignias', [AdminUserBadgeController::class, 'store'])
        ->name('admin.users.badges.store');
    Route::delete('/usuarios/{user}/insignias/{badge}', [AdminUserBadgeController::class, 'destroy'])
        ->name('admin.users.badges.destroy');

    Route::resource('usuarios', AdminUserController::class)->parameters([
        'usuarios' => 'user',
    ])->names([
        'index' => 'admin.users.index',
        'create' => 'admin.users.create',
        'store' => 'admin.users.store',
        'show' => 'admin.users.show',
        'edit' => 'admin.users.edit',
        'update' => 'admin.users.update',
        'destroy' => 'admin.users.destroy',
    ]);

    Route::resource('rangos-comunidad', AdminCommunityRankController::class)
        ->except('show')
        ->parameters(['rangos-comunidad' => 'communityRank'])
        ->names([
            'index' => 'admin.community-ranks.index',
            'create' => 'admin.community-ranks.create',
            'store' => 'admin.community-ranks.store',
            'edit' => 'admin.community-ranks.edit',
            'update' => 'admin.community-ranks.update',
            'destroy' => 'admin.community-ranks.destroy',
        ]);

    Route::resource('perfiles-historicos', App\Http\Controllers\Admin\LegacyProfileController::class)
        ->except(['show', 'destroy'])
        ->parameters(['perfiles-historicos' => 'legacyProfile'])
        ->names([
            'index' => 'admin.legacy-profiles.index',
            'create' => 'admin.legacy-profiles.create',
            'store' => 'admin.legacy-profiles.store',
            'edit' => 'admin.legacy-profiles.edit',
            'update' => 'admin.legacy-profiles.update',
        ]);

    Route::get('/perfiles-historicos/reclamaciones', [App\Http\Controllers\Admin\LegacyProfileClaimController::class, 'index'])
        ->middleware('admin')
        ->name('admin.legacy-profile-claims.index');
    Route::patch('/perfiles-historicos/reclamaciones/{legacyProfileClaim}', [App\Http\Controllers\Admin\LegacyProfileClaimController::class, 'update'])
        ->middleware('admin')
        ->name('admin.legacy-profile-claims.update');

    Route::resource('insignias-comunidad', AdminBadgeController::class)
        ->except('show')
        ->parameters(['insignias-comunidad' => 'badge'])
        ->names([
            'index' => 'admin.badges.index',
            'create' => 'admin.badges.create',
            'store' => 'admin.badges.store',
            'edit' => 'admin.badges.edit',
            'update' => 'admin.badges.update',
            'destroy' => 'admin.badges.destroy',
        ]);

    Route::resource('categorias-foros', AdminForumCategoryController::class)
        ->except('show')
        ->parameters(['categorias-foros' => 'forumCategory'])
        ->names([
            'index' => 'admin.forum-categories.index',
            'create' => 'admin.forum-categories.create',
            'store' => 'admin.forum-categories.store',
            'edit' => 'admin.forum-categories.edit',
            'update' => 'admin.forum-categories.update',
            'destroy' => 'admin.forum-categories.destroy',
        ]);

    Route::resource('foros', AdminForumController::class)
        ->except(['show', 'destroy'])
        ->parameters(['foros' => 'forum'])
        ->names([
            'index' => 'admin.forums.index',
            'create' => 'admin.forums.create',
            'store' => 'admin.forums.store',
            'edit' => 'admin.forums.edit',
            'update' => 'admin.forums.update',
        ]);

    Route::get('/temas-foros', [AdminForumModerationController::class, 'threads'])->name('admin.forum-topics.index');
    Route::get('/moderacion-foros', [AdminForumModerationController::class, 'index'])->name('admin.forum-moderation.index');
    Route::get('/validacion/comunidad', [AdminCommunityReportController::class, 'index'])->name('admin.community-reports.index');
    Route::patch('/validacion/comunidad/{communityReport}', [AdminCommunityReportController::class, 'update'])->name('admin.community-reports.update');
    Route::post('/validacion/comunidad/{communityReport}/acciones', [AdminCommunityReportController::class, 'action'])->name('admin.community-reports.action');

    Route::resource('roles', AdminRoleController::class)->parameters([
        'roles' => 'role',
    ])->names([
        'index' => 'admin.roles.index',
        'create' => 'admin.roles.create',
        'store' => 'admin.roles.store',
        'show' => 'admin.roles.show',
        'edit' => 'admin.roles.edit',
        'update' => 'admin.roles.update',
        'destroy' => 'admin.roles.destroy',
    ]);

    Route::resource('permisos', AdminPermissionController::class)
        ->only(['index', 'store', 'destroy'])
        ->parameters([
            'permisos' => 'permission',
        ])
        ->names([
            'index' => 'admin.permissions.index',
            'store' => 'admin.permissions.store',
            'destroy' => 'admin.permissions.destroy',
        ]);

    Route::resource('generos', AdminGenreController::class)->parameters([
        'generos' => 'genre',
    ])->names([
        'index' => 'admin.genres.index',
        'create' => 'admin.genres.create',
        'store' => 'admin.genres.store',
        'show' => 'admin.genres.show',
        'edit' => 'admin.genres.edit',
        'update' => 'admin.genres.update',
        'destroy' => 'admin.genres.destroy',
    ]);

    Route::resource('series', AdminSeriesController::class)->parameters([
        'series' => 'series',
    ])->names([
        'index' => 'admin.series.index',
        'create' => 'admin.series.create',
        'store' => 'admin.series.store',
        'show' => 'admin.series.show',
        'edit' => 'admin.series.edit',
        'update' => 'admin.series.update',
        'destroy' => 'admin.series.destroy',
    ]);

    Route::resource('episodios', AdminEpisodeController::class)->parameters([
        'episodios' => 'episode',
    ])->names([
        'index' => 'admin.episodes.index',
        'create' => 'admin.episodes.create',
        'store' => 'admin.episodes.store',
        'show' => 'admin.episodes.show',
        'edit' => 'admin.episodes.edit',
        'update' => 'admin.episodes.update',
        'destroy' => 'admin.episodes.destroy',
    ]);

    Route::get('/validacion', [ModerationController::class, 'index'])->name('admin.moderation.index');
    Route::post('/validacion/series/{series}/approve', [ModerationController::class, 'approveSeries'])->name('admin.moderation.series.approve');
    Route::post('/validacion/series/{series}/reject', [ModerationController::class, 'rejectSeries'])->name('admin.moderation.series.reject');
    Route::post('/validacion/episodios/{episode}/approve', [ModerationController::class, 'approveEpisode'])->name('admin.moderation.episodes.approve');
    Route::post('/validacion/episodios/{episode}/reject', [ModerationController::class, 'rejectEpisode'])->name('admin.moderation.episodes.reject');
});

require __DIR__.'/auth.php';

// Las secciones del catálogo viven directamente en el dominio: /anime, /series-gl,
// y las que se creen desde el panel. Se declara al final para no interceptar rutas del sitio.
Route::get('/{sectionSlug}', [PublicCatalogController::class, 'section'])
    ->where('sectionSlug', '[A-Za-z0-9-]+')
    ->name('catalog.sections.show');
