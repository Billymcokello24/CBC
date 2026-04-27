<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class LandingController extends Controller
{
    public function modules()
    {
        return Inertia::render('landing/Modules');
    }

    public function about()
    {
        return Inertia::render('landing/About');
    }

    public function contact()
    {
        return Inertia::render('landing/Contact');
    }

    public function bookDemo(Request $request)
    {
        $validated = $request->validate([
            'school_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'message' => 'nullable|string',
        ]);

        try {
            // Queue the email to be sent in the background
            Mail::to('billyochiengokello@gmail.com')->queue(new \App\Mail\DemoRequestMail($validated));
        } catch (\Exception $e) {
            // Log error but continue to give user positive feedback if the database/queues are handled
            \Illuminate\Support\Facades\Log::error('Mail failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Thank you! We have received your request and will contact you shortly to schedule a demo.');
    }
}
