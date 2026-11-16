<?php

use App\Http\Controllers\Account;
use App\Http\Controllers\Admin;
use App\Http\Controllers\AdvertClickController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DevAccountSwitchController;
use App\Http\Controllers\EngagementController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PropertyController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
Route::get('/properties/{property}', [PropertyController::class, 'show'])->name('properties.show');
Route::post('/properties/{property}/engage', [EngagementController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('properties.engage');

Route::get('/ads/{advert}', AdvertClickController::class)->middleware('throttle:60,1')->name('adverts.click');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:6,1');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:6,1');
});

Route::post('/dev/switch-account/{user}', DevAccountSwitchController::class)->name('dev.switch-account');

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/', Account\DashboardController::class)->name('index');
    Route::get('/engagements', [Account\EngagementController::class, 'index'])->name('engagements.index');
    Route::get('/engagements/{engagement}', [Account\EngagementController::class, 'show'])->name('engagements.show');
    Route::get('/notifications', [Account\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [Account\NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/notifications/{notification}', [Account\NotificationController::class, 'show'])->name('notifications.show');
    Route::put('/appearance', [Account\AppearanceController::class, 'update'])->name('appearance.update');
    Route::get('/profile', [Account\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [Account\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [Account\ProfileController::class, 'updatePassword'])->name('profile.password');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    Route::resource('properties', Admin\PropertyController::class)->except('show');
    Route::scopeBindings()->group(function () {
        Route::patch('/properties/{property}/images/{image}/cover', [Admin\PropertyImageController::class, 'cover'])->name('properties.images.cover');
        Route::delete('/properties/{property}/images/{image}', [Admin\PropertyImageController::class, 'destroy'])->name('properties.images.destroy');
    });

    Route::resource('adverts', Admin\AdvertController::class)->except('show');
    Route::patch('/adverts/{advert}/toggle', [Admin\AdvertController::class, 'toggle'])->name('adverts.toggle');

    Route::redirect('/site', '/admin/site/landing')->name('site.index');
    Route::get('/site/{area}', [Admin\SiteSettingController::class, 'edit'])->name('site.edit');
    Route::put('/site/{area}', [Admin\SiteSettingController::class, 'update'])->name('site.update');
    Route::delete('/site/{area}', [Admin\SiteSettingController::class, 'reset'])->name('site.reset');

    Route::get('/engagements', [Admin\EngagementController::class, 'index'])->name('engagements.index');
    Route::get('/engagements/{engagement}', [Admin\EngagementController::class, 'show'])->name('engagements.show');
    Route::post('/engagements/{engagement}/updates', [Admin\EngagementUpdateController::class, 'store'])->name('engagements.updates.store');
});
