<?php

use App\Http\Controllers\Web\Backend\Settings\MailSettingController;
use App\Http\Controllers\Web\Backend\Settings\ProfileController;
use App\Http\Controllers\Web\Backend\Settings\SystemSettingController;
use App\Http\Controllers\Web\Backend\Settings\FirebaseSettingController;
use App\Http\Controllers\Web\Backend\Settings\UpdateAlertController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {

    //! Route for Profile Settings
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'index')->name('profile.setting');
        Route::patch('/update-profile', 'UpdateProfile')->name('update.profile');
        Route::put('/update-profile-password', 'UpdatePassword')->name('update.Password');
        Route::post('/update-profile-picture', 'UpdateProfilePicture')->name('update.profile.picture');
    });

    //! Route for System Settings
    Route::controller(SystemSettingController::class)->group(function () {
        Route::get('/system-setting', 'index')->name('system.index');
        Route::patch('/system-setting', 'update')->name('system.update');
    });

    //! Route for Mail Settings
    Route::controller(MailSettingController::class)->group(function () {
        Route::get('/mail-setting', 'index')->name('mail.setting');
        Route::patch('/mail-setting', 'update')->name('mail.update');
    });

    //! Route for Firebase Settings
    Route::controller(FirebaseSettingController::class)->group(function () {
        Route::get('/firebase-setting', 'index')->name('firebase.setting');
        Route::post('/firebase-setting', 'update')->name('firebase.setting.update');
        Route::delete('/firebase-setting', 'destroy')->name('firebase.setting.destroy');
    });

    //! Route for Update Alert Settings
    Route::controller(UpdateAlertController::class)->group(function () {
        Route::get('/update-alerts', 'index')->name('update-alerts.index');
        Route::patch('/update-alerts', 'update')->name('update-alerts.update');
    });
});
