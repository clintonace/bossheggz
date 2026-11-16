---
name: seggezapp-site
description: "Use when building or changing the SeggzApp property website: public property listings (sale/rent), property detail pages, the 'engage' (inquiry) flow for guests and signed-in users, the engager's account area (engaged properties + progress), notifications/emails about engaged properties, the admin dashboard (properties, engagements, progress updates), or the site's look and feel (modern + classic design, top click loader)."
---

# SeggzApp Property Website

SeggzApp is a Laravel 13 website where an **admin posts properties for sale or rent** and the public browses them and **engages** (expresses interest) with the ones they like. Treat this file as the product brief; the user will add more features over time — extend this file when they do.

## Product rules (source of truth)

### Roles
- **Admin** (`users.is_admin = true`): manages properties and engagements from `/admin`. Create one with `php artisan app:make-admin {email}`.
- **Engager (signed in)**: a regular registered user ("client"). Can engage, and has a **client dashboard** at `/account` (layout `layouts/client`: white sidebar, ivory content): Overview (stats, active engagements with progress trackers, recent activity feed, recommended properties similar to what they engaged), My engagements (`/account/engagements`, All / In progress / Completed tabs), engagement detail with timeline, Notifications, and Profile & security (details + password change). Receives updates **in-app (database notifications) and by email**.
- **Test account switcher** (`<x-dev-account-switcher />`, in every layout; `POST /dev/switch-account/{user}`): floating "Test accounts" button that signs in as any admin/client in one click. It is a login backdoor — gated by `config('listings.dev_account_switcher')`, which defaults to on **only** when `APP_ENV=local` (override with `DEV_ACCOUNT_SWITCHER`). The controller 404s and the button doesn't render when it's off. Never enable it in production.
- **Engager (guest)**: engages by entering name + email (phone/message optional). No account, no automatic emails — the admin reaches out **manually** (the admin UI gives a mailto/phone link and flags the engagement as "guest").

### Properties
- `listing_type`: `sale` | `rent` (rent has a `rent_period`: month/year).
- `status`: `draft` (hidden from public) | `published` | `under_offer` | `sold` | `rented`. Only non-draft properties are public; `sold`/`rented` stay visible but cannot be engaged.
- Multiple images per property, ordered; first image is the cover. Stored on the `public` disk (`php artisan storage:link`). Images whose `path` is an absolute URL are used as-is (seed/demo data).
- When the admin changes a property's **price or status**, every *registered* engager of that property is notified (mail + in-app).

### Adverts
- Admin manages adverts at `/admin/adverts`. The sidebar "Adverts" item is a **dropdown** (All adverts, Home page, Listings page, Property page, Pop-up adverts, + New advert). The index has a "Show adverts for" dropdown; choosing a page shows a **page map** — its sections top to bottom with a dashed spot before/after each (and "inside" the property grid), the adverts in each spot, and "Add advert here" (pre-fills the create form via `?format=&page=&section=&position=`).
- Fields: title, sponsor, short body, button text, `http(s)` link, optional image, active flag, optional start/end schedule, plus placement. Edit page shows a live preview.
- **Two formats** (`App\Enums\AdvertFormat`):
  - `inline` — `page` (`App\Enums\AdvertPage`: home/listings/property) + `section` (defined in `AdvertPage::sections()`, top-to-bottom, each with its allowed positions) + `position` (`App\Enums\AdvertPosition`: before/after, or `inside` for the listings `grid` only — rendered as cards after every 3rd property, max 2, and the page gives up that many property slots so rows stay full) + `width` (`App\Enums\AdvertWidth`: half = 6/12, full = 12/12, always full on phones) + `sort_order`. All live adverts in a spot render together in a 12-column grid via `<x-advert-slot :adverts="$adverts" at="section:position" />`; controllers pass `Advert::slotsFor($page)`.
  - `popup` ("major" advert) — `page` is a page or `all`; `display_seconds` 3–60. Rendered by `<x-advert-popup>` in the public layout (chosen by `Advert::popupFor()` from the route; never in admin/client dashboards). Shows ~1s after load, **once per browser session**, auto-closes with a countdown; clicking anywhere on the advert opens the advertiser in a new tab; only ✕ (or Esc/backdrop) closes it.
- New page sections must be added to `AdvertPage::sections()` **and** get `<x-advert-slot>` tags before/after them in the view.
- Only **live** adverts (active and within schedule) render. Always labelled "Sponsored"; links go through `/ads/{advert}` (counts clicks, 404 when not live) with `target="_blank" rel="sponsored noopener"`.
- Adverts can promote **anything** (partner businesses, products, events) — they are not property listings, so demo/example copy must not look like property ads.
- Demo adverts: `AdvertSeeder` (idempotent) — beach-travel full banner after "Latest" (home), gym card inside the listings grid, coffee + boat-tour half-width pair after the listings results, and a phone-launch pop-up on the home page (8s). All fictional brands linking to example.com.

### Engagements (the progress pipeline)
`new → contacted → viewing_scheduled → negotiating → closed`, with `cancelled` as an exit. Defined in `App\Enums\EngagementStatus` (label, colour, step).
- The admin moves an engagement forward from `/admin/engagements/{id}` and can attach a note. Each change is stored as an `EngagementUpdate` (timeline).
- Registered engagers get `EngagementProgressUpdated` (mail + database). Guests get nothing automatic.
- A signed-in user who engages a property they already have an open engagement on is redirected to that engagement instead of creating a duplicate.
- Admins are notified (mail + database) of every new engagement.

## Design direction — "modern and classic"
- Typography: **Fraunces** (serif) for display headings, **Instrument Sans** for UI/body. Both loaded via `bunny()` in `vite.config.js` + `@fonts`.
- Palette (Tailwind v4 `@theme` in `resources/css/app.css`): warm ivory background, deep ink (`ink-*`) text/surfaces, **brass** (`brass-*`) accent. Stone neutrals for borders/muted text.
- Generous whitespace, large rounded images (`rounded-2xl`/`rounded-3xl`), subtle shadows, soft hover lift, scroll-reveal (`data-reveal`).
- Admin: dark ink sidebar, light content area, stat cards, clean tables, status pills.
- Client dashboard: same app-shell structure as admin but a white sidebar with an ink active state and a dark welcome banner — so clients and admins never confuse the two areas.
- **Top click loader is mandatory on every page** (`#page-loader` in both layouts, logic in `resources/js/app.js`): it runs on every same-origin link click and form submit until the next page loads, and does a short sweep on any other button click. New layouts must include `<x-page-loader />`.
- No extra JS framework: interactivity is vanilla JS in `resources/js/app.js` via `data-*` hooks (`data-toggle`, `data-gallery`, `data-confirm`, `data-reveal`, `data-image-preview`). Don't add npm/composer dependencies without asking.
- Reuse Blade components in `resources/views/components` (`property-card`, `status-badge`, `progress-tracker`, `icon`, form fields) before writing new markup.

## Site manager (appearance)
- `/admin/site/{area}` (sidebar dropdown "Site manager" → Landing page / Client dashboard / Admin dashboard; `App\Enums\SiteArea`). Each area stores its own settings in `site_settings` (`App\Models\SiteSetting::for($area)` merges saved values over `SiteArea::defaults()`): accent / primary / background colours (+ presets in `config/appearance.php`), heading + body font (curated list in `config/appearance.php`; non-bundled fonts load from Bunny Fonts), base font size 14–19px (scales the whole UI via the root font size), sidebar light/dark (admin/client) or footer light/dark (landing), colour mode light/dark/system, animations on/off. Live preview on the edit page; "Reset to defaults" deletes the row.
- How theming works: `<x-site-theme>` in every layout's `<head>` outputs CSS variables derived from the three colours by `SiteSetting::recipes()` (shared with the preview JS). Layouts set `<html data-mode data-motion>`. **Dark mode = the neutral (`ink-*`, `white`, `ivory`, `sand`) and `brass-*` scales are inverted**, so every page supports it automatically — therefore **never hard-code hex colours in views; always use theme tokens** (`bg-white`, `bg-ivory`, `text-ink-*`, `bg-brass-*` …).
- **Per-user light/dark/auto**: every signed-in user has an Appearance switch (Light / Dark / Auto) in the **profile menu at the top-right** of every area (`<x-profile-menu>` in the public header, client topbar and admin topbar). Saved to `users.theme_mode` via `PUT /account/appearance` (instant JS switch, then saved in the background) and it **overrides** the area's Site manager mode (`SiteSetting::modeFor($user)` → layouts' `$colorMode`).
- **Sidebars follow the mode**: both dashboard sidebars are designed light; Site manager sidebar option is `auto` (default — dark in dark mode, light in light mode), `light` (always light → `data-palette="normal"`) or `dark` (always dark → `data-palette="inverted"`). Keep sidebar markup light-authored so "auto" just inherits the page palette.
- `data-palette="normal"` keeps an element in the normal (light-mode) palette even in dark mode (used for intentionally dark panels: footer, auth photo panel, advert banners, pop-up, dark hero cards). `data-palette="inverted"` flips one element: sidebars/footers are designed in one tone (admin sidebar & footer dark, client sidebar light) and the other tone is that palette inverted.

## Where things live
- Public: `HomeController`, `PropertyController`, `EngagementController`; views in `resources/views/{home,properties}`.
- Auth: `App\Http\Controllers\Auth\*`, views in `resources/views/auth`.
- Account: `App\Http\Controllers\Account\*`, views in `resources/views/account`.
- Admin: `App\Http\Controllers\Admin\*` behind `auth` + `admin` middleware, views in `resources/views/admin`, layout `layouts/admin`.
- Currency symbol: `config('listings.currency')` (`LISTING_CURRENCY` env).

## Workflow
1. Read the request, then the relevant routes (`php artisan route:list --except-vendor`), controllers, views and tests.
2. Confirm any ambiguity that changes product behaviour (roles, who gets notified, what guests can see).
3. Implement following the rules above and the Boost guidelines in `CLAUDE.md` (Pint, enums, form requests, named routes).
4. Keep local development working through `docker compose` on host port `8091`; run `npm run build` after frontend changes. The compose command must keep `php artisan serve --no-reload` — without it, `serve` strips Docker's env vars and the app silently falls back to `.env` (SQLite) instead of MySQL. After changing composer dependencies, run `docker compose exec app composer install` (vendor lives in a named volume).
5. Add/adjust feature tests in `tests/Feature` and run `php artisan test --compact`. Report remaining gaps clearly.
