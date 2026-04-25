<?php

namespace App\Services\Curriculum;

use App\Models\Curriculum\AssignmentSubmission;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AssignmentService
{
    /**
     * Combine multiple images from a submission into a single PDF.
     *
     * @param AssignmentSubmission $submission
     * @return string Path to the generated PDF
     */
    public function generateSubmissionPdf(AssignmentSubmission $submission): string
    {
        // Get the view content
        $images = $this->getSubmissionImages($submission);
        $pdf = Pdf::loadView('pdf.assignment_submission', [
            'submission' => $submission->load(['student', 'assignment.subject']),
            'annotations' => $submission->private_notes ? json_decode($submission->private_notes, true) : [],
            'images' => $images,
        ]);

        $pdfName = 'assignments/submissions/submission_' . $submission->id . '.pdf';
        Storage::disk('public')->put($pdfName, $pdf->output());

        return $pdfName;
    }

    /**
     * Generate a physical marked copy and store it in the vault.
     */
    public function generateAndStoreMarkedPdf(AssignmentSubmission $submission, array $markedImages = []): string
    {
        $images = !empty($markedImages) ? $markedImages : $this->getSubmissionImages($submission);
        
        $pdf = Pdf::loadView('pdf.assignment_submission', [
            'submission' => $submission->load(['student', 'assignment.subject', 'assignment.classroom']),
            'annotations' => empty($markedImages) ? ($submission->private_notes ? json_decode($submission->private_notes, true) : []) : [],
            'images' => $images,
            'flattened' => !empty($markedImages),
        ]);

        // Organize by Subject -> Class -> Student
        $subject = Str::slug($submission->assignment->subject->name ?? 'Uncategorized');
        $class = Str::slug($submission->assignment->classroom->name ?? 'No_Class');
        $student = Str::slug($submission->student->full_name);
        
        $vaultPath = "vault/{$subject}/{$class}/{$student}_marked_{$submission->id}.pdf";
        
        Storage::disk('public')->put($vaultPath, $pdf->output());
        
        return $vaultPath;
    }

    private function getSubmissionImages(AssignmentSubmission $submission): array
    {
        $attachments = $submission->attachments()->where('file_type', 'like', 'image/%')->get();
        $images = [];
        foreach ($attachments as $attachment) {
            if (Storage::disk('public')->exists($attachment->file_path)) {
                $path = Storage::disk('public')->path($attachment->file_path);
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $images[] = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }
        return $images;
    }

    /**
     * Save digital annotations onto a submission.
     * Handle layering of red marker and comments.
     */
    public function saveAnnotations(AssignmentSubmission $submission, array $annotationData)
    {
        // Store as JSON for now, can be flattened later if needed
        $submission->update([
            'private_notes' => json_encode($annotationData)
        ]);
        
        return true;
    }
}
