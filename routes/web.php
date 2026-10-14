<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/welcome', function () {
    return view('welcome');
})->name('welcome');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- Maintenance & Diagnostic Routes (Protected: Requires Admin or MAINTENANCE_TOKEN) ---
Route::middleware('maintenance.token')->group(function () {
    Route::get('/run-migrate', function () {
        Artisan::call('migrate', ['--force' => true]);
        return 'Database migration successfully!';
    });

    Route::get('/run-seed', function () {
        Artisan::call('db:seed', ['--force' => true]);
        return 'Database seeding completed successfully!';
    });

    Route::get('/storage-link', function () {
        Artisan::call('storage:link');
        return 'Storage link created successfully!';
    });

    Route::get('/clear-config', function () {
        Artisan::call('optimize:clear');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        return 'Config cache cleared successfully!';
    });

    Route::get('/fix-server-issues', function () {
        Artisan::call('optimize:clear');
        Artisan::call('storage:link');
        $syncResult = \App\Helpers\UploadStorageHelper::syncDirectories();
        return response()->json([
            'status' => 'success',
            'message' => 'Server maintenance tasks and storage sync completed.',
            'details' => ["Laravel optimize:clear executed.", "Storage link executed.", "Synced files: " . ($syncResult['synced_files'] ?? 0)]
        ]);
    });
});


Route::get('/founder/activate/{token}', [\App\Http\Controllers\API\Founder\FounderController::class, 'activate'])->name('web.founder.activate');

Route::middleware('auth')->prefix('founder')->group(function () {
    Route::get('/review', [\App\Http\Controllers\Web\Frontend\FounderReviewController::class, 'show'])->name('founder.review.show');
    Route::post('/review', [\App\Http\Controllers\Web\Frontend\FounderReviewController::class, 'submit'])->name('founder.review.submit');
    Route::get('/review/thank-you', [\App\Http\Controllers\Web\Frontend\FounderReviewController::class, 'thankyou'])->name('founder.review.thankyou');
});

Route::get('/subscription/success', function () {
    return view('frontend.subscription.success');
})->name('subscription.success');

require __DIR__ . '/auth.php';

// -- Paddle webhook route override --------------------------------------------
// Cashier registers this route automatically, but we prepend our payload
// validation middleware so malformed/empty requests return 400 (not 500).
// CSRF is already excepted for this path in bootstrap/app.php.
use Laravel\Paddle\Http\Controllers\WebhookController as PaddleWebhookController;

Route::post('/paddle/webhook', PaddleWebhookController::class)
    ->middleware('paddle.payload.validate')
    ->name('cashier.webhook');

// -- Media / Storage Image Serving Fallbacks ----------------------------------
// Ensures /storage/... and /uploads/... URLs always resolve properly on local and production.
Route::get('/storage/{path}', function (string $path) {
    // 1. Direct storage/app/public
    $storagePath = storage_path('app/public/' . $path);
    if (is_file($storagePath)) {
        return response()->file($storagePath, ['Cache-Control' => 'public, max-age=86400']);
    }
    // 2. Direct public folder
    $directPublic = public_path($path);
    if (is_file($directPublic)) {
        return response()->file($directPublic, ['Cache-Control' => 'public, max-age=86400']);
    }
    // 3. Sub-folder under uploads
    if (str_starts_with($path, 'uploads/')) {
        $sub = substr($path, strlen('uploads/'));
        $publicSub = public_path('uploads/' . $sub);
        if (is_file($publicSub)) {
            return response()->file($publicSub, ['Cache-Control' => 'public, max-age=86400']);
        }
    }
    abort(404);
})->where('path', '.*');

Route::get('/uploads/{path}', function (string $path) {
    // 1. Direct public/uploads
    $publicUpload = public_path('uploads/' . $path);
    if (is_file($publicUpload)) {
        return response()->file($publicUpload, ['Cache-Control' => 'public, max-age=86400']);
    }
    // 2. Storage public/uploads
    $storageUpload = storage_path('app/public/uploads/' . $path);
    if (is_file($storageUpload)) {
        return response()->file($storageUpload, ['Cache-Control' => 'public, max-age=86400']);
    }
    // 3. Storage public root
    $directStorage = storage_path('app/public/' . $path);
    if (is_file($directStorage)) {
        return response()->file($directStorage, ['Cache-Control' => 'public, max-age=86400']);
    }
    abort(404);
})->where('path', '.*');

