<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\School;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of all users across all tenants.
     */
    public function index(Request $request)
    {
        $query = User::with(['school', 'roles'])
            ->when($request->search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($request->school_id, function ($q, $schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            });

        return Inertia::render('super-admin/users/Index', [
            'users' => $query->latest()->paginate(20)->withQueryString(),
            'schools' => School::select('id', 'name')->get(),
            'filters' => $request->only(['search', 'school_id', 'status']),
        ]);
    }

    /**
     * Update the specified user's status.
     */
    public function toggleStatus(User $user)
    {
        if ($user->hasRole('super_admin') && auth()->id() === $user->id) {
            return back()->with('error', 'You cannot deactivate your own super admin account.');
        }

        $user->update([
            'status' => $user->status === 'active' ? 'inactive' : 'active'
        ]);

        return back()->with('success', "User status updated to {$user->status}.");
    }

    /**
     * Reset a user's password.
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
            'force_password_change' => true,
        ]);

        return back()->with('success', "Password reset successfully for {$user->name}.");
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        if ($user->hasRole('super_admin')) {
            return back()->with('error', 'Super Admin accounts cannot be deleted through this interface.');
        }

        $user->delete();

        return back()->with('success', 'User account deleted successfully.');
    }
}
