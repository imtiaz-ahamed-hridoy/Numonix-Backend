<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Backend\{DashboardController, UserController};
use App\Http\Controllers\Web\Backend\Faq\FaqController;
use App\Http\Controllers\Web\Backend\Mail\MailController;
use App\Http\Controllers\Web\Backend\Review\ReviewController;
// Stripe removed - Paddle is the active payment provider
use App\Http\Controllers\Web\Backend\EmailLog\EmailLogController;
use App\Http\Controllers\Web\Backend\Notification\CustomNotificationController;
use App\Http\Controllers\Web\Backend\Quiz\{QuestionController, QuizController};
use App\Http\Controllers\Web\Backend\EarTraining\{EarTrainingStageController, EarTrainingLevelController, EarTrainingExplanationTemplateController};
use App\Http\Controllers\Web\Backend\LearningController;
use App\Http\Controllers\Web\Backend\Cms\UserGuideController;
use App\Http\Controllers\Web\Backend\Cms\TermsConditionsController;
use App\Http\Controllers\Web\Backend\Cms\PrivacyPolicyController;
use App\Http\Controllers\Web\Backend\Cms\GlossaryController;
use App\Http\Controllers\Web\Backend\Cms\CompleteGlossaryController;
use App\Http\Controllers\Web\Backend\Cms\AboutNumoniXController;
use App\Http\Controllers\Web\Backend\GuestAccess\{GuestAccessController, GuestAnalyticsController};
use App\Http\Controllers\Web\Backend\HomeModuleController; // Learning

// Route for Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Route for Users Page
Route::get('/user', [UserController::class, 'index'])->name('user.index');
Route::get('/user/create', [UserController::class, 'create'])->name('user.create');
Route::get('/user/{id}', [UserController::class, 'show'])->name('user.show');
Route::post('/user/store', [UserController::class, 'store'])->name('user.store');
Route::get('/user/edit/{id}', [UserController::class, 'edit'])->name('user.edit');
Route::post('/user/update/{id}', [UserController::class, 'update'])->name('user.update');
Route::delete('/user/destroy/{id}', [UserController::class, 'destroy'])->name('user.destroy');



// Custom Notifications
Route::get('/custom-notifications', [CustomNotificationController::class, 'index'])->name('custom-notifications.index');
Route::post('/custom-notifications/send', [CustomNotificationController::class, 'send'])->name('custom-notifications.send');

//backend mail send
Route::get('/mail/tempalte', [MailController::class, 'index'])->name('mail.index');
Route::get('/mail/tempalte/create', [MailController::class, 'create'])->name('mail.create');
Route::post('/mail/tempalte/create', [MailController::class, 'store'])->name('mail.store');
Route::get('/mail/tempalte/edit/{id}', [MailController::class, 'edit'])->name('mail.edit');
Route::post('/mail/tempalte/update/{id}', [MailController::class, 'update'])->name('mailTemp.update');
Route::delete('/mail/tempalte/delete/{id}', [MailController::class, 'destroy'])->name('mail.destroy');

// send email Log
Route::get('/mail', [EmailLogController::class, 'index'])->name('email.index');
Route::get('/mail/send', [EmailLogController::class, 'create'])->name('email.create');
Route::post('/mail/send', [EmailLogController::class, 'sendBulkMail'])->name('send.bulk.mail');
Route::delete('/mail/delete/{id}', [EmailLogController::class, 'destroy'])->name('email.destroy');


//faq
Route::get('/faq', [FaqController::class, 'index'])->name('faq.index');
Route::get('/faq/create', [FaqController::class, 'create'])->name('faq.create');
Route::post('/faq/create', [FaqController::class, 'store'])->name('faq.store');
Route::get('/faq/{id}', [FaqController::class, 'show'])->name('faq.show');
Route::get('/faq/edit/{id}', [FaqController::class, 'edit'])->name('faq.edit');
Route::post('/faq/update/{id}', [FaqController::class, 'update'])->name('faq.update');
Route::delete('/faq/delete/{id}', [FaqController::class, 'destroy'])->name('faq.destroy');


//review
Route::get('/review', [ReviewController::class, 'index'])->name('review.index');
Route::get('/review/create', [ReviewController::class, 'create'])->name('review.create');
Route::post('/review/store', [ReviewController::class, 'store'])->name('review.store');
Route::get('/review/edit/{id}', [ReviewController::class, 'edit'])->name('review.edit');
Route::post('/review/update/{id}', [ReviewController::class, 'update'])->name('review.update');
Route::delete('/review/delete/{id}', [ReviewController::class, 'destroy'])->name('review.destroy');

// Stripe routes removed - payment processing is now handled via Paddle



// Learning
Route::get('/learning', [LearningController::class, 'index'])->name('learning.index');
Route::get('/learning/create', [LearningController::class, 'create'])->name('learning.create');
Route::post('/learning/store', [LearningController::class, 'store'])->name('learning.store');
Route::get('/learning/{id}', [LearningController::class, 'show'])->name('learning.show');
Route::get('/learning/edit/{id}', [LearningController::class, 'edit'])->name('learning.edit');
Route::post('/learning/update/{id}', [LearningController::class, 'update'])->name('learning.update');
Route::delete('/learning/delete/{id}', [LearningController::class, 'destroy'])->name('learning.destroy');

// Learning Groups
Route::get('/learning-groups', [\App\Http\Controllers\Web\Backend\LearningGroupController::class, 'index'])->name('learning-group.index');
Route::get('/learning-groups/create', [\App\Http\Controllers\Web\Backend\LearningGroupController::class, 'create'])->name('learning-group.create');
Route::post('/learning-groups/store', [\App\Http\Controllers\Web\Backend\LearningGroupController::class, 'store'])->name('learning-group.store');
Route::get('/learning-groups/edit/{id}', [\App\Http\Controllers\Web\Backend\LearningGroupController::class, 'edit'])->name('learning-group.edit');
Route::put('/learning-groups/update/{id}', [\App\Http\Controllers\Web\Backend\LearningGroupController::class, 'update'])->name('learning-group.update');
Route::delete('/learning-groups/delete/{id}', [\App\Http\Controllers\Web\Backend\LearningGroupController::class, 'destroy'])->name('learning-group.destroy');

// Home Modules
Route::get('/home-module', [HomeModuleController::class, 'index'])->name('home-module.index');
Route::get('/home-module/create', [HomeModuleController::class, 'create'])->name('home-module.create');
Route::post('/home-module/store', [HomeModuleController::class, 'store'])->name('home-module.store');
Route::get('/home-module/edit/{id}', [HomeModuleController::class, 'edit'])->name('home-module.edit');
Route::post('/home-module/update/{id}', [HomeModuleController::class, 'update'])->name('home-module.update');
Route::delete('/home-module/delete/{id}', [HomeModuleController::class, 'destroy'])->name('home-module.destroy');

//Quiz
Route::get('/quiz', [QuizController::class, 'index'])->name('quiz.index');
Route::get('/quiz/create', [QuizController::class, 'create'])->name('quiz.create');
Route::post('/quiz/store', [QuizController::class, 'store'])->name('quiz.store');
Route::get('/quiz/edit/{id}', [QuizController::class, 'edit'])->name('quiz.edit');
Route::post('/quiz/update/{id}', [QuizController::class, 'update'])->name('quiz.update');
Route::delete('/quiz/delete/{id}', [QuizController::class, 'destroy'])->name('quiz.destroy');

//Question
Route::get('/quiz/{quizId}/questions', [QuestionController::class, 'index'])->name('quiz.questions.index');
Route::get('/quiz/{quizId}/questions/create', [QuestionController::class, 'create'])->name('quiz.questions.create');
Route::post('/quiz/{quizId}/questions/store', [QuestionController::class, 'store'])->name('quiz.questions.store');
Route::get('/quiz/{quizId}/questions/edit/{id}', [QuestionController::class, 'edit'])->name('quiz.questions.edit');
Route::post('/quiz/{quizId}/questions/update/{id}', [QuestionController::class, 'update'])->name('quiz.questions.update');
Route::delete('/quiz/{quizId}/questions/delete/{id}', [QuestionController::class, 'destroy'])->name('quiz.questions.destroy');

// Ear Training Stage
Route::get('/ear-training/stages', [EarTrainingStageController::class, 'index'])->name('admin.ear-training.stages.index');
Route::get('/ear-training/stages/create', [EarTrainingStageController::class, 'create'])->name('admin.ear-training.stages.create');
Route::post('/ear-training/stages/store', [EarTrainingStageController::class, 'store'])->name('admin.ear-training.stages.store');
Route::get('/ear-training/stages/edit/{stage}', [EarTrainingStageController::class, 'edit'])->name('admin.ear-training.stages.edit');
Route::post('/ear-training/stages/update/{stage}', [EarTrainingStageController::class, 'update'])->name('admin.ear-training.stages.update');
Route::delete('/ear-training/stages/delete/{stage}', [EarTrainingStageController::class, 'destroy'])->name('admin.ear-training.stages.destroy');

// Ear Training Level
Route::get('/ear-training/stages/{stage}/levels', [EarTrainingLevelController::class, 'index'])->name('admin.ear-training.levels.index');
Route::get('/ear-training/stages/{stage}/levels/create', [EarTrainingLevelController::class, 'create'])->name('admin.ear-training.levels.create');
Route::post('/ear-training/stages/{stage}/levels/store', [EarTrainingLevelController::class, 'store'])->name('admin.ear-training.levels.store');
Route::get('/ear-training/levels/edit/{level}', [EarTrainingLevelController::class, 'edit'])->name('admin.ear-training.levels.edit');
Route::post('/ear-training/levels/update/{level}', [EarTrainingLevelController::class, 'update'])->name('admin.ear-training.levels.update');
Route::delete('/ear-training/levels/delete/{level}', [EarTrainingLevelController::class, 'destroy'])->name('admin.ear-training.levels.destroy');

// Ear Training Explanation Templates
Route::get('/ear-training/templates', [EarTrainingExplanationTemplateController::class, 'index'])->name('admin.ear-training.templates.index');
Route::get('/ear-training/templates/create', [EarTrainingExplanationTemplateController::class, 'create'])->name('admin.ear-training.templates.create');
Route::post('/ear-training/templates/store', [EarTrainingExplanationTemplateController::class, 'store'])->name('admin.ear-training.templates.store');
Route::get('/ear-training/templates/edit/{template}', [EarTrainingExplanationTemplateController::class, 'edit'])->name('admin.ear-training.templates.edit');
Route::post('/ear-training/templates/update/{template}', [EarTrainingExplanationTemplateController::class, 'update'])->name('admin.ear-training.templates.update');
Route::delete('/ear-training/templates/delete/{template}', [EarTrainingExplanationTemplateController::class, 'destroy'])->name('admin.ear-training.templates.destroy');


// CMS Pages
Route::prefix('cms')->name('cms.')->group(function () {
    Route::get('/user-guide', [UserGuideController::class, 'index'])->name('user-guide.index');
    Route::post('/user-guide', [UserGuideController::class, 'update'])->name('user-guide.update');

    Route::get('/terms-conditions', [TermsConditionsController::class, 'index'])->name('terms-conditions.index');
    Route::post('/terms-conditions', [TermsConditionsController::class, 'update'])->name('terms-conditions.update');

    Route::get('/privacy-policy', [PrivacyPolicyController::class, 'index'])->name('privacy-policy.index');
    Route::post('/privacy-policy', [PrivacyPolicyController::class, 'update'])->name('privacy-policy.update');

    Route::get('/glossary', [GlossaryController::class, 'index'])->name('glossary.index');
    Route::post('/glossary', [GlossaryController::class, 'update'])->name('glossary.update');

    Route::get('/complete-glossary', [CompleteGlossaryController::class, 'index'])->name('complete-glossary.index');
    Route::post('/complete-glossary', [CompleteGlossaryController::class, 'update'])->name('complete-glossary.update');

    Route::get('/about-numonix', [AboutNumoniXController::class, 'index'])->name('about-numonix.index');
    Route::post('/about-numonix', [AboutNumoniXController::class, 'update'])->name('about-numonix.update');
});

// Activity Logs
Route::get('/activity-logs', [\App\Http\Controllers\Web\Backend\ActivityLog\ActivityLogController::class, 'index'])->name('activity.index');
Route::delete('/activity-logs/delete/{id}', [\App\Http\Controllers\Web\Backend\ActivityLog\ActivityLogController::class, 'destroy'])->name('activity.destroy');

// Guest Access Management
Route::get('/guest-access', [GuestAccessController::class, 'index'])->name('guest-access.index');
Route::post('/guest-access/update', [GuestAccessController::class, 'update'])->name('guest-access.update');
Route::post('/guest-access/toggle-item', [GuestAccessController::class, 'toggleItem'])->name('guest-access.toggle-item');
Route::get('/guest-access/preview', [GuestAccessController::class, 'contentPreview'])->name('guest-access.preview');

// Guest & User Analytics
Route::get('/guest-analytics', [GuestAnalyticsController::class, 'index'])->name('guest-analytics.index');
Route::get('/guest-analytics/chart-data', [GuestAnalyticsController::class, 'chartData'])->name('guest-analytics.chart-data');

// --- Founder Programme Dashboard (Spec 3.16) ------------------------------
// Admin view: founder count, trial status, review form completion rate, conversion rate.
Route::get('/founders', [\App\Http\Controllers\Web\Backend\Founder\FounderDashboardController::class, 'index'])->name('founders.index');
Route::get('/founders/export', [\App\Http\Controllers\Web\Backend\Founder\FounderDashboardController::class, 'export'])->name('founders.export');

