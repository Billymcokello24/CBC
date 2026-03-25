<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\GeneralNotificationMail;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class BroadcastController extends Controller
{
    /**
     * Show the broadcast creation form.
     */
    public function index()
    {
        return Inertia::render('super-admin/broadcasts/Index', [
            'roles' => Role::all(),
        ]);
    }

    /**
     * Send the broadcast to selected recipients.
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'greeting' => 'required|string|max:255',
            'message' => 'required|string',
            'action_text' => 'nullable|string|max:50',
            'action_url' => 'nullable|url',
            'recipients' => 'required|in:all_admins,all_users',
        ]);

        $query = User::query()->where('status', 'active');

        if ($validated['recipients'] === 'all_admins') {
            $query->role('admin'); // Assuming 'admin' is the role for school admins
        }

        $users = $query->get();
        $lines = explode("\n", $validated['message']);

        foreach ($users as $user) {
            Mail::to($user->email)->send(new GeneralNotificationMail(
                $validated['subject'],
                str_replace('{name}', $user->name, $validated['greeting']),
                $lines,
                $validated['action_text'] ?? null,
                $validated['action_url'] ?? null
            ));
        }

        return back()->with('success', "Broadcast dispatched to " . $users->count() . " recipients.");
    }
}
