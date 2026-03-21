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

        return Inertia::render('settings/SchoolProfile', [
            'school' => $school,
            'settings' => $settings,
        ]);
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
