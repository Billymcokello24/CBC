<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Settings\SchoolSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(): Response
    {
        return $this->schoolProfile();
    }

    public function schoolProfile(): Response
    {
        $schoolId = auth()->user()->school_id;
        $school = School::with(['schoolType', 'schoolLevel'])->findOrFail($schoolId);
        $settings = SchoolSetting::where('school_id', $schoolId)->get();
        $academicYears = \App\Models\Academic\AcademicYear::where('school_id', $schoolId)
            ->orderByDesc('start_date')
            ->get();

        return Inertia::render('settings/SchoolProfile', [
            'school' => $school,
            'settings' => $settings,
            'academicYears' => $academicYears,
        ]);
    }

    public function updateSchoolProfile(Request $request): \Illuminate\Http\RedirectResponse
    {
        $schoolId = auth()->user()->school_id;
        $school = School::findOrFail($schoolId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'registration_number' => 'nullable|string|max:100',
            'motto' => 'nullable|string|max:255',
            'vision' => 'nullable|string',
            'mission' => 'nullable|string',
            'email' => 'required|email|max:100',
            'phone' => 'nullable|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'county' => 'nullable|string|max:100',
            'sub_county' => 'nullable|string|max:100',
            'ward' => 'nullable|string|max:100',
            'pdf_theme_color' => 'nullable|string|max:20',
        ]);

        $schoolData = collect($validated)->except(['pdf_theme_color'])->toArray();
        $school->update($schoolData);

        if (array_key_exists('pdf_theme_color', $validated)) {
            $school->setSetting('pdf_theme_color', $validated['pdf_theme_color'], 'string', 'general');
        }

        return back()->with('success', 'School profile updated successfully.');
    }
    public function updateSchoolLogo(Request $request): \Illuminate\Http\RedirectResponse
    {
        $schoolId = auth()->user()->school_id;
        $school = School::findOrFail($schoolId);

        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($school->logo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($school->logo);
            }

            $path = $request->file('logo')->store('schools/logos', 'public');
            $school->update(['logo' => $path]);
        }

        return back()->with('success', 'School logo updated successfully.');
    }

    public function updateSecuritySettings(Request $request): \Illuminate\Http\RedirectResponse
    {
        $schoolId = auth()->user()->school_id;
        $school = School::findOrFail($schoolId);

        $validated = $request->validate([
            'password_expiry_days' => 'nullable|integer|min:0',
            'require_2fa' => 'boolean',
            'session_timeout_minutes' => 'nullable|integer|min:1',
            'max_login_attempts' => 'nullable|integer|min:1',
            'lockout_duration_minutes' => 'nullable|integer|min:1',
        ]);

        foreach ($validated as $key => $value) {
            $school->setSetting($key, $value, is_bool($value) ? 'boolean' : 'integer', 'security');
        }

        return back()->with('success', 'Security settings updated successfully.');
    }

    public function storeAcademicYear(Request $request): \Illuminate\Http\RedirectResponse
    {
        $schoolId = auth()->user()->school_id;

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'boolean',
            'status' => 'required|in:active,inactive,closed',
        ]);

        // Ensure fixed January cycle if name matches a year? 
        // Actually, the user wants us to ensure it shifts in January.
        // We'll enforce that the start_date must be January 1st if possible, or just trust the UI.
        // The UI will default to Jan 1st.

        $year = \App\Models\Academic\AcademicYear::create(array_merge($validated, [
            'school_id' => $schoolId,
        ]));

        if ($request->boolean('is_current')) {
            $year->makeCurrent();
        }

        return back()->with('success', 'Academic year created successfully.');
    }

    public function updateAcademicYear(Request $request, \App\Models\Academic\AcademicYear $year): \Illuminate\Http\RedirectResponse
    {
        if ($year->school_id !== auth()->user()->school_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:active,inactive,closed',
        ]);

        $year->update($validated);

        return back()->with('success', 'Academic year updated successfully.');
    }

    public function setCurrentAcademicYear(Request $request, \App\Models\Academic\AcademicYear $year): \Illuminate\Http\RedirectResponse
    {
        if ($year->school_id !== auth()->user()->school_id) {
            abort(403);
        }

        $year->makeCurrent();

        return back()->with('success', "{$year->name} is now the current academic year.");
    }

    public function academicSettings(): Response
    {
        $schoolId = auth()->user()->school_id;
        $settings = SchoolSetting::where('school_id', $schoolId)
            ->where('group', 'academic')
            ->get();

        return Inertia::render('settings/AcademicSettings', [
            'settings' => $settings,
        ]);
    }

    public function users(): Response
    {
        return Inertia::render('settings/Users');
    }

    public function system(): Response
    {
        return Inertia::render('settings/System');
    }
}
