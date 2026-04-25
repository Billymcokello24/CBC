<?php

namespace App\Http\Controllers;

use App\Models\Curriculum\CurriculumResource;
use App\Models\Curriculum\Subject;
use App\Models\Curriculum\ResourceFolder;
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
        $query = CurriculumResource::with(['subject', 'gradeLevel', 'folder']);

        return Inertia::render('curriculum/resources/Index', [
            'resources' => $query->latest()->get(),
            'subjects' => Subject::active()->get(['id', 'name']),
            'grades' => GradeLevel::all(['id', 'name']),
            'folders' => ResourceFolder::withCount('resources')->latest()->get(),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('curriculum/resources/Create', [
            'subjects' => Subject::active()->get(['id', 'name']),
            'grades' => GradeLevel::all(['id', 'name']),
            'folders' => ResourceFolder::latest()->get(['id', 'name']),
            'selectedFolderId' => $request->folder_id,
        ]);
    }

    public function edit(CurriculumResource $resource): Response
    {
        return Inertia::render('curriculum/resources/Edit', [
            'resource' => $resource,
            'subjects' => Subject::active()->get(['id', 'name']),
            'grades' => GradeLevel::all(['id', 'name']),
            'folders' => ResourceFolder::latest()->get(['id', 'name']),
        ]);
    }

    public function show(CurriculumResource $resource): Response
    {
        $resource->load(['subject', 'gradeLevel']);
        
        return Inertia::render('curriculum/resources/Show', [
            'resource' => $resource,
            'relatedResources' => CurriculumResource::with(['subject', 'gradeLevel'])
                ->where('resource_type', $resource->resource_type)
                ->where('id', '!=', $resource->id)
                ->limit(5)
                ->get()
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'resource_type' => 'nullable',
            'description' => 'nullable|string',
            'subject_id' => 'nullable|exists:subjects,id',
            'grade_level_id' => 'nullable|exists:grade_levels,id',
            'folder_id' => 'nullable|exists:resource_folders,id',
            'files' => 'nullable|array',
            'files.*' => 'file|max:20480', // 20MB per file
            'url' => 'nullable|url',
        ]);

        $baseData = $request->only([
            'title', 'resource_type', 'description', 
            'subject_id', 'grade_level_id', 'folder_id', 'url'
        ]);

        if ($request->subject_id) {
            $baseData['resourceable_type'] = Subject::class;
            $baseData['resourceable_id'] = $request->subject_id;
        }

        if ($request->hasFile('files')) {
            $files = $request->file('files');
            foreach ($files as $index => $file) {
                $data = $baseData;
                
                // If uploading multiple, append index to title except first one
                if (count($files) > 1) {
                    $data['title'] = $baseData['title'] . ' (' . ($index + 1) . ')';
                }

                $path = $file->store('curriculum/resources', 'public');
                $data['file_path'] = $path;
                
                // Type detection
                $extension = strtolower($file->getClientOriginalExtension());
                $data['resource_type'] = match($extension) {
                    'pdf' => 'pdf',
                    'doc', 'docx', 'xls', 'xlsx' => 'document',
                    'mp4', 'mov', 'avi', 'webm' => 'video',
                    'mp3', 'wav', 'ogg' => 'audio',
                    'jpg', 'jpeg', 'png', 'gif', 'webp' => 'image',
                    default => 'document'
                };

                CurriculumResource::create($data);
            }
        } else {
            // Handle URL only or empty file
            if (empty($baseData['resource_type'])) {
                $baseData['resource_type'] = !empty($baseData['url']) ? 'link' : 'document';
            }
            CurriculumResource::create($baseData);
        }

        return back()->with('success', 'Resources synchronized successfully.');
    }


    public function update(Request $request, CurriculumResource $resource): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'url' => 'nullable|url',
            'subject_id' => 'nullable|exists:subjects,id',
            'grade_level_id' => 'nullable|exists:grade_levels,id',
            'folder_id' => 'nullable|exists:resource_folders,id',
            'resource_type' => 'nullable',
            'is_recommended' => 'nullable|boolean',
            'file' => 'nullable|file|max:20480', // 20MB
        ]);

        $data = $validated;
        unset($data['file']);

        if ($request->hasFile('file')) {
            if ($resource->file_path) {
                Storage::disk('public')->delete($resource->file_path);
            }
            $file = $request->file('file');
            $data['file_path'] = $file->store('curriculum/resources', 'public');
            
            if (empty($data['resource_type'])) {
                $extension = strtolower($file->getClientOriginalExtension());
                $data['resource_type'] = match($extension) {
                    'pdf' => 'pdf',
                    'mp4', 'mov', 'avi' => 'video',
                    'mp3', 'wav' => 'audio',
                    'jpg', 'jpeg', 'png', 'gif' => 'image',
                    default => 'document'
                };
            }
        }

        if ($request->subject_id) {
            $data['resourceable_type'] = Subject::class;
            $data['resourceable_id'] = $request->subject_id;
        }

        $resource->update($data);
        return redirect()->route('curriculum.resources.index')->with('success', 'Resource synchronized successfully.');
    }

    public function destroy(CurriculumResource $resource): RedirectResponse
    {
        if ($resource->file_path) {
            Storage::disk('public')->delete($resource->file_path);
        }

        $resource->delete();

        return back()->with('success', 'Resource deleted successfully.');
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:curriculum_resources,id',
        ]);

        $resources = CurriculumResource::whereIn('id', $request->ids)->get();

        foreach ($resources as $resource) {
            if ($resource->file_path) {
                Storage::disk('public')->delete($resource->file_path);
            }
            $resource->delete();
        }

        return back()->with('success', count($request->ids) . ' resources deleted successfully.');
    }

    public function storeFolder(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject_id' => 'nullable|exists:subjects,id',
            'grade_level_id' => 'nullable|exists:grade_levels,id',
        ]);

        ResourceFolder::create([
            'name' => $request->name,
            'school_id' => auth()->user()->school_id,
            'subject_id' => $request->subject_id,
            'grade_level_id' => $request->grade_level_id,
        ]);

        return back()->with('success', 'Registry folder initialized successfully.');
    }
}
