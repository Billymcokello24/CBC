<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\School;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use App\Mail\ParentNotificationMail;
use Illuminate\Support\Str;

class ParentsController extends Controller
{
    /**
     * Display a listing of parents/guardians.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search', '');
        $status = $request->input('status', 'all');
        $perPage = $request->input('per_page', 20);
        
        $parents = Guardian::query()
            ->where('school_id', auth()->user()->school_id)
            ->with([
                'students:id,first_name,last_name,admission_number', 
                'user' => fn($q) => $q->withoutGlobalScopes()->select('id', 'email')
            ])
            ->when($search, fn($q) => $q->search($search))
            ->when($status !== 'all', function($q) use ($status) {
                return $q->where('is_active', $status === 'active');
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('parents/Index', [
            'parents' => $parents,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'per_page' => (int) $perPage,
            ],
            'stats' => [
                'total' => Guardian::where('school_id', auth()->user()->school_id)->count(),
                'active' => Guardian::where('school_id', auth()->user()->school_id)->where('is_active', true)->count(),
                'new_this_month' => Guardian::where('school_id', auth()->user()->school_id)->whereMonth('created_at', now()->month)->count(),
            ],
            'can_export' => true,
            'statusOptions' => [
                ['value' => 'all', 'label' => 'All Statuses'],
                ['value' => 'active', 'label' => 'Active Accounts'],
                ['value' => 'inactive', 'label' => 'Locked Accounts'],
            ]
        ]);
    }

    public function exportPdf(Request $request)
    {
        $search = trim((string) ($request->input('search') ?? ''));

        $items = Guardian::query()
            ->with(['students:id,first_name,last_name,current_class_id', 'students.currentClass:id,name,grade_level_id', 'students.currentClass.gradeLevel'])
            ->when($search !== '', fn ($q) => $q->search($search))
            ->orderBy('id', 'desc')
            ->get();

        $school = School::find(auth()->user()->school_id);
        $themeColor = DB::table('school_settings')
            ->where('school_id', $school?->id)
            ->where('key', 'pdf_theme_color')
            ->value('value') ?? '#1e40af';

        $pdf = Pdf::loadView('pdf.parents', [
            'items' => $items,
            'school' => $school,
            'themeColor' => $themeColor
        ]);

        return $pdf->download('parent_registry_' . date('Y_m_d_His') . '.pdf');
    }

    /**
     * Show the form for creating a new parent.
     */
    public function create(): Response
    {
        return Inertia::render('parents/Create', [
            'students' => Student::active()->select('id', 'first_name', 'last_name', 'admission_number')->get(),
            'counties' => config('settings.counties', []),
        ]);
    }

    /**
     * Store a newly created parent in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:guardians,email|unique:users,email',
            'phone' => 'required|string|max:20',
            'id_number' => 'nullable|string|max:20',
            'gender' => 'required|in:male,female,other',
            'relationship_type' => 'required|string',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
        ]);

        try {
            DB::beginTransaction();

            $randomPassword = \Illuminate\Support\Str::random(12);

            // 1. Create User Account
            $user = User::create([
                'name' => "{$validated['first_name']} {$validated['last_name']}",
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($randomPassword),
                'status' => 'active',
                'school_id' => auth()->user()->school_id,
                'force_password_change' => true,
            ]);

            // Assign Parent Role if it exists
            $user->assignRole('parent');

            // 2. Create Guardian Profile
            $guardian = Guardian::create(array_merge(
                $request->except(['student_ids', 'password', 'password_confirmation']),
                ['user_id' => $user->id, 'school_id' => auth()->user()->school_id, 'is_active' => true]
            ));

            // 3. Link to Students
            foreach ($validated['student_ids'] as $studentId) {
                $guardian->students()->attach($studentId, [
                    'school_id' => auth()->user()->school_id,
                    'relationship' => $validated['relationship_type'],
                    'is_primary_contact' => true,
                ]);
            }

            // 4. Send Welcome Email
            $students = $guardian->students()->get();
            $token = Password::createToken($user);
            $resetUrl = url(route('password.reset', [
                'token' => $token,
                'email' => $user->email,
            ], false));

            Mail::to($user->email)->send(new ParentNotificationMail($guardian, $students, $randomPassword, $resetUrl));

            DB::commit();

            return redirect()->route('parents.index')
                ->with('success', 'Parent account created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating parent: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified parent.
     */
    public function show(Guardian $parent): Response
    {
        $parent->load([
            'students:id,first_name,last_name,admission_number', 
            'user' => fn($q) => $q->withoutGlobalScopes()->select('id', 'email', 'name')
        ]);
        return Inertia::render('parents/Show', [
            'parent' => $parent
        ]);
    }

    /**
     * Show the form for editing the specified parent.
     */
    public function edit(Guardian $parent): Response
    {
        $parent->load([
            'students:id', 
            'user' => fn($q) => $q->withoutGlobalScopes()->select('id', 'email')
        ]);
        return Inertia::render('parents/Edit', [
            'parent' => $parent,
            'students' => Student::active()->select('id', 'first_name', 'last_name', 'admission_number')->get(),
            'selectedStudentIds' => $parent->students->pluck('id'),
        ]);
    }

    /**
     * Update the specified parent in storage.
     */
    public function update(Request $request, Guardian $parent): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:guardians,email,' . $parent->id . '|unique:users,email,' . $parent->user_id,
            'phone' => 'required|string|max:20',
            'id_number' => 'nullable|string|max:20',
            'gender' => 'required|in:male,female,other',
            'relationship_type' => 'required|string',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
        ]);

        try {
            DB::beginTransaction();

            $parent->load('user');
            $parentUser = $parent->user;

            // 1. Update User Account
            if ($parentUser) {
                $parentUser->update([
                    'name' => trim("{$validated['first_name']} " . ($validated['middle_name'] ? "{$validated['middle_name']} " : "") . $validated['last_name']),
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                ]);
            }

            // 2. Update Guardian Profile
            $parent->update(collect($validated)->except(['student_ids'])->toArray());

            // 3. Sync Students
            $syncData = [];
            foreach ($validated['student_ids'] as $studentId) {
                $syncData[$studentId] = [
                    'school_id' => auth()->user()->school_id,
                    'relationship' => $validated['relationship_type'],
                    'is_primary_contact' => true,
                ];
            }
            $parent->students()->sync($syncData);

            // 4. Send Update Email with credentials and reset link
            $randomPassword = Str::random(12);
            if ($parentUser) {
                // Check if email was changed before we potentially update it again with password
                $emailChanged = $parentUser->wasChanged('email');
                
                $parentUser->update(['password' => Hash::make($randomPassword)]);
                
                $students = $parent->students()->get();
                $token = Password::createToken($parentUser);
                $resetUrl = url(route('password.reset', [
                    'token' => $token,
                    'email' => $parentUser->email,
                ], false));

                $note = $emailChanged ? "Your account email has been updated to this address ({$parentUser->email})." : null;

                Mail::to($parentUser->email)->send(new ParentNotificationMail($parent, $students, $randomPassword, $resetUrl, $note));
            }

            DB::commit();

            return redirect()->route('parents.index')
                ->with('success', 'Parent account updated and notification sent successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating parent: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified parent from storage.
     */
    public function destroy(Guardian $parent): RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            // Delete pivot relationships first (Laravel does this with detach usually, but delete() on model won't if not set to cascade in DB)
            $parent->students()->detach();
            
            // Delete user account
            $user = $parent->user;
            $parent->delete();
            $user?->delete();

            DB::commit();

            return redirect()->route('parents.index')
                ->with('success', 'Parent account deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error deleting parent: ' . $e->getMessage());
        }
    }
}
