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

class ParentsController extends Controller
{
    /**
     * Display a listing of parents/guardians.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search', '');
        
        $parents = Guardian::query()
            ->with(['students:id,first_name,last_name,admission_number', 'user:id,email'])
            ->when($search, fn($q) => $q->search($search))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('parents/Index', [
            'parents' => $parents,
            'filters' => $request->only(['search']),
            'stats' => [
                'total' => Guardian::count(),
                'active' => Guardian::where('is_active', true)->count(),
                'new_this_month' => Guardian::whereMonth('created_at', now()->month)->count(),
            ]
        ]);
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
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\UserCreatedMail($user, $randomPassword));

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
        $parent->load(['students:id,first_name,last_name,admission_number', 'user:id,email,name']);
        return Inertia::render('parents/Show', [
            'parent' => $parent
        ]);
    }

    /**
     * Show the form for editing the specified parent.
     */
    public function edit(Guardian $parent): Response
    {
        $parent->load(['students:id', 'user:id,email']);
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

            // 1. Update User Account
            $parent->user->update([
                'name' => "{$validated['first_name']} {$validated['last_name']}",
                'email' => $validated['email'],
                'phone' => $validated['phone'],
            ]);

            // 2. Update Guardian Profile
            $parent->update($request->except(['student_ids', 'password', 'password_confirmation']));

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

            DB::commit();

            return redirect()->route('parents.index')
                ->with('success', 'Parent account updated successfully.');

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
