<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SiteArea;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SiteSettingRequest;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function edit(SiteArea $area): View
    {
        return view('admin.site.edit', [
            'area' => $area,
            'setting' => SiteSetting::for($area),
            'fonts' => config('appearance.fonts'),
            'presets' => config('appearance.presets'),
            'recipes' => SiteSetting::recipes(),
        ]);
    }

    public function update(SiteSettingRequest $request, SiteArea $area): RedirectResponse
    {
        SiteSetting::updateOrCreate(['area' => $area], ['settings' => $request->validated()]);

        return back()->with('success', "{$area->label()} appearance saved.");
    }

    public function reset(SiteArea $area): RedirectResponse
    {
        SiteSetting::where('area', $area)->first()?->delete();

        return back()->with('success', "{$area->label()} reset to the default look.");
    }
}
