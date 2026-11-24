<?php

use App\Http\Controllers\Account;
use App\Http\Controllers\Admin;
use App\Http\Controllers\AdvertClickController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DevAccountSwitchController;
use App\Http\Controllers\EngagementController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\LandBankingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\TourController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
Route::get('/properties/{property}', [PropertyController::class, 'show'])->name('properties.show');
Route::post('/properties/{property}/engage', [EngagementController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('properties.engage');

Route::get('/investments', [InvestmentController::class, 'index'])->name('investments.index');
Route::get('/investments/{opportunity}', [InvestmentController::class, 'show'])->name('investments.show');
Route::get('/land-banking', [LandBankingController::class, 'index'])->name('land-banking.index');
Route::get('/land-banking/{opportunity}', [LandBankingController::class, 'show'])->name('land-banking.show');

Route::get('/insights', [ArticleController::class, 'index'])->name('insights.index');
Route::get('/insights/{article}', [ArticleController::class, 'show'])->name('insights.show');

Route::get('/services', [PageController::class, 'services'])->name('services');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/enquiries', [EnquiryController::class, 'store'])->middleware('throttle:10,1')->name('enquiries.store');

Route::get('/ads/{advert}', AdvertClickController::class)->middleware('throttle:60,1')->name('adverts.click');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:6,1');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:6,1');
});

Route::post('/dev/switch-account/{user}', DevAccountSwitchController::class)->name('dev.switch-account');

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::post('/tour/{area}', TourController::class)->middleware(['auth', 'throttle:30,1'])->name('tour.complete');

Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/', Account\DashboardController::class)->name('index');
    Route::get('/engagements', [Account\EngagementController::class, 'index'])->name('engagements.index');
    Route::get('/engagements/{engagement}', [Account\EngagementController::class, 'show'])->name('engagements.show');
    Route::get('/enquiries', [Account\EnquiryController::class, 'index'])->name('enquiries.index');
    Route::get('/enquiries/{enquiry}', [Account\EnquiryController::class, 'show'])->name('enquiries.show');
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

    Route::resource('opportunities', Admin\InvestmentOpportunityController::class)->except('show');
    Route::resource('articles', Admin\ArticleController::class)->except('show');

    Route::resource('enquiry-types', Admin\EnquiryTypeController::class)->except(['show', 'create']);
    Route::resource('whatsapp-numbers', Admin\WhatsappNumberController::class)->except(['show', 'create']);

    Route::get('/enquiries/export', [Admin\EnquiryController::class, 'export'])->name('enquiries.export');
    Route::resource('enquiries', Admin\EnquiryController::class)->only(['index', 'show', 'update', 'destroy']);

    Route::resource('adverts', Admin\AdvertController::class)->except('show');
    Route::patch('/adverts/{advert}/toggle', [Admin\AdvertController::class, 'toggle'])->name('adverts.toggle');

    Route::get('/pages', [Admin\PageContentController::class, 'index'])->name('pages.index');
    Route::get('/pages/{page}', [Admin\PageContentController::class, 'edit'])->name('pages.edit');
    Route::put('/pages/{page}', [Admin\PageContentController::class, 'update'])->name('pages.update');
    Route::delete('/pages/{page}/sections/{section}', [Admin\PageContentController::class, 'reset'])->name('pages.reset');

    Route::get('/data', [Admin\DataManagerController::class, 'index'])->name('data.index');
    Route::get('/data/seed', [Admin\DataManagerController::class, 'seedForm'])->name('data.seed');
    Route::post('/data/seed', [Admin\DataManagerController::class, 'seed'])->middleware('throttle:10,1')->name('data.seed.store');
    Route::get('/data/delete', [Admin\DataManagerController::class, 'deleteForm'])->name('data.delete');
    Route::delete('/data', [Admin\DataManagerController::class, 'destroy'])->middleware('throttle:10,1')->name('data.destroy');

    Route::redirect('/site', '/admin/site/landing')->name('site.index');
    Route::get('/site/{area}', [Admin\SiteSettingController::class, 'edit'])->name('site.edit');
    Route::put('/site/{area}', [Admin\SiteSettingController::class, 'update'])->name('site.update');
    Route::delete('/site/{area}', [Admin\SiteSettingController::class, 'reset'])->name('site.reset');

    Route::get('/engagements', [Admin\EngagementController::class, 'index'])->name('engagements.index');
    Route::get('/engagements/{engagement}', [Admin\EngagementController::class, 'show'])->name('engagements.show');
    Route::post('/engagements/{engagement}/updates', [Admin\EngagementUpdateController::class, 'store'])->name('engagements.updates.store');
});
