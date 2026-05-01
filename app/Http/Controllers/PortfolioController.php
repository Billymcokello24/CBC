<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Curriculum\Portfolio;
use App\Models\Curriculum\PortfolioItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Storage;

class PortfolioController extends Controller
{
    private function getSchoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(): Response
    {
        $schoolId = $this->getSchoolId();
        
        $students = Student::where('school_id', $schoolId)
            ->with(['currentClass', 'portfolio'])
            ->withCount('portfolioItems')
            ->paginate(20);

        return Inertia::render('assessments/PortfolioIndex', [
            'students' => $students,
        ]);
    }

    public function show(int $studentId): Response
    {
        $schoolId = $this->getSchoolId();
        $student = Student::with(['currentClass', 'portfolio.items.competencies', 'portfolio.items.subject'])
            ->findOrFail($studentId);
        
        // Find or create portfolio for student
        $portfolio = Portfolio::firstOrCreate([
            'student_id' => $studentId,
            'school_id' => $schoolId,
        ], [
            'title' => "Portfolio - " . $student->first_name . " " . $student->last_name,
            'is_public' => false,
        ]);

        return Inertia::render('assessments/PortfolioShow', [
            'student' => $student,
            'portfolio' => $portfolio->load(['items' => function($q) {
                $q->with(['subject', 'indicators', 'indicators.competency'])->orderBy('item_date', 'desc');
            }]),
            'subjects' => \App\Models\Curriculum\Subject::whereHas('schoolSubjects', function($q) use ($schoolId) {
                $q->where('school_id', $schoolId)->where('is_offered', true);
            })->get(),
            'competencies' => \App\Models\Curriculum\Competency::where('school_id', $schoolId)
                ->with('indicators')
                ->get(),
        ]);
    }

    public function storeItem(Request $request, int $portfolioId)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'item_type' => 'required|string|in:image,video,document,text,link',
            'item_date' => 'required|date',
            'subject_id' => 'nullable|exists:subjects,id',
            'file' => 'nullable|file|max:10240', // 10MB limit
            'url' => 'nullable|url',
        ]);

        $portfolio = Portfolio::findOrFail($portfolioId);
        
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('portfolios/' . $portfolio->id, 'public');
            $validated['file_path'] = $path;
        }

        $validated['portfolio_id'] = $portfolio->id;
        $validated['school_id'] = $this->getSchoolId();

        $item = PortfolioItem::create($validated);

        if ($request->has('indicator_ids')) {
            $item->indicators()->sync($request->indicator_ids);
        }

        return back()->with('success', 'Portfolio item added successfully.');
    }

    public function destroyItem(int $itemId)
    {
        $item = PortfolioItem::findOrFail($itemId);
        
        if ($item->file_path) {
            Storage::disk('public')->delete($item->file_path);
        }
        
        $item->delete();

        return back()->with('success', 'Portfolio item removed.');
    }
}
