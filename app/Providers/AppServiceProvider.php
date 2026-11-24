<?php

namespace App\Providers;

use App\Enums\AdvertPage;
use App\Enums\EngagementStatus;
use App\Enums\EnquiryStatus;
use App\Enums\SiteArea;
use App\Models\Advert;
use App\Models\Engagement;
use App\Models\Enquiry;
use App\Models\InvestmentOpportunity;
use App\Models\Property;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            'property' => Property::class,
            'investment_opportunity' => InvestmentOpportunity::class,
        ]);

        View::composer(['components.layouts.app', 'components.layouts.admin', 'components.layouts.client'], function (ViewInstance $view): void {
            $user = auth()->user();

            $view->with([
                'unreadNotificationsCount' => $user ? $user->unreadNotifications()->count() : 0,
                'recentNotifications' => $user ? $user->notifications()->take(6)->get() : collect(),
            ]);
        });

        foreach (['app' => SiteArea::Landing, 'auth' => SiteArea::Landing, 'client' => SiteArea::Client, 'admin' => SiteArea::Admin] as $layout => $area) {
            View::composer("components.layouts.{$layout}", function (ViewInstance $view) use ($area): void {
                $theme = SiteSetting::for($area);

                $view->with(['siteTheme' => $theme, 'colorMode' => $theme->modeFor(auth()->user())]);
            });
        }

        View::composer('components.layouts.app', function (ViewInstance $view): void {
            $page = match (true) {
                request()->routeIs('home') => AdvertPage::Home,
                request()->routeIs('properties.index') => AdvertPage::Listings,
                request()->routeIs('properties.show') => AdvertPage::Property,
                default => null,
            };

            $view->with('popupAdvert', Advert::popupFor($page));
        });

        View::composer('components.dev-account-switcher', function (ViewInstance $view): void {
            $view->with([
                'testAdmins' => User::where('is_admin', true)->orderBy('id')->take(4)->get(['id', 'name', 'email', 'is_admin']),
                'testClients' => User::where('is_admin', false)->orderBy('id')->take(4)->get(['id', 'name', 'email', 'is_admin']),
            ]);
        });

        View::composer('components.layouts.client', function (ViewInstance $view): void {
            $view->with('openEngagementsCount', auth()->user()?->engagements()->open()->count() ?? 0);
        });

        View::composer('components.layouts.admin', function (ViewInstance $view): void {
            $view->with([
                'newEngagementsCount' => Engagement::where('status', EngagementStatus::New)->count(),
                'newEnquiriesCount' => Enquiry::where('status', EnquiryStatus::New)->count(),
            ]);
        });
    }
}
