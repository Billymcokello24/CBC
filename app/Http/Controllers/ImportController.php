<?php
// /home/billy/PhpstormProjects/CBC/app/Http/Controllers/ImportController.php

namespace App\Http\Controllers;

use App\Models\ImportProcess;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function cancel(ImportProcess $process)
    {
        // Only allow the user who started the import or an admin to cancel it
        if ($process->user_id !== auth()->id() && !auth()->user()->hasAnyRole(['super_admin', 'school_admin', 'admin'])) {
            return back()->with('error', 'Unauthorized to cancel this process.');
        }

        if (in_array($process->status, ['pending', 'processing'])) {
            $process->update(['status' => 'canceled']);
            return back()->with('success', 'Import process has been marked for cancellation.');
        }

        return back()->with('error', 'Only pending or processing imports can be canceled.');
    }

    public function getActiveImports()
    {
        return ImportProcess::where('user_id', auth()->id())
            ->pending()
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
