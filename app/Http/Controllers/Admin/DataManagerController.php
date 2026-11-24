<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DataSet;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeleteDataRequest;
use App\Http\Requests\Admin\SeedDataRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Admin "Data manager": add demo data or delete data, choosing exactly which kinds.
 */
class DataManagerController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.data.index', [
            'groups' => DataSet::grouped(),
            'counts' => $this->counts($request->user()),
        ]);
    }

    public function seedForm(Request $request): View
    {
        return view('admin.data.seed', [
            'groups' => DataSet::grouped(seedableOnly: true),
            'counts' => $this->counts($request->user()),
            'selected' => $this->preselected($request),
        ]);
    }

    public function deleteForm(Request $request): View
    {
        return view('admin.data.delete', [
            'groups' => DataSet::grouped(),
            'counts' => $this->counts($request->user()),
            'selected' => $this->preselected($request),
        ]);
    }

    public function seed(SeedDataRequest $request): RedirectResponse
    {
        $plan = DataSet::seedPlan($request->dataSets());
        $admin = $request->user();
        $before = $this->counts($admin);

        DB::transaction(function () use ($plan): void {
            foreach ($plan as $dataSet) {
                $dataSet->seed();
            }
        });

        $added = collect($plan)
            ->map(fn (DataSet $dataSet) => [$dataSet, $dataSet->count($admin) - $before[$dataSet->value]])
            ->filter(fn (array $change) => $change[1] > 0)
            ->map(fn (array $change) => $change[0]->countLabel($change[1]));

        return to_route('admin.data.index')->with('success', $added->isEmpty()
            ? 'That demo data is already in place — nothing new was added.'
            : 'Demo data added: '.$added->implode(', ').'.');
    }

    public function destroy(DeleteDataRequest $request): RedirectResponse
    {
        $plan = DataSet::deletePlan($request->dataSets());
        $admin = $request->user();
        $before = $this->counts($admin);

        DB::transaction(function () use ($plan, $admin): void {
            foreach ($plan as $dataSet) {
                $dataSet->delete($admin);
            }
        });

        $after = $this->counts($admin);
        $removed = collect(DataSet::deleteOrder())
            ->map(fn (DataSet $dataSet) => [$dataSet, $before[$dataSet->value] - $after[$dataSet->value]])
            ->filter(fn (array $change) => $change[1] > 0)
            ->map(fn (array $change) => $change[0]->countLabel($change[1]));

        return to_route('admin.data.index')->with('success', $removed->isEmpty()
            ? 'There was nothing to delete.'
            : 'Deleted '.$removed->implode(', ').'. Your admin account was kept.');
    }

    /**
     * @return array<string, int>
     */
    private function counts(User $admin): array
    {
        return collect(DataSet::cases())->mapWithKeys(fn (DataSet $dataSet) => [$dataSet->value => $dataSet->count($admin)])->all();
    }

    /**
     * Datasets ticked from a link such as ?only=properties.
     *
     * @return list<string>
     */
    private function preselected(Request $request): array
    {
        return collect(explode(',', (string) $request->query('only')))
            ->filter(fn (string $value) => DataSet::tryFrom($value) !== null)
            ->values()
            ->all();
    }
}
