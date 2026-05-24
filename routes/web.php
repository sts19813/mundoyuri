<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\EpisodeController as AdminEpisodeController;
use App\Http\Controllers\Admin\GenreController as AdminGenreController;
use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\Admin\SeriesController as AdminSeriesController;
use App\Http\Controllers\ContentSubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'home'])->name('home');
Route::get('/series', [CatalogController::class, 'series'])->name('catalog.series.index');
Route::get('/generos', function () {
    if (auth()->check() && auth()->user()->role === 'admin') {
        return redirect()->route('admin.genres.index');
    }

    return app(CatalogController::class)->genres();
})->name('catalog.genres.index');
Route::get('/generos/{genre:slug}', [CatalogController::class, 'genre'])->name('catalog.genres.show');
Route::get('/series/{series:slug}', [CatalogController::class, 'showSeries'])->name('catalog.series.show');
Route::get('/series/{series:slug}/episodios/{episode:slug}', [CatalogController::class, 'showEpisode'])->name('catalog.episodes.show');
Route::post('/comentarios', [CommentController::class, 'store'])->name('comments.store');
Route::redirect('/episodios', '/series');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware(['auth'])
    ->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::redirect('/perfil', '/profile');

        Route::get('/aportes/nuevo', [ContentSubmissionController::class, 'create'])->name('submissions.create');
        Route::post('/aportes', [ContentSubmissionController::class, 'store'])->name('submissions.store');
    });


// Rutas del Panel Admin - Protegidas por middleware 'auth' y 'admin'
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Gestión de Usuarios
    Route::resource('users', AdminUserController::class)->names([
        'index' => 'admin.users.index',
        'create' => 'admin.users.create',
        'store' => 'admin.users.store',
        'show' => 'admin.users.show',
        'edit' => 'admin.users.edit',
        'update' => 'admin.users.update',
        'destroy' => 'admin.users.destroy',
    ]);

    Route::resource('roles', AdminRoleController::class)->names([
        'index' => 'admin.roles.index',
        'create' => 'admin.roles.create',
        'store' => 'admin.roles.store',
        'show' => 'admin.roles.show',
        'edit' => 'admin.roles.edit',
        'update' => 'admin.roles.update',
        'destroy' => 'admin.roles.destroy',
    ]);

    Route::resource('genres', AdminGenreController::class)->names([
        'index' => 'admin.genres.index',
        'create' => 'admin.genres.create',
        'store' => 'admin.genres.store',
        'show' => 'admin.genres.show',
        'edit' => 'admin.genres.edit',
        'update' => 'admin.genres.update',
        'destroy' => 'admin.genres.destroy',
    ]);

    Route::resource('series', AdminSeriesController::class)->names([
        'index' => 'admin.series.index',
        'create' => 'admin.series.create',
        'store' => 'admin.series.store',
        'show' => 'admin.series.show',
        'edit' => 'admin.series.edit',
        'update' => 'admin.series.update',
        'destroy' => 'admin.series.destroy',
    ]);

    Route::resource('episodes', AdminEpisodeController::class)->names([
        'index' => 'admin.episodes.index',
        'create' => 'admin.episodes.create',
        'store' => 'admin.episodes.store',
        'show' => 'admin.episodes.show',
        'edit' => 'admin.episodes.edit',
        'update' => 'admin.episodes.update',
        'destroy' => 'admin.episodes.destroy',
    ]);

    Route::get('/moderation', [ModerationController::class, 'index'])->name('admin.moderation.index');
    Route::post('/moderation/series/{series}/approve', [ModerationController::class, 'approveSeries'])->name('admin.moderation.series.approve');
    Route::post('/moderation/series/{series}/reject', [ModerationController::class, 'rejectSeries'])->name('admin.moderation.series.reject');
    Route::post('/moderation/episodes/{episode}/approve', [ModerationController::class, 'approveEpisode'])->name('admin.moderation.episodes.approve');
    Route::post('/moderation/episodes/{episode}/reject', [ModerationController::class, 'rejectEpisode'])->name('admin.moderation.episodes.reject');

    // Perfil del Admin
    Route::get('/profile', [AdminProfileController::class, 'show'])->name('admin.profile.show');
    Route::get('/profile/edit', [AdminProfileController::class, 'edit'])->name('admin.profile.edit');
    Route::put('/profile', [AdminProfileController::class, 'update'])->name('admin.profile.update');
    Route::put('/profile/password', [AdminProfileController::class, 'updatePassword'])->name('admin.profile.updatePassword');
});

require __DIR__.'/auth.php';
