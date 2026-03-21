<?php

namespace App\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use App\Models\Communication\Announcement;
use App\Models\Communication\Message;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CommunicationController extends Controller
{
    public function index(): Response
    {
        $schoolId = auth()->user()->school_id;

        $stats = [
            'active_announcements' => Announcement::where('school_id', $schoolId)->where('is_published', true)->where('expire_at', '>', now())->count(),
            'unread_messages' => Message::where('conversation_id', auth()->id())->whereNull('deleted_at')->count(), // Simplified
            'total_sent_sms' => 0, // Placeholder
        ];

        $recentAnnouncements = Announcement::where('school_id', $schoolId)
            ->where('is_published', true)
            ->orderBy('publish_at', 'desc')
            ->limit(3)
            ->get();

        return Inertia::render('communication/Index', [
            'stats' => $stats,
            'recentAnnouncements' => $recentAnnouncements,
        ]);
    }

    public function announcements(): Response
    {
        $schoolId = auth()->user()->school_id;
        $announcements = Announcement::where('school_id', $schoolId)
            ->with(['creator'])
            ->orderBy('publish_at', 'desc')
            ->paginate(15);

        return Inertia::render('communication/Announcements', [
            'announcements' => $announcements,
        ]);
    }

    public function messages(): Response
    {
        $messages = Message::where(function($q) {
                $q->where('sender_id', auth()->id())
                  ->orWhere('conversation_id', auth()->id());
            })
            ->with(['sender'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('communication/Messages', [
            'messages' => $messages,
        ]);
    }
}
