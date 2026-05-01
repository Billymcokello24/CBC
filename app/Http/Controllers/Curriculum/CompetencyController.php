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
use App\Models\Curriculum\Subject;
use App\Models\Curriculum\Strand;
use App\Models\Curriculum\SubStrand;

class CompetencyController extends Controller
{
    public function index(): Response
    {
        $schoolId = auth()->user()->school_id;

        $competencies = Competency::orderBy('display_order')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        $grades = GradeLevel::where('school_id', $schoolId)->orderBy('level_order')->get(['id', 'name']);

        $subjects = Subject::where('school_id', $schoolId)
            ->with(['strands.subStrands'])
            ->orderBy('name')
            ->get();

        $indicators = CompetencyIndicator::where('school_id', $schoolId)
            ->with(['competency', 'gradeLevel', 'subStrand.strand.subject'])
            ->orderBy('display_order')
            ->get();

        return Inertia::render('curriculum/competencies/Index', [
            'competencies' => $competencies,
            'grades' => $grades,
            'subjects' => $subjects,
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
            'sub_strand_id' => 'required|exists:sub_strands,id',
            'indicator' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'display_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $validated['school_id'] = auth()->user()->school_id;

        CompetencyIndicator::create($validated);

        return redirect()->back()->with('success', 'Indicator created successfully.');
    }

    public function destroyIndicator(CompetencyIndicator $indicator): RedirectResponse
    {
        $indicator->delete();
        return redirect()->back()->with('success', 'Indicator deleted successfully.');
    }

    public function downloadTemplate()
    {
        $headers = [
            'Subject',
            'Strand',
            'Sub Strand',
            'Grade',
            'Competency Code',
            'Indicator Text',
            'Indicator Code',
            'Description'
        ];

        $callback = function() use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            
            // Example row
            fputcsv($file, [
                'Mathematics',
                'Numbers',
                'Whole Numbers',
                'Grade 4',
                'CT',
                'Reads and writes numbers up to 10,000',
                'MATH-001',
                'Assessment criteria for whole numbers'
            ]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=competency_indicators_template.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ]);
    }

    public function importIndicators(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $data = array_map('str_getcsv', file($path));
        $header = array_shift($data);

        $schoolId = auth()->user()->school_id;

        DB::transaction(function () use ($data, $schoolId) {
            foreach ($data as $row) {
                if (count($row) < 6) continue;

                [$subjectName, $strandName, $subStrandName, $gradeName, $compCode, $indicatorText, $indicatorCode, $desc] = array_pad($row, 8, '');

                $subject = Subject::firstOrCreate(
                    ['school_id' => $schoolId, 'name' => $subjectName],
                    ['code' => strtoupper(substr($subjectName, 0, 4))]
                );

                $grade = GradeLevel::where('school_id', $schoolId)->where('name', 'LIKE', "%$gradeName%")->first();
                if (!$grade) continue;

                $strand = Strand::firstOrCreate(
                    ['subject_id' => $subject->id, 'name' => $strandName, 'grade_level_id' => $grade->id],
                    ['school_id' => $schoolId, 'code' => strtoupper(substr($strandName, 0, 3))]
                );

                $subStrand = SubStrand::firstOrCreate(
                    ['strand_id' => $strand->id, 'name' => $subStrandName],
                    ['school_id' => $schoolId, 'code' => strtoupper(substr($subStrandName, 0, 3))]
                );

                $competency = Competency::where('code', $compCode)->first();
                if (!$competency) {
                    $competency = Competency::create([
                        'name' => $compCode,
                        'code' => $compCode,
                        'category' => 'core'
                    ]);
                }

                CompetencyIndicator::updateOrCreate(
                    [
                        'school_id' => $schoolId,
                        'grade_level_id' => $grade->id,
                        'sub_strand_id' => $subStrand->id,
                        'indicator' => $indicatorText,
                    ],
                    [
                        'competency_id' => $competency->id,
                        'code' => $indicatorCode ?: "IND-" . rand(1000, 9999),
                        'description' => $desc,
                        'is_active' => true
                    ]
                );
            }
        });

        return redirect()->back()->with('success', 'Indicators imported successfully.');
    }
}
