<?php

namespace App\Http\Controllers;

use App\Models\Curriculum\LearningOutcome;
use App\Models\Curriculum\LearningIndicator;
use App\Models\Curriculum\Strand;
use App\Models\Curriculum\SubStrand;
use App\Models\Curriculum\Subject;
use App\Models\Curriculum\LearningArea;
use App\Models\Academic\GradeLevel;
use App\Models\Academic\SchoolClass;
use App\Models\Teacher;
use App\Models\TeacherSubject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SyllabusController extends Controller
{
    public function index(Request $request): Response
    {
        $subjectGrades = DB::table('subject_grade_levels')
            ->where('is_active', true)
            ->get(['subject_id', 'grade_level_id'])
            ->groupBy('subject_id')
            ->map(fn($rows) => collect($rows)->pluck('grade_level_id')->values())
            ->all();

        $subjects = Subject::active()
            ->with(['learningArea:id,name'])
            ->orderBy('name')
            ->get(['id', 'name', 'learning_area_id'])
            ->map(function ($subject) use ($subjectGrades) {
                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'learning_area_id' => $subject->learning_area_id,
                    'learning_area' => $subject->learningArea?->name,
                    'grade_level_ids' => $subjectGrades[$subject->id] ?? [],
                ];
            });

        $grades = GradeLevel::orderBy('level_order')->get(['id', 'name']);
        $learningAreas = LearningArea::active()->ordered()->get(['id', 'name']);

        $academicYear = DB::table('academic_years')->where('is_current', true)->first() 
            ?? DB::table('academic_years')->orderByDesc('start_date')->first();

        $teachers = Teacher::where('status', 'active')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'staff_number'])
            ->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->full_name,
                'staff_number' => $t->staff_number,
            ]);

        $classes = SchoolClass::query()
            ->with(['gradeLevel:id,name', 'stream:id,name', 'academicYear:id,name'])
            ->orderByDesc('academic_year_id')
            ->orderBy('name')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'grade_id' => $c->grade_level_id,
                'grade_name' => $c->gradeLevel?->name,
                'stream' => $c->stream?->name,
                'academic_year' => $c->academicYear?->name,
            ]);

        return Inertia::render('curriculum/syllabus/Index', [
            'subjects' => $subjects,
            'grades' => $grades,
            'learningAreas' => $learningAreas,
            'teachers' => $teachers,
            'classes' => $classes,
            'academicYear' => $academicYear,
        ]);

    }

    public function show(Request $request, Subject $subject, GradeLevel $grade): Response
    {
        $strands = Strand::where('subject_id', $subject->id)
            ->where('grade_level_id', $grade->id)
            ->orderBy('display_order')
            ->get();

        $academicYear = DB::table('academic_years')->where('is_current', true)->first() 
            ?? DB::table('academic_years')->orderByDesc('start_date')->first();

        $teachers = Teacher::where('status', 'active')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'staff_number'])
            ->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->full_name,
                'staff_number' => $t->staff_number,
            ]);

        $classes = SchoolClass::where('grade_level_id', $grade->id)
            ->with(['stream:id,name', 'academicYear:id,name'])
            ->orderByDesc('academic_year_id')
            ->orderBy('name')
            ->get(['id', 'name', 'stream_id', 'academic_year_id'])
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'stream' => $c->stream?->name,
                'academic_year' => $c->academicYear?->name,
            ]);

        $allocations = TeacherSubject::where('subject_id', $subject->id)
            ->whereIn('class_id', $classes->pluck('id'))
            ->where('academic_year_id', $academicYear?->id)
            ->with(['teacher:id,first_name,middle_name,last_name'])
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'teacher_id' => $a->teacher_id,
                'class_id' => $a->class_id,
                'is_primary' => $a->is_primary_teacher,
                'teacher_name' => $a->teacher?->full_name,
            ]);

        return Inertia::render('curriculum/syllabus/Show', [
            'subject' => $subject,
            'grade' => $grade,
            'strands' => $strands,
            'teachers' => $teachers,
            'classes' => $classes,
            'allocations' => $allocations,
            'academicYear' => $academicYear,
        ]);
    }

    public function showTopic(Request $request, Strand $strand): Response
    {
        $strand->load(['subStrands.learningOutcomes.indicators', 'subject', 'gradeLevel']);

        return Inertia::render('curriculum/syllabus/TopicShow', [
            'strand' => $strand,
            'subject' => $strand->subject,
            'grade' => $strand->gradeLevel,
        ]);
    }

    public function storeOutcome(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sub_strand_id' => 'required|exists:sub_strands,id',
            'outcome' => 'required|string',
            'code' => 'nullable|string|max:30',
            'description' => 'nullable|string',
            'outcome_type' => 'nullable|string|max:50',
            'display_order' => 'required|integer|min:0',
        ]);

        LearningOutcome::create(array_merge($validated, [
            'school_id' => auth()->user()->school_id,
        ]));

        return back()->with('success', 'Learning outcome added successfully.');
    }

    public function updateOutcome(Request $request, LearningOutcome $outcome): RedirectResponse
    {
        $validated = $request->validate([
            'outcome' => 'required|string',
            'code' => 'nullable|string|max:30',
            'description' => 'nullable|string',
            'outcome_type' => 'nullable|string|max:50',
            'display_order' => 'required|integer|min:0',
        ]);

        $outcome->update($validated);

        return back()->with('success', 'Learning outcome updated successfully.');
    }

    public function destroyOutcome(LearningOutcome $outcome): RedirectResponse
    {
        $outcome->delete();
        return back()->with('success', 'Learning outcome removed.');
    }

    // --- Competency Tracking ---

    public function markAchieved(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => 'required',
            'learning_outcome_id' => 'required',
            'achievement_level' => 'required|string', // e.g., EE, ME, AE, BE
            'assessment_date' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        StudentAchievement::updateOrCreate(
            [
                'student_id' => $validated['student_id'],
                'learning_outcome_id' => $validated['learning_outcome_id'],
            ],
            array_merge($validated, [
                'school_id' => Auth::user()->school_id,
                'assessed_by' => Auth::id(),
            ])
        );

        return back()->with('success', 'Achievement recorded successfully.');
    }

    public function getStudentProgress($student_id, $subject_id): Response
    {
        $student = DB::table('students')->where('id', $student_id)->first();
        $achievements = StudentAchievement::where('student_id', $student_id)
            ->whereHas('learningOutcome.subStrand.strand', function($query) use ($subject_id) {
                $query->where('subject_id', $subject_id);
            })
            ->with('learningOutcome')
            ->get();

        return Inertia::render('curriculum/portfolio/Progress', [
            'student' => $student,
            'achievements' => $achievements,
        ]);
    }

    public function storeIndicator(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'learning_outcome_id' => 'required|exists:learning_outcomes,id',
            'indicator' => 'required|string',
            'description' => 'nullable|string',
            'display_order' => 'required|integer|min:0',
        ]);

        LearningIndicator::create(array_merge($validated, [
            'school_id' => auth()->user()->school_id,
        ]));

        return back()->with('success', 'Indicator added successfully.');
    }

    // Subject CRUD
    public function storeSubject(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'learning_area_id' => 'required|exists:learning_areas,id',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        Subject::create(array_merge($validated, [
            'school_id' => auth()->user()->school_id,
            'is_active' => true,
        ]));

        return back()->with('success', 'Subject created.');
    }

    public function updateSubject(Request $request, Subject $subject): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'learning_area_id' => 'required|exists:learning_areas,id',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $subject->update($validated);
        return back()->with('success', 'Subject updated.');
    }

    public function destroySubject(Subject $subject): RedirectResponse
    {
        $subject->delete();
        return back()->with('success', 'Subject removed.');
    }

    // Strand CRUD
    public function storeStrand(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'grade_level_id' => 'required|exists:grade_levels,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'display_order' => 'required|integer|min:0',
        ]);

        Strand::create(array_merge($validated, [
            'school_id' => auth()->user()->school_id,
        ]));

        return back()->with('success', 'Topic created.');
    }

    public function updateStrand(Request $request, Strand $strand): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'display_order' => 'required|integer|min:0',
        ]);

        $strand->update($validated);
        return back()->with('success', 'Topic updated.');
    }

    public function destroyStrand(Strand $strand): RedirectResponse
    {
        $strand->delete();
        return back()->with('success', 'Topic removed.');
    }

    // SubStrand CRUD
    public function storeSubStrand(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'strand_id' => 'required|exists:strands,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'display_order' => 'required|integer|min:0',
        ]);

        SubStrand::create(array_merge($validated, [
            'school_id' => auth()->user()->school_id,
        ]));

        return back()->with('success', 'Sub-topic created.');
    }

    public function updateSubStrand(Request $request, SubStrand $subStrand): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'display_order' => 'required|integer|min:0',
        ]);

        $subStrand->update($validated);
        return back()->with('success', 'Sub-topic updated.');
    }

    public function bulkStoreSubjects(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);
        
        // Clean headers
        $header = array_map('trim', $header);

        $school_id = auth()->user()->school_id;
        $count = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== FALSE) {
                $data = array_combine($header, $row);
                $data = array_map('trim', $data);

                $learningAreaName = $data['learning_area'] ?? null;
                $learningArea = null;

                if ($learningAreaName) {
                    // Bypass SchoolScope so we also find global (school_id=NULL) seeded areas
                    $allAreas = LearningArea::withoutGlobalScopes()
                        ->where(function ($q) use ($school_id) {
                            $q->where('school_id', $school_id)->orWhereNull('school_id');
                        })->get();

                    // 1. Exact case-insensitive match
                    $learningArea = $allAreas->first(
                        fn($a) => strtolower(trim($a->name)) === strtolower(trim($learningAreaName))
                    );

                    // 2. Keyword overlap scoring
                    if (!$learningArea) {
                        $stopwords = ['and', 'or', 'the', 'of', 'in', 'for'];
                        $csvWords = array_filter(
                            explode(' ', strtolower($learningAreaName)),
                            fn($w) => strlen($w) > 2 && !in_array($w, $stopwords)
                        );

                        $bestScore = 0;
                        $bestArea = null;

                        foreach ($allAreas as $area) {
                            $dbName = strtolower($area->name);
                            $score = 0;
                            foreach ($csvWords as $word) {
                                $stem = rtrim($word, 's');
                                if (str_contains($dbName, $word) || str_contains($dbName, $stem)) {
                                    $score++;
                                }
                            }
                            if ($score > $bestScore) {
                                $bestScore = $score;
                                $bestArea = $area;
                            }
                        }

                        if ($bestScore > 0) {
                            $learningArea = $bestArea;
                        }
                    }

                    // 3. similar_text fallback
                    if (!$learningArea) {
                        $bestScore = 0;
                        $bestArea = null;
                        foreach ($allAreas as $area) {
                            similar_text(strtolower($learningAreaName), strtolower($area->name), $percent);
                            if ($percent > $bestScore && $percent >= 40) {
                                $bestScore = $percent;
                                $bestArea = $area;
                            }
                        }
                        $learningArea = $bestArea;
                    }

                    // 4. Auto-create with safe unique code
                    if (!$learningArea) {
                        $words = explode(' ', $learningAreaName);
                        $baseCode = count($words) > 1
                            ? strtoupper(implode('', array_map(fn($w) => substr($w, 0, 1), $words)))
                            : strtoupper(substr($learningAreaName, 0, 3));

                        $code = $baseCode;
                        $i = 1;
                        while (LearningArea::withoutGlobalScopes()->where('code', $code)->exists()) {
                            $code = $baseCode . $i++;
                        }

                        $learningArea = LearningArea::create([
                            'name'      => $learningAreaName,
                            'code'      => $code,
                            'school_id' => $school_id,
                            'is_active' => true,
                        ]);
                    }
                }

                if (!$learningArea) {
                    $errors[] = "Row " . ($count + 2) . ": Learning area not specified.";
                    continue;
                }

                // Resolve a globally-unique subject code
                // (subjects_code_unique index covers ALL schools, not per-school)
                $rawCode = !empty($data['code']) ? strtoupper(trim($data['code'])) : null;

                if (!$rawCode) {
                    // Generate from name initials
                    $words = explode(' ', $data['name']);
                    $rawCode = count($words) > 1
                        ? strtoupper(implode('', array_map(fn($w) => substr($w, 0, 1), $words)))
                        : strtoupper(substr($data['name'], 0, 3));
                }

                // Check if this code already belongs to THIS school's subject already
                $existingForThisSchool = Subject::withoutGlobalScopes()
                    ->where('code', $rawCode)
                    ->where('school_id', $school_id)
                    ->first();

                if ($existingForThisSchool) {
                    // Update the existing record instead of inserting
                    $existingForThisSchool->update([
                        'learning_area_id' => $learningArea->id,
                        'name'             => $data['name'],
                        'description'      => $data['description'] ?? null,
                        'is_active'        => true,
                    ]);
                    $count++;
                    continue;
                }

                // If code is taken globally (other school or seeded), make a school-specific variant
                $subjectCode = $rawCode;
                if (Subject::withoutGlobalScopes()->where('code', $subjectCode)->exists()) {
                    $subjectCode = $rawCode . '-' . $school_id;
                    $i = 1;
                    while (Subject::withoutGlobalScopes()->where('code', $subjectCode)->exists()) {
                        $subjectCode = $rawCode . '-' . $school_id . '-' . $i++;
                    }
                }

                Subject::create([
                    'school_id'        => $school_id,
                    'learning_area_id' => $learningArea->id,
                    'name'             => $data['name'],
                    'code'             => $subjectCode,
                    'description'      => $data['description'] ?? null,
                    'is_active'        => true,
                ]);
                $count++;

            }

            if (!empty($errors)) {
                DB::rollBack();
                return back()->with('error', 'Import failed: ' . implode(' ', $errors));
            }

            DB::commit();
            fclose($handle);
            return back()->with('success', "Imported {$count} subjects successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return back()->with('error', 'Error importing CSV: ' . $e->getMessage());
        }
    }

    public function downloadSubjectTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="subjects_template.csv"',
        ];

        $columns = ['name', 'learning_area', 'code', 'description'];

        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, [
                'Mathematics', 
                'Mathematics',
                'MAT', 
                'Introduction to logic and numbers'
            ]);
            fputcsv($file, [
                'Integrated Science',
                'Integrated Science',
                'INT-SCI',
                'Scientific concepts and inquiry',
            ]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
