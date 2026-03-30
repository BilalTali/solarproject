<?php

use App\Http\Controllers\Admin\ICardController;
use App\Http\Controllers\Admin\JoiningLetterController;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;


// Public QR Verification Page
Route::get('/verify/{token}', [\App\Http\Controllers\Solar\QrVerificationController::class, 'verify'])
    ->name('qr.verify');

Route::get('/sitemap.xml', [\App\Http\Controllers\Solar\SitemapController::class, 'index']);
Route::get('/robots.txt', [\App\Http\Controllers\Solar\SitemapController::class, 'robots']);

Route::get('/', function () {
    return response()->json(['message' => 'API is running']);
});

Route::get('/favicon.ico', function () {
    $faviconPath = \App\Models\Setting::getValue('company_favicon', 'branding/favicon.ico');
    if ($faviconPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($faviconPath)) {
        return response()->file(storage_path('app/public/'.$faviconPath));
    }
    abort(404);
});

Route::get('/icons/icon-{size}.png', function ($size) {
    $faviconPath = \App\Models\Setting::getValue('company_favicon');
    if ($faviconPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($faviconPath)) {
        return response()->file(storage_path('app/public/'.$faviconPath));
    }
    // Fallback to a default if not found
    $fallback = public_path('vite.svg');

    return response()->file($fallback);
})->where('size', '192|512');



// ── Admin Preview Routes (Keep in web for easier preview) ────────────
Route::middleware(['web', 'auth:sanctum', 'admin'])->group(function () {
    Route::get('/admin/icard/{userId}/preview', [ICardController::class, 'preview'])
        ->name('icard.preview');
    Route::get('/admin/joining-letter/{user}/preview', [JoiningLetterController::class, 'preview'])
        ->name('joining-letter.preview');
});
