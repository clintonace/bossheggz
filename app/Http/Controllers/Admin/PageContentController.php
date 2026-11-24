<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LandingPage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePageContentRequest;
use App\Models\PageSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageContentController extends Controller
{
    public function index(): View
    {
        $pages = collect(LandingPage::cases())->map(function (LandingPage $page) {
            $sections = $page->content();

            return [
                'page' => $page,
                'sections' => $sections->count(),
                'customised' => $sections->filter->isCustomised()->count(),
                'hidden' => $sections->reject->isShown()->count(),
                'updated_at' => $sections->pluck('updated_at')->filter()->max(),
            ];
        });

        return view('admin.pages.index', ['pages' => $pages]);
    }

    public function edit(LandingPage $page): View
    {
        return view('admin.pages.edit', [
            'page' => $page,
            'sections' => $page->content(),
            'icons' => config('page_content.icons'),
        ]);
    }

    public function update(UpdatePageContentRequest $request, LandingPage $page): RedirectResponse
    {
        foreach ($page->sections() as $key => $definition) {
            $values = $request->validated("sections.{$key}") ?? [];

            foreach ($definition['fields'] as $name => $field) {
                if ($field['type'] === 'image' && $request->hasFile("uploads.{$key}.{$name}")) {
                    $values[$name] = $request->file("uploads.{$key}.{$name}")->store(PageSection::ImageDirectory, 'public');
                }
            }

            PageSection::updateSection($page, $key, $values, $request->boolean("visibility.{$key}", true));
        }

        return to_route('admin.pages.edit', $page)->with('success', "{$page->label()} page saved.");
    }

    public function reset(LandingPage $page, string $section): RedirectResponse
    {
        abort_unless($page->hasSection($section), 404);

        $pageSection = $page->content()->get($section);
        $pageSection->resetToDefault();

        return redirect(route('admin.pages.edit', $page).'#section-'.$section)
            ->with('success', "{$pageSection->label()} reset to the default content.");
    }
}
