<?php

namespace Modules\Property\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Modules\Core\Http\Requests\DeleteMultiRequest;
use Modules\Property\Models\ProjectUnitType;
use Throwable;

class ProjectUnitTypeController extends Controller
{
    public function __construct()
    {
        $this->setActive('properties');
        $this->setActive('project_unit_types');
    }

    public function index()
    {
        $model = ProjectUnitType::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(config('core.page_size', 10));

        return view('property::admin.project_unit_type.index', compact('model'));
    }

    public function create()
    {
        return view('property::admin.project_unit_type.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $type = new ProjectUnitType;
        $type->name = $this->buildNameTranslations($validated['name']);
        $type->sort_order = (int) ($validated['sort_order'] ?? 0);
        $type->is_active = $request->boolean('is_active', true);
        $type->save();

        return redirect()->route('admin.project_unit_types.index');
    }

    public function edit(ProjectUnitType $project_unit_type)
    {
        return view('property::admin.project_unit_type.edit', [
            'projectUnitType' => $project_unit_type,
        ]);
    }

    public function update(Request $request, ProjectUnitType $project_unit_type): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $project_unit_type->name = $this->buildNameTranslations(
            $validated['name'],
            $project_unit_type->getTranslations('name')
        );
        $project_unit_type->sort_order = (int) ($validated['sort_order'] ?? 0);
        $project_unit_type->is_active = $request->boolean('is_active', true);
        $project_unit_type->save();

        return redirect()->route('admin.project_unit_types.index');
    }

    public function deleteMulti(DeleteMultiRequest $request): RedirectResponse
    {
        $ids = array_map('intval', (array) $request->input('ids', []));
        ProjectUnitType::query()->whereIn('id', $ids)->delete();

        return back();
    }

    /**
     * @param  array<string, string>  $existing
     * @return array<string, string>
     */
    private function buildNameTranslations(mixed $value, array $existing = []): array
    {
        $locale = app()->getLocale();
        $translations = $existing;

        if (is_array($value)) {
            foreach ($value as $lang => $text) {
                if (! is_string($lang) || ! is_scalar($text)) {
                    continue;
                }

                $clean = trim((string) $text);
                if ($clean !== '') {
                    $translations[$lang] = $clean;
                }
            }
        } else {
            $clean = trim((string) $value);
            if ($clean !== '') {
                $translations[$locale] = $clean;
            }
        }

        $sourceText = trim((string) ($translations[$locale] ?? Arr::first($translations) ?? ''));
        if ($sourceText === '') {
            return $translations;
        }

        $translations[$locale] = $sourceText;

        foreach (otherLangs() as $lang) {
            if (! is_string($lang) || $lang === $locale) {
                continue;
            }

            if (! empty($translations[$lang])) {
                continue;
            }

            try {
                $translations[$lang] = autoGoogleTranslator($lang, $sourceText);
            } catch (Throwable $exception) {
                Log::warning('Project unit type auto translation failed.', [
                    'field_locale' => $lang,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return $translations;
    }
}
