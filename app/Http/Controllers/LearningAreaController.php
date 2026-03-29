<?php

namespace App\Http\Controllers;

use App\Models\Curriculum\LearningArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LearningAreaController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('curriculum/learning-areas/Index', [
            'learningAreas' => LearningArea::ordered()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:learning_areas,code',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'display_order' => 'nullable|integer',
        ]);

        LearningArea::create($validated);

        return back()->with('success', 'Learning Area created successfully.');
    }

    public function show(LearningArea $learningArea): Response
    {
        return Inertia::render('curriculum/learning-areas/Show', [
            'learningArea' => $learningArea->load('subjects'),
        ]);
    }

    public function update(Request $request, LearningArea $learningArea): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:learning_areas,code,' . $learningArea->id,
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'display_order' => 'nullable|integer',
        ]);

        $learningArea->update($validated);

        return back()->with('success', 'Learning Area updated successfully.');
    }

    public function destroy(LearningArea $learningArea): RedirectResponse
    {
        $learningArea->delete();

        return redirect()->route('curriculum.learning-areas.index')->with('success', 'Learning Area deleted successfully.');
    }
}
