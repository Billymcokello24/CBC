<?php

namespace App\Http\Controllers;

use App\Models\ExportProcess;
use App\Jobs\GeneratePdfExportJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExportController extends Controller
{
    public function start(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:staff,students,parents',
            'filters' => 'nullable|array'
        ]);

        $process = ExportProcess::create([
            'user_id' => auth()->id(),
            'school_id' => auth()->user()->school_id,
            'type' => $request->type,
            'status' => 'pending',
            'filters' => $request->filters ?? []
        ]);

        GeneratePdfExportJob::dispatch($process->id);

        return response()->json([
            'id' => $process->id,
            'message' => 'Report generation started.'
        ]);
    }

    public function status(ExportProcess $process)
    {
        abort_unless($process->user_id === auth()->id(), 403);

        return response()->json($process);
    }

    public function cancel(ExportProcess $process)
    {
        abort_unless($process->user_id === auth()->id(), 403);
        
        if ($process->status === 'pending' || $process->status === 'processing') {
            $process->update([
                'status' => 'cancelled',
                'error_message' => 'Process cancelled by user.'
            ]);
            return response()->json(['message' => 'Process cancelled.']);
        }

        return response()->json(['message' => 'Process cannot be cancelled.'], 400);
    }

    public function startDelete(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:staff,students,parents',
            'ids' => 'nullable|array',
            'all_matching' => 'nullable|boolean',
            'filters' => 'nullable|array'
        ]);

        $process = ExportProcess::create([
            'user_id' => auth()->id(),
            'school_id' => auth()->user()->school_id,
            'type' => 'delete_' . $request->type,
            'status' => 'pending',
            'filters' => [
                'ids' => $request->ids,
                'all' => $request->all_matching,
                'filters' => $request->filters
            ]
        ]);

        \App\Jobs\BulkDeleteJob::dispatch($process->id);

        return response()->json([
            'id' => $process->id,
            'message' => 'Bulk deletion has started in the background.'
        ]);
    }

    public function download(ExportProcess $process)
    {
        abort_unless($process->user_id === auth()->id(), 403);
        abort_unless($process->status === 'completed', 404);
        abort_unless(Storage::exists($process->file_path), 404);

        return Storage::download($process->file_path, $process->file_name);
    }
}
