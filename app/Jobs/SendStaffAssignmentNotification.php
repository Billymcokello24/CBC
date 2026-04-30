<?php

namespace App\Jobs;

use App\Mail\StaffAssignmentMail;
use App\Models\Teacher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendStaffAssignmentNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $teacher;
    public $title;
    public $messageBody;
    public $actionUrl;
    public $actionText;

    /**
     * Create a new job instance.
     */
    public function __construct(Teacher $teacher, $title, $messageBody, $actionUrl = null, $actionText = 'View Dashboard')
    {
        $this->teacher = $teacher->withoutRelations();
        $this->title = $title;
        $this->messageBody = $messageBody;
        $this->actionUrl = $actionUrl;
        $this->actionText = $actionText;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Refresh the teacher to get current relationships like user
        $teacher = Teacher::with('user')->find($this->teacher->id);
        
        if (!$teacher) {
            return;
        }

        $email = $teacher->email ?? $teacher->user?->email;

        if ($email) {
            Mail::to($email)->send(new StaffAssignmentMail(
                $teacher,
                $this->title,
                $this->messageBody,
                $this->actionUrl,
                $this->actionText
            ));
        }
    }
}
