<?php

namespace App\Http\Controllers;

use App\Models\Curriculum\CurriculumResource;
use App\Models\Curriculum\Subject;
use App\Models\Academic\GradeLevel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class LearningResourceController extends Controller
{
    public function index(Request $request): Response
    {
        $query = CurriculumResource::with(['subject', 'gradeLevel']);

        return Inertia::render('curriculum/resources/Index', [
            'resources' => $query->latest()->get(),
            'subjects' => Subject::active()->get(['id', 'name']),
            'grades' => GradeLevel::all(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'resource_type' => 'nullable',
            'description' => 'nullable|string',
            'subject_id' => 'nullable|exists:subjects,id',
            'grade_level_id' => 'nullable|exists:grade_levels,id',
            'file' => 'nullable|file|max:10240', // 10MB limit
            'url' => 'nullable|url',
        ]);

        $data = $validated;
        unset($data['file']);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('curriculum/resources', 'public');
            $data['file_path'] = $path;
            
            // Auto-detect resource type if not provided
            if (empty($data['resource_type'])) {
                $extension = strtolower($file->getClientOriginalExtension());
                $data['resource_type'] = match($extension) {
                    'pdf' => 'pdf',
                    'doc', 'docx' => 'doc',
                    'mp4', 'mov', 'avi' => 'video',
                    'mp3', 'wav' => 'audio',
                    'jpg', 'jpeg', 'png', 'gif' => 'image',
                    default => 'document'
                };
            }
        } elseif (!empty($data['url'])) {
            $data['resource_type'] = 'link';
        }

        if (empty($data['resource_type'])) {
            $data['resource_type'] = 'document';
        }

        if ($validated['subject_id']) {
            $data['resourceable_type'] = Subject::class;
            $data['resourceable_id'] = $validated['subject_id'];
        }

        CurriculumResource::create($data);

        return back()->with('success', 'Resource uploaded successfully.');
    }

    public function update(Request $request, CurriculumResource $resource): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'url' => 'nullable|url',
        ]);

        $resource->update($validated);
        return back()->with('success', 'Resource updated.');
    }

    public function destroy(CurriculumResource $resource): RedirectResponse
    {
        if ($resource->file_path) {
            Storage::disk('public')->delete($resource->file_path);
        }

        $resource->delete();

        return back()->with('success', 'Resource deleted successfully.');
    }
}
