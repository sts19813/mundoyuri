<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminPermissionController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\BackblazeB2SettingController;
use App\Http\Controllers\Admin\EpisodeController as AdminEpisodeController;
use App\Http\Controllers\Admin\GenreController as AdminGenreController;
use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\Admin\SeriesController as AdminSeriesController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContentSubmissionController;
use App\Http\Controllers\EpisodeSourcePlayerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicCatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicCatalogController::class, 'home'])->name('home');
Route::get('/index', [PublicCatalogController::class, 'home'])->name('legacy.index');

Route::get('/inicio-catalogo', [CatalogController::class, 'home'])->name('catalog.home');
Route::get('/series', [CatalogController::class, 'series'])->name('catalog.series.index');
Route::get('/generos', [CatalogController::class, 'genres'])->name('catalog.genres.index');
Route::get('/generos/{genre:slug}', [CatalogController::class, 'genre'])->name('catalog.genres.show');
Route::get('/series/{series:slug}', [CatalogController::class, 'showSeries'])->name('catalog.series.show');
Route::get('/series/{series:slug}/episodios/{episode:slug}', [CatalogController::class, 'showEpisode'])->name('catalog.episodes.show');
Route::get('/player/episode-sources/{source}', EpisodeSourcePlayerController::class)->name('episode-sources.player');
Route::post('/comentarios', [CommentController::class, 'store'])->name('comments.store');
Route::get('/episodios', [PublicCatalogController::class, 'episodes'])->name('legacy.episodios');
Route::get('/episodios/{episode:slug}', [PublicCatalogController::class, 'episodes'])->name('public.episodes.show');

Route::get('/dashboard', [AdminDashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])
    ->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::redirect('/perfil', '/profile')->name('profile.spanish');

        Route::get('/aportes/nuevo', fn () => redirect()->route('admin.series.create'))->name('submissions.create');
        Route::post('/aportes', [ContentSubmissionController::class, 'store'])
            ->middleware('can:create series')
            ->name('submissions.store');
    });

Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/profile', [AdminProfileController::class, 'show'])->name('admin.profile.show');
    Route::get('/profile/edit', fn () => redirect()->route('admin.profile.show'))->name('admin.profile.edit');
    Route::put('/profile', [AdminProfileController::class, 'update'])->name('admin.profile.update');
    Route::put('/profile/password', [AdminProfileController::class, 'updatePassword'])->name('admin.profile.updatePassword');
});

// Panel compartido: cada controlador protege sus acciones mediante permisos.
Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {
    Route::redirect('/', '/admin/dashboard');

    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/configuracion/backblaze-b2', [BackblazeB2SettingController::class, 'edit'])->middleware('admin')->name('admin.settings.backblaze-b2.edit');
    Route::put('/configuracion/backblaze-b2', [BackblazeB2SettingController::class, 'update'])->middleware('admin')->name('admin.settings.backblaze-b2.update');
    Route::post('/configuracion/backblaze-b2/verificar', [BackblazeB2SettingController::class, 'verify'])->middleware('admin')->name('admin.settings.backblaze-b2.verify');

    Route::redirect('/users', '/admin/usuarios');
    Route::redirect('/genres', '/admin/generos');
    Route::redirect('/episodes', '/admin/episodios');
    Route::redirect('/moderation', '/admin/validacion');

    // Gestión de Usuarios
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
