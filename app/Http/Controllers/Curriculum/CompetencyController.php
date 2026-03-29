<?php

namespace App\Http\Controllers\Curriculum;

use App\Http\Controllers\Controller;
use App\Models\Curriculum\Competency;
use App\Models\Curriculum\CompetencyIndicator;
use App\Models\Academic\GradeLevel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class CompetencyController extends Controller
{
    public function index(): Response
    {
        // Fetch all competencies (Core ones have school_id = null)
        // We use withoutGlobalScopes to ensure we see the null ones if needed
        // but let's assume the model is refined.
        $competencies = Competency::orderBy('display_order')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        $grades = GradeLevel::orderBy('level_order')->get(['id', 'name']);

        $indicators = CompetencyIndicator::with(['competency', 'gradeLevel'])
            ->orderBy('display_order')
            ->get()
            ->groupBy('grade_level_id');

        return Inertia::render('curriculum/competencies/Index', [
            'competencies' => $competencies,
            'grades' => $grades,
            'indicators' => $indicators,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'category' => 'required|string|in:core,custom',
            'display_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        Competency::create($validated);

        return redirect()->back()->with('success', 'Competency created successfully.');
    }

    public function update(Request $request, Competency $competency): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'category' => 'required|string|in:core,custom',
            'display_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $competency->update($validated);

        return redirect()->back()->with('success', 'Competency updated successfully.');
    }

    public function destroy(Competency $competency): RedirectResponse
    {
        $competency->delete();
        return redirect()->back()->with('success', 'Competency deleted successfully.');
    }

    public function storeIndicator(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'competency_id' => 'required|exists:competencies,id',
            'grade_level_id' => 'required|exists:grade_levels,id',
            'indicator' => 'required|string|max:255',
            'description' => 'nullable|string',
            'display_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        CompetencyIndicator::create($validated);

        return redirect()->back()->with('success', 'Indicator created successfully.');
    }

    public function destroyIndicator(CompetencyIndicator $indicator): RedirectResponse
    {
        $indicator->delete();
        return redirect()->back()->with('success', 'Indicator deleted successfully.');
    }
}
