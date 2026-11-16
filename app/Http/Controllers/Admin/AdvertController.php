<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdvertFormat;
use App\Enums\AdvertPage;
use App\Enums\AdvertPosition;
use App\Enums\AdvertWidth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdvertRequest;
use App\Models\Advert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdvertController extends Controller
{
    /**
     * "view" is all, popup, or a page (home/listings/property) to show that page's advert map.
     */
    public function index(Request $request): View
    {
        $view = $request->query('view', 'all');
        $page = AdvertPage::tryFrom((string) $view);
        $view = $page ? $page->value : (in_array($view, ['all', 'popup'], true) ? $view : 'all');

        $adverts = Advert::query()
            ->when($page, fn ($query) => $query->where('format', AdvertFormat::Inline)->where('page', $page->value))
            ->when($view === 'popup', fn ($query) => $query->where('format', AdvertFormat::Popup))
            ->ordered()
            ->get();

        $all = Advert::all();

        return view('admin.adverts.index', [
            'view' => $view,
            'page' => $page,
            'adverts' => $adverts,
            'slots' => $adverts->groupBy(fn (Advert $advert) => $advert->slotKey()),
            'liveCount' => $all->filter(fn (Advert $advert) => $advert->state() === 'live')->count(),
            'totalCount' => $all->count(),
            'totalClicks' => $all->sum('clicks'),
        ]);
    }

    /**
     * Query parameters (format, page, section, position) pre-fill the placement, e.g. from "Add advert here".
     */
    public function create(Request $request): View
    {
        $format = AdvertFormat::tryFrom((string) $request->query('format')) ?? AdvertFormat::Inline;

        return view('admin.adverts.create', ['advert' => new Advert([
            'format' => $format,
            'page' => $request->query('page', $format === AdvertFormat::Popup ? 'all' : AdvertPage::Home->value),
            'section' => $request->query('section', 'latest'),
            'position' => AdvertPosition::tryFrom((string) $request->query('position')) ?? AdvertPosition::After,
            'width' => AdvertWidth::Full,
            'display_seconds' => 8,
            'sort_order' => 0,
            'cta_label' => 'Learn more',
            'is_active' => true,
        ])]);
    }

    public function store(AdvertRequest $request): RedirectResponse
    {
        $advert = new Advert($request->advertAttributes());
        $advert->image_path = $request->file('image')?->store('adverts', 'public');
        $advert->save();

        return to_route('admin.adverts.edit', $advert)->with('success', 'Advert created.');
    }

    public function edit(Advert $advert): View
    {
        return view('admin.adverts.edit', ['advert' => $advert]);
    }

    public function update(AdvertRequest $request, Advert $advert): RedirectResponse
    {
        $advert->fill($request->advertAttributes());

        if ($request->hasFile('image') || $request->validated('remove_image')) {
            $advert->deleteStoredImage();
            $advert->image_path = $request->file('image')?->store('adverts', 'public');
        }

        $advert->save();

        return back()->with('success', 'Advert updated.');
    }

    /**
     * Pause or resume an advert.
     */
    public function toggle(Advert $advert): RedirectResponse
    {
        $advert->update(['is_active' => ! $advert->is_active]);

        return back()->with('success', $advert->is_active ? 'Advert resumed.' : 'Advert paused — it is no longer shown on the site.');
    }

    public function destroy(Advert $advert): RedirectResponse
    {
        $advert->delete();

        return to_route('admin.adverts.index')->with('success', 'Advert deleted.');
    }
}
