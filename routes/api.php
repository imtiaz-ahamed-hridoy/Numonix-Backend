<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Auth\{LoginController, ProfileUpdateController, RegisterController};
use App\Http\Controllers\API\Cookie\CookieSettingsController;
use App\Http\Controllers\API\Faq\FaqController;
use App\Http\Controllers\API\Notification\NotificationSettingsController;
use App\Http\Controllers\API\Notification\NotificationController;
use App\Http\Controllers\API\Quiz\QuizApiController;
use App\Http\Controllers\API\Review\ReviewController;
use App\Http\Controllers\API\Support\SupportController;
use App\Http\Controllers\API\SystemSetting\SystemSettingController;
use App\Http\Controllers\API\SystemSetting\UpdateAlertApiController;

use App\Http\Controllers\API\LearningController;
use App\Http\Controllers\API\SocialLogin\SocialLoginController;
use App\Http\Controllers\API\PushNotification\FirebaseTokenController;
use App\Http\Controllers\API\UserProgress\UserProgressController;
use App\Http\Controllers\API\EarTraining\EarTrainingApiController;
use App\Http\Controllers\API\Cms\UserGuideApiController;
use App\Http\Controllers\API\Cms\TermsConditionsApiController;
use App\Http\Controllers\API\Cms\PrivacyPolicyApiController;
use App\Http\Controllers\API\Cms\GlossaryApiController;
use App\Http\Controllers\API\Cms\CompleteGlossaryApiController;
use App\Http\Controllers\API\Cms\AboutNumoniXApiController;

use App\Http\Controllers\API\Subscription\SubscriptionController;
use App\Http\Controllers\API\Preferences\PreferencesController;
use App\Http\Controllers\API\Guest\GuestDeviceController;
use App\Http\Controllers\API\HomeController;
use App\Http\Controllers\API\DashboardView\DashboardOverviewController;

/* ==========================================================================
 * GUEST DEVICE ROUTES - For guest device identification (no auth required)
 * ========================================================================== */

Route::post('/guest/identify', [GuestDeviceController::class, 'identify'])
    ->middleware('throttle:5,1');
Route::get('/guest/device', [GuestDeviceController::class, 'device'])
    ->middleware('validate.guest.headers');

/* ==========================================================================
 * PUBLIC ROUTES - No authentication required
 * These routes work for both guests (no token) and authenticated users.
 * Controllers use auth('sanctum')->user() to optionally resolve the user.
 * ========================================================================== */

// System Setting
Route::get('/system-setting', [SystemSettingController::class, 'systemSetting']);

// App Update Alerts - public endpoints consumed by the mobile app
// Returns { "status": 200, "data": true|false, "message": "..." }
Route::get('/app-update/soft',  [UpdateAlertApiController::class, 'softUpdate']);
Route::get('/app-update/force', [UpdateAlertApiController::class, 'forceUpdate']);

// Public FAQ endpoint
Route::get('/faq', [FaqController::class, 'index']);

// Public reviews endpoint
Route::get('/reviews', [ReviewController::class, 'index']);

// Social Login
Route::post('/social/login', [SocialLoginController::class, 'socialLogin'])
    ->middleware('throttle:10,1');

Route::post('/social/apple/callback', [SocialLoginController::class, 'redirectCallbackApple'])
    ->middleware('throttle:10,1');

// Founder single-use activation
Route::get('/founder/activate/{token}', [\App\Http\Controllers\API\Founder\FounderController::class, 'activate'])->name('founder.activate');

// Support routes
Route::post('/support/contact', [SupportController::class, 'contact']);
Route::post('/support/bug-report', [SupportController::class, 'reportBug']);
Route::post('/support/feature-suggestion', [SupportController::class, 'suggestFeature']);

// CMS Public API Routes
Route::prefix('cms')->group(function () {
    Route::get('/user-guide', [UserGuideApiController::class, 'index']);
    Route::get('/terms-conditions', [TermsConditionsApiController::class, 'index']);
    Route::get('/privacy-policy', [PrivacyPolicyApiController::class, 'index']);
    Route::get('/glossary', [GlossaryApiController::class, 'index']);
    Route::get('/complete-glossary', [CompleteGlossaryApiController::class, 'index']);
    Route::get('/about-numonix', [AboutNumoniXApiController::class, 'index']);
});



/* --------------------------------------------------------------------------
 * GUEST-AWARE ROUTES - Require guest headers (or authentication)
 * --------------------------------------------------------------------------
 * Unauthenticated requests MUST provide all three guest headers:
 *   X-Guest-Device-Id, X-Guest-Token, X-Guest-Fingerprint
 *
 * The validate.guest.headers middleware wraps all guest-accessible routes.
 * For authenticated users, auth:sanctum takes priority.
 * -------------------------------------------------------------------------- */
Route::middleware(['track.guest.activity', 'validate.guest.headers'])->group(function () {

    // Home - Aggregated home page with modules
    Route::get('/home', [HomeController::class, 'index']);

    // Learning - Guest-accessible listing & detail routes
    Route::get('/learnings', [LearningController::class, 'index']);
    Route::get('/learnings/lessons', [LearningController::class, 'lessons']);
    Route::get('/learnings/core-theory', [LearningController::class, 'coreTheory']);
    Route::get('/learnings/advanced-insights', [LearningController::class, 'advancedInsights']);
    Route::get('/learnings/case-studies', [LearningController::class, 'caseStudies']);
    Route::get('/learnings/fnh-architecture', [LearningController::class, 'fnhArchitecture']);
    Route::get('/learnings/show/{id}', [LearningController::class, 'show']);
    Route::get('/learnings/summary/{id}', [LearningController::class, 'summary']);

    // Learning Progress - Optional auth: authenticated users get DB persistence,
    // guests receive the same response payload but nothing is saved.
    Route::post('/learnings/completed', [LearningController::class, 'complete']);

    // Checklist - supports both guests (via X-Guest-Device-Id + X-Guest-Token headers)
    // and authenticated users.
    Route::post('/learnings/checklist/update', [LearningController::class, 'updateChecklist']);

    // Ear Training - Guest-accessible listing, stateless quiz, and progress routes.
    // tonic-root-hint and level-complete all use optional auth: guests are
    // identified by the server-derived guest_fingerprint computed from
    // device_id + token using APP_KEY.
    Route::get('/ear-training/stages', [EarTrainingApiController::class, 'stages']);
    Route::get('/ear-training/stages/{stageId}/levels', [EarTrainingApiController::class, 'levels']);
    Route::post('/ear-training/submit/{levelId}', [EarTrainingApiController::class, 'submit']);
    Route::get('/ear-training/explanation-templates', [EarTrainingApiController::class, 'explanationTemplates']);
    // Backward-compatibility aliases for legacy mobile endpoints
    Route::get('/ear-training/levels/quizzes/{id}', [EarTrainingApiController::class, 'levels']);
    Route::match(['get', 'post'], '/functional-ear-training/submit', [EarTrainingApiController::class, 'submit']);

    // Quiz - Guest-accessible listing, stateless answer check, full submit,
    // review, attempt details, and retry. All use optional auth: guests are
    // identified by guest_fingerprint, authenticated users by user_id.
    Route::get('/quiz/by-learning/{learning_id}', [QuizApiController::class, 'byLearning']);
    Route::get('/quiz/by-lesson/{learning_id}', [QuizApiController::class, 'byLearning']);
    Route::post('/quiz/check-answer', [QuizApiController::class, 'checkAnswer']);
    Route::post('/quiz/submit/{learning_id}', [QuizApiController::class, 'submit']);
    Route::get('/quiz/review/{attempt_id}', [QuizApiController::class, 'review']);
    Route::get('/quiz/review-by-learning/{learning_id}', [QuizApiController::class, 'reviewByLearning']);
    Route::get('/quiz/attempt-details/{id}', [QuizApiController::class, 'attemptDetails']);
    Route::post('/quiz/retry/{learning_id}', [QuizApiController::class, 'retry']);

    // --- User Progress Dashboard -----------------------------------------
    Route::get('/user/progress', UserProgressController::class);
});



/* --------------------------------------------------------------------------
 * COOKIE SETTINGS - Guest or authenticated
 * --------------------------------------------------------------------------
 * Unauthenticated requests MUST provide all three guest headers.
 * -------------------------------------------------------------------------- */
Route::middleware('validate.guest.headers')->group(function () {
    Route::get('/cookie-settings-show', [CookieSettingsController::class, 'show']);
    Route::post('/cookie-settings-update', [CookieSettingsController::class, 'update']);
});


/* ==========================================================================
 * GUEST AUTH ROUTES - Only for unauthenticated users
 * ========================================================================== */
Route::group(['middleware' => 'guest:sanctum'], function () {
    Route::post('login', [LoginController::class, 'login'])->middleware('throttle:6,1');
    Route::post('register', [RegisterController::class, 'register'])->middleware('throttle:5,1');
    Route::post('email/verify/resend', [RegisterController::class, 'resendEmailVerifyOTP'])->middleware('throttle:2,1');
    Route::post('email/verify', [RegisterController::class, 'verifyEmail']);
    Route::post('password/forgot', [RegisterController::class, 'forgotPassword'])->middleware('throttle:3,1');
    Route::post('password/forgot/verify', [RegisterController::class, 'verifyForgotPasswordOTP']);
    Route::post('password/reset', [RegisterController::class, 'resetPassword'])->middleware('throttle:5,1');
});

/* ==========================================================================
 * AUTHENTICATED ROUTES - Require valid Sanctum token
 * ========================================================================== */
Route::group(['middleware' => 'auth:sanctum'], function () {

    // --- Dashboard -------------------------------------------------------
    Route::get('/dashboard', [DashboardOverviewController::class, 'index']);

    // --- Profile ---------------------------------------------------------
    Route::get('/profile', [ProfileUpdateController::class, 'profile']);
    Route::post('/profile/download-my-data', [ProfileUpdateController::class, 'downloadMyData']);
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::post('/logout/all', [LoginController::class, 'logoutAll']);

    Route::get('/profile/manage-account', [ProfileUpdateController::class, 'manageAccount']);
    Route::post('/profile/details', [ProfileUpdateController::class, 'updateDetails']);
    Route::post('/profile/change-password', [ProfileUpdateController::class, 'changePassword']);
    Route::post('/auth/change-password', [LoginController::class, 'changePassword']);
    Route::post('/profile/delete-account', [ProfileUpdateController::class, 'accountDelete']);

    // --- Notifications ---------------------------------------------------
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications/send', [NotificationController::class, 'sendCustom']);
    Route::get('/notification-settings-show', [NotificationSettingsController::class, 'show']);
    Route::post('/notification-settings-update', [NotificationSettingsController::class, 'update']);

    // --- Cookie settings (moved to optional-auth section above) ----------

    // --- Learning Progress (auth-only: saves to DB) ----------------------
    // /learnings/completed is now in the optional-auth group (supports guests)
    Route::post('/learnings/next', [LearningController::class, 'nextLearning']);
    Route::post('/learnings/previous', [LearningController::class, 'previousLearning']);

    // --- Quiz (auth-only: questions listing is protected, retry is now in optional-auth) -
    Route::get('/quiz/questions', [QuizApiController::class, 'questions']);

    // --- Reviews (CRUD) --------------------------------------------------
    Route::post('/reviews-store', [ReviewController::class, 'store']);
    Route::post('/reviews-update/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews-delete/{id}', [ReviewController::class, 'destroy']);

    // --- Subscription ----------------------------------------------------
    Route::get('/subscription/status', [SubscriptionController::class, 'status']);
    Route::match(['get', 'post'], '/subscription/checkout', [SubscriptionController::class, 'checkout']);

    // --- Founder Programme -----------------------------------------------
    Route::get('/founder/status', [\App\Http\Controllers\API\Founder\FounderController::class, 'status']);
    Route::post('/founder/review', [\App\Http\Controllers\API\Founder\FounderController::class, 'submitReview']);


    // --- Preferences -----------------------------------------------------
    Route::get('/preferences', [PreferencesController::class, 'index']);
    Route::post('/preferences', [PreferencesController::class, 'store']);

    // --- Firebase Push Notifications -------------------------------------
    Route::controller(FirebaseTokenController::class)->group(function () {
        Route::post('/firebase-token/add', 'updateFirebaseToken');
        Route::post('/firebase-token/delete', 'deleteFirebaseToken');
    });
});
