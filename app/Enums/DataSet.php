<?php

namespace App\Enums;

use App\Models\Advert;
use App\Models\Article;
use App\Models\Engagement;
use App\Models\Enquiry;
use App\Models\InvestmentOpportunity;
use App\Models\PageSection;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\SiteSetting;
use App\Models\User;
use Database\Seeders\AdvertSeeder;
use Database\Seeders\ArticleSeeder;
use Database\Seeders\DemoClientSeeder;
use Database\Seeders\DemoEngagementSeeder;
use Database\Seeders\DemoEnquirySeeder;
use Database\Seeders\InvestmentOpportunitySeeder;
use Database\Seeders\PropertySeeder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

/**
 * The groups of data the admin "Data manager" can seed with demo content or delete.
 * The signed-in admin is never deleted, and seeding never creates admin accounts.
 */
enum DataSet: string
{
    case Clients = 'clients';
    case Properties = 'properties';
    case Investments = 'investments';
    case LandBanking = 'land_banking';
    case Articles = 'articles';
    case Adverts = 'adverts';
    case Engagements = 'engagements';
    case Enquiries = 'enquiries';
    case Notifications = 'notifications';
    case PageContent = 'page_content';
    case Appearance = 'appearance';
    case Admins = 'admins';

    public function label(): string
    {
        return match ($this) {
            self::Clients => 'Client accounts',
            self::Properties => 'Property listings',
            self::Investments => 'Investment opportunities',
            self::LandBanking => 'Land banking',
            self::Articles => 'Insights articles',
            self::Adverts => 'Adverts',
            self::Engagements => 'Engagements',
            self::Enquiries => 'Enquiries',
            self::Notifications => 'Notifications',
            self::PageContent => 'Page content edits',
            self::Appearance => 'Site manager settings',
            self::Admins => 'Other admin accounts',
        };
    }

    /**
     * Singular noun used in result messages ("9 property listings").
     */
    public function noun(): string
    {
        return match ($this) {
            self::Clients => 'client account',
            self::Properties => 'property listing',
            self::Investments => 'investment opportunity',
            self::LandBanking => 'land opportunity',
            self::Articles => 'article',
            self::Adverts => 'advert',
            self::Engagements => 'engagement',
            self::Enquiries => 'enquiry',
            self::Notifications => 'notification',
            self::PageContent => 'edited page section',
            self::Appearance => 'appearance setting',
            self::Admins => 'other admin account',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Clients => 'users',
            self::Properties => 'building',
            self::Investments => 'briefcase',
            self::LandBanking => 'leaf',
            self::Articles => 'book',
            self::Adverts => 'megaphone',
            self::Engagements => 'inbox',
            self::Enquiries => 'message',
            self::Notifications => 'bell',
            self::PageContent => 'file',
            self::Appearance => 'sliders',
            self::Admins => 'shield',
        };
    }

    public function group(): string
    {
        return match ($this) {
            self::Properties, self::Investments, self::LandBanking, self::Articles, self::Adverts => 'Listings & content',
            self::Clients, self::Engagements, self::Enquiries, self::Notifications, self::Admins => 'People & activity',
            self::PageContent, self::Appearance => 'Website settings',
        };
    }

    /**
     * What seeding adds, or null when the dataset has no demo content.
     */
    public function seedDescription(): ?string
    {
        return match ($this) {
            self::Clients => '3 demo client accounts (including test@example.com). Never creates admins.',
            self::Properties => '9 listings for sale, rent, shortlet and lease, with photos.',
            self::Investments => 'Rental income, joint venture and other investment opportunities.',
            self::LandBanking => 'Residential, commercial and agricultural land opportunities.',
            self::Articles => '6 guides and market updates for the Insights page.',
            self::Adverts => 'Demo banners, cards and a pop-up for fictional brands.',
            self::Engagements => 'Engagements from guests and the demo client, with progress updates.',
            self::Enquiries => 'Guest enquiries plus the demo client’s land banking and investment enquiries with adviser updates.',
            default => null,
        };
    }

    public function deleteDescription(): string
    {
        return match ($this) {
            self::Clients => 'All non-admin accounts and their notifications. Their engagements and enquiries are kept as guest records.',
            self::Properties => 'Every listing, its photos and all engagements on it.',
            self::Investments => 'Investment opportunities (not land) and their uploaded images. Enquiries about them are kept.',
            self::LandBanking => 'Land banking opportunities and their uploaded images. Enquiries about them are kept.',
            self::Articles => 'Every Insights article and its uploaded image.',
            self::Adverts => 'Every advert, pop-up and uploaded advert image.',
            self::Engagements => 'Every engagement and its progress timeline.',
            self::Enquiries => 'Every enquiry and its update timeline.',
            self::Notifications => 'In-app notifications for every account.',
            self::PageContent => 'Your edits in Pages — every page goes back to its original wording and images.',
            self::Appearance => 'Colours, fonts, glass and tour settings — back to the default look.',
            self::Admins => 'Every admin account except the one you are signed in with.',
        };
    }

    public function isSeedable(): bool
    {
        return $this->seedDescription() !== null;
    }

    /**
     * Datasets that must exist before this one can be seeded.
     *
     * @return list<self>
     */
    public function requires(): array
    {
        return match ($this) {
            self::Engagements => [self::Clients, self::Properties],
            self::Enquiries => [self::Clients, self::Investments, self::LandBanking],
            default => [],
        };
    }

    public function count(User $admin): int
    {
        return match ($this) {
            self::Clients => User::where('is_admin', false)->count(),
            self::Properties => Property::count(),
            self::Investments => InvestmentOpportunity::whereNot(fn (Builder $query) => $query->land())->count(),
            self::LandBanking => InvestmentOpportunity::query()->land()->count(),
            self::Articles => Article::count(),
            self::Adverts => Advert::count(),
            self::Engagements => Engagement::count(),
            self::Enquiries => Enquiry::count(),
            self::Notifications => DatabaseNotification::count(),
            self::PageContent => PageSection::count(),
            self::Appearance => SiteSetting::count(),
            self::Admins => User::where('is_admin', true)->whereKeyNot($admin->getKey())->count(),
        };
    }

    public function seed(): void
    {
        match ($this) {
            self::Clients => app(DemoClientSeeder::class)->run(),
            self::Properties => app(PropertySeeder::class)->run(),
            self::Investments => app(InvestmentOpportunitySeeder::class)->run(land: false),
            self::LandBanking => app(InvestmentOpportunitySeeder::class)->run(land: true),
            self::Articles => app(ArticleSeeder::class)->run(),
            self::Adverts => app(AdvertSeeder::class)->run(),
            self::Engagements => app(DemoEngagementSeeder::class)->run(),
            self::Enquiries => app(DemoEnquirySeeder::class)->run(),
            default => null,
        };
    }

    /**
     * Delete the dataset. Models with stored images are deleted one by one so their files go too.
     */
    public function delete(User $admin): void
    {
        match ($this) {
            self::Clients => self::deleteUsers(User::where('is_admin', false)),
            self::Properties => self::deleteProperties(),
            self::Investments => InvestmentOpportunity::whereNot(fn (Builder $query) => $query->land())->each(fn (InvestmentOpportunity $opportunity) => $opportunity->delete()),
            self::LandBanking => InvestmentOpportunity::query()->land()->each(fn (InvestmentOpportunity $opportunity) => $opportunity->delete()),
            self::Articles => Article::query()->each(fn (Article $article) => $article->delete()),
            self::Adverts => Advert::query()->each(fn (Advert $advert) => $advert->delete()),
            self::Engagements => Engagement::query()->delete(),
            self::Enquiries => Enquiry::query()->delete(),
            self::Notifications => DatabaseNotification::query()->delete(),
            self::PageContent => PageSection::query()->each(fn (PageSection $section) => $section->resetToDefault()),
            self::Appearance => SiteSetting::query()->each(fn (SiteSetting $setting) => $setting->delete()),
            self::Admins => self::deleteUsers(User::where('is_admin', true)->whereKeyNot($admin->getKey())),
        };
    }

    public function countLabel(int $count): string
    {
        return $count.' '.Str::plural($this->noun(), $count);
    }

    /**
     * The datasets to seed for a selection, with their requirements, in a safe order.
     *
     * @param  iterable<self>  $selected
     * @return list<self>
     */
    public static function seedPlan(iterable $selected): array
    {
        $wanted = collect($selected)->flatMap(fn (self $set) => [...$set->requires(), $set]);

        return array_values(array_filter(self::seedOrder(), fn (self $set) => $wanted->contains($set)));
    }

    /**
     * The datasets to delete for a selection, in a safe order.
     *
     * @param  iterable<self>  $selected
     * @return list<self>
     */
    public static function deletePlan(iterable $selected): array
    {
        $wanted = collect($selected);

        return array_values(array_filter(self::deleteOrder(), fn (self $set) => $wanted->contains($set)));
    }

    /**
     * @return list<self>
     */
    public static function seedOrder(): array
    {
        return [self::Clients, self::Properties, self::Investments, self::LandBanking, self::Articles, self::Adverts, self::Engagements, self::Enquiries];
    }

    /**
     * @return list<self>
     */
    public static function deleteOrder(): array
    {
        return [self::Engagements, self::Enquiries, self::Notifications, self::Properties, self::Investments, self::LandBanking, self::Articles, self::Adverts, self::Clients, self::Admins, self::PageContent, self::Appearance];
    }

    /**
     * @return array<string, list<self>>
     */
    public static function grouped(bool $seedableOnly = false): array
    {
        return collect(self::cases())
            ->filter(fn (self $set) => ! $seedableOnly || $set->isSeedable())
            ->groupBy(fn (self $set) => $set->group())
            ->map(fn ($sets) => $sets->values()->all())
            ->all();
    }

    /**
     * @param  Builder<User>  $users
     */
    private static function deleteUsers(Builder $users): void
    {
        $ids = $users->pluck('id');

        DatabaseNotification::where('notifiable_type', (new User)->getMorphClass())->whereIn('notifiable_id', $ids)->delete();
        User::whereIn('id', $ids)->delete();
    }

    private static function deleteProperties(): void
    {
        Engagement::query()->delete();
        PropertyImage::query()->each(fn (PropertyImage $image) => $image->delete());
        Property::query()->delete();
    }
}
