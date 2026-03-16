<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\EmbroideryFile;
use App\Services\Embroidery\EmbroideryConverter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class FileController extends Controller
{
    private EmbroideryConverter $converter;

    public function __construct(EmbroideryConverter $converter)
    {
        $this->converter = $converter;
    }

    /**
     * Display file library.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = $user->embroideryFiles()
            ->when($request->search, fn($q, $s) => $q->where('original_name', 'like', "%{$s}%"))
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->when($request->format, fn($q, $f) => $q->where('extension', $f))
            ->latest();

        $files = $query->paginate(20)->withQueryString();

        $formats = $user->embroideryFiles()
            ->selectRaw('extension, count(*) as count')
            ->groupBy('extension')
            ->pluck('count', 'extension')
            ->toArray();

        return view('files.index', compact('files', 'formats'));
    }

    /**
     * Show upload form.
     */
    public function upload()
    {
        $supportedFormats = EmbroideryConverter::readableFormats();

        return view('files.upload', compact('supportedFormats'));
    }

    /**
     * Handle file upload.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $plan = $user->activePlan();

        $maxSizeMb  = $plan ? $plan->max_file_size_mb : 10;
        $maxSizeKb  = $maxSizeMb * 1024;
        $allowedExt = implode(',', EmbroideryConverter::readableFormats());

        $request->validate([
            'files'   => 'required|array|max:20',
            'files.*' => "required|file|max:{$maxSizeKb}|mimes:{$allowedExt}",
        ], [
            'files.*.mimes' => "Only supported embroidery formats are allowed: {$allowedExt}",
            'files.*.max'   => "Each file must be smaller than {$maxSizeMb} MB.",
        ]);

        $uploaded = [];
        $errors   = [];

        foreach ($request->file('files') as $file) {
            $sizeBytes = $file->getSize();

            // Check storage limit
            if (!$user->hasStorageFor($sizeBytes)) {
                $errors[] = "Storage limit reached. Cannot upload {$file->getClientOriginalName()}.";
                continue;
            }

            $ext        = strtolower($file->getClientOriginalExtension());
            $storedName = Str::uuid() . '.' . $ext;
            $path       = $file->storeAs("embroidery/{$user->id}/originals", $storedName, 'local');

            // Extract metadata
            $metadata = [];
            $absolutePath = Storage::disk('local')->path($path);
            try {
                $metadata = $this->converter->extractMetadata($absolutePath);
            } catch (\Exception $e) {
                // Non-fatal - continue without metadata
            }

            $embFile = EmbroideryFile::create([
                'user_id'       => $user->id,
                'original_name' => $file->getClientOriginalName(),
                'stored_name'   => $storedName,
                'disk'          => 'local',
                'path'          => $path,
                'extension'     => $ext,
                'size_bytes'    => $sizeBytes,
                'type'          => 'original',
                'stitch_count'  => $metadata['stitch_count'] ?? null,
                'color_count'   => $metadata['color_count'] ?? null,
                'thread_colors' => $metadata['thread_colors'] ?? null,
                'width_mm'      => $metadata['width_mm'] ?? null,
                'height_mm'     => $metadata['height_mm'] ?? null,
                'metadata'      => $metadata ?: null,
            ]);

            // Generate preview (async-friendly, won't block upload)
            if (env('PYEMBROIDERY_AVAILABLE')) {
                try {
                    $previewDir = Storage::disk('local')->path("embroidery/{$user->id}/previews");
                    if (!is_dir($previewDir)) mkdir($previewDir, 0755, true);
                    $previewPath = $this->converter->generatePreview($absolutePath, $previewDir);
                    if ($previewPath) {
                        $relPath = "embroidery/{$user->id}/previews/" . basename($previewPath);
                        $embFile->update(['preview_path' => $relPath, 'preview_generated' => true]);
                    }
                } catch (\Exception $e) {
                    // Non-fatal
                }
            }

            AuditLog::log('file.uploaded', $user->id, EmbroideryFile::class, $embFile->id, [], [
                'filename' => $embFile->original_name,
                'size'     => $embFile->size_bytes,
            ]);

            $uploaded[] = $embFile;
        }

        if (empty($uploaded) && !empty($errors)) {
            return back()->withErrors(['files' => $errors]);
        }

        $message = count($uploaded) . ' file(s) uploaded successfully.';
        if (!empty($errors)) {
            $message .= ' ' . count($errors) . ' file(s) failed.';
        }

        return redirect()->route('files.index')
            ->with('success', $message)
            ->with('warnings', $errors);
    }

    /**
     * Show file details.
     */
    public function show(EmbroideryFile $file)
    {
        $this->authorize('view', $file);

        $conversions = $file->conversions()->with('outputFile')->latest()->get();

        return view('files.show', compact('file', 'conversions'));
    }

    /**
     * Download a file.
     */
    public function download(EmbroideryFile $file)
    {
        $this->authorize('view', $file);

        if (!Storage::disk($file->disk)->exists($file->path)) {
            abort(404, 'File not found.');
        }

        AuditLog::log('file.downloaded', auth()->id(), EmbroideryFile::class, $file->id);

        return Storage::disk($file->disk)->download($file->path, $file->original_name);
    }

    /**
     * Download multiple files as a ZIP.
     */
    public function downloadZip(Request $request)
    {
        $request->validate(['file_ids' => 'required|array|max:50']);

        $user  = $request->user();
        $files = EmbroideryFile::whereIn('id', $request->file_ids)
            ->where('user_id', $user->id)
            ->get();

        if ($files->isEmpty()) {
            abort(404, 'No files found.');
        }

        $zipPath = sys_get_temp_dir() . '/embroidery_' . Str::uuid() . '.zip';
        $zip     = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            abort(500, 'Could not create ZIP file.');
        }

        foreach ($files as $file) {
            $absPath = Storage::disk($file->disk)->path($file->path);
            if (file_exists($absPath)) {
                $zip->addFile($absPath, $file->original_name);
            }
        }

        $zip->close();

        return response()->download($zipPath, 'embroidery_files.zip')->deleteFileAfterSend(true);
    }

    /**
     * Delete a file.
     */
    public function destroy(EmbroideryFile $file)
    {
        $this->authorize('delete', $file);

        $file->deletePhysicalFile();
        $file->delete();

        AuditLog::log('file.deleted', auth()->id(), EmbroideryFile::class, $file->id, [
            'filename' => $file->original_name,
        ]);

        return redirect()->route('files.index')->with('success', 'File deleted successfully.');
    }
}
