<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Api\V1\ICardController;
use App\Http\Controllers\Api\V1\JoiningLetterController;

// Public QR Verification Page
Route::get('/verify/{token}', [\App\Http\Controllers\QrVerificationController::class, 'verify'])
    ->name('qr.verify');

Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index']);
Route::get('/robots.txt', [\App\Http\Controllers\SitemapController::class, 'robots']);

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/favicon.ico', function () {
    $faviconPath = \App\Models\Setting::getValue('company_favicon', 'branding/favicon.ico');
    if ($faviconPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($faviconPath)) {
        return response()->file(storage_path('app/public/' . $faviconPath));
    }
    abort(404);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// ── iCard & Joining Letter Routes (Accessible via signed URLs) ────────────
Route::get('/icard/download/{userId?}', [ICardController::class, 'download'])
     ->middleware('signed')
     ->name('icard.download');

Route::get('/joining-letter/download/{userId}', [JoiningLetterController::class, 'download'])
     ->middleware('signed')
     ->name('joining-letter.download');

Route::middleware(['web', 'auth:sanctum', 'admin'])->group(function () {
    Route::get('/admin/icard/{userId}/preview', [ICardController::class, 'preview'])
         ->name('icard.preview');
    Route::get('/admin/joining-letter/{user}/preview', [JoiningLetterController::class, 'preview'])
         ->name('joining-letter.preview');
});
