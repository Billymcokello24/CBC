<?php

namespace App\Http\Controllers;

use App\Mail\DemoBookingMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class LandingController extends Controller
{
    public function about()
    {
        return Inertia::render('landing/About');
    }

    public function modules()
    {
        return Inertia::render('landing/Modules');
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

        // Send Email
        try {
            Mail::to('info@doitrixtech.co.ke')->send(new DemoBookingMail($validated));
            return back()->with('success', 'Your demo request has been sent! We will contact you shortly.');
        } catch (\Exception $e) {
            return back()->with('error', 'Sorry, we couldn\'t send your request. Please try again later.');
        }
    }
}
