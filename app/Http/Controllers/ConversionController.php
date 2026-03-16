<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessConversion;
use App\Models\AuditLog;
use App\Models\Conversion;
use App\Models\ConversionUsage;
use App\Models\EmbroideryFile;
use App\Services\Embroidery\EmbroideryConverter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ConversionController extends Controller
{
    private EmbroideryConverter $converter;

    public function __construct(EmbroideryConverter $converter)
    {
        $this->converter = $converter;
    }

    /**
     * Show conversion form.
     */
    public function create(Request $request)
    {
        $user   = $request->user();
        $plan   = $user->activePlan();
        $files  = $user->embroideryFiles()->originals()->latest()->get();
        $targetFormats = EmbroideryConverter::writableFormats();
        $todayCount    = $user->todayConversionCount();
        $dailyLimit    = $plan ? $plan->conversions_per_day : 5;

        // Pre-selected file
        $selectedFile = null;
        if ($request->file_id) {
            $selectedFile = EmbroideryFile::find($request->file_id);
            if ($selectedFile && $selectedFile->user_id !== $user->id) {
                $selectedFile = null;
            }
        }

        return view('convert.create', compact(
            'files', 'targetFormats', 'plan', 'todayCount', 'dailyLimit', 'selectedFile'
        ));
    }

    /**
     * Start a conversion.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Check daily limit
        if (!$user->canConvert()) {
            return back()->withErrors(['limit' => 'You have reached your daily conversion limit. Please upgrade your plan.']);
        }

        $request->validate([
            'source_file_id' => 'required|integer|exists:embroidery_files,id',
            'target_format'  => 'required|string|in:' . implode(',', EmbroideryConverter::writableFormats()),
        ]);

        $sourceFile = EmbroideryFile::findOrFail($request->source_file_id);
        $this->authorize('view', $sourceFile);

        $targetFormat = strtolower($request->target_format);

        // Create conversion record
        $conversion = Conversion::create([
            'user_id'           => $user->id,
            'source_file_id'    => $sourceFile->id,
            'source_format'     => $sourceFile->extension,
            'target_format'     => $targetFormat,
            'original_filename' => $sourceFile->original_name,
            'status'            => 'pending',
        ]);

        // Dispatch job to queue
        ProcessConversion::dispatch($conversion, $user)->onQueue(
            $user->activePlan()?->priority_queue ? 'priority' : 'conversions'
        );

        AuditLog::log('conversion.started', $user->id, Conversion::class, $conversion->id);

        return redirect()->route('conversions.show', $conversion->id)
            ->with('success', 'Conversion started! You will be notified when it is complete.');
    }

    /**
     * Batch conversion.
     */
    public function batch(Request $request)
    {
        $user = $request->user();
        $plan = $user->activePlan();

        $request->validate([
            'file_ids'      => 'required|array|max:' . ($plan?->max_batch_size ?? 1),
            'file_ids.*'    => 'integer|exists:embroidery_files,id',
            'target_format' => 'required|string|in:' . implode(',', EmbroideryConverter::writableFormats()),
        ]);

        $targetFormat = strtolower($request->target_format);
        $dispatched   = 0;

        foreach ($request->file_ids as $fileId) {
            if (!$user->canConvert()) break;

            $sourceFile = EmbroideryFile::find($fileId);
            if (!$sourceFile || $sourceFile->user_id !== $user->id) continue;

            $conversion = Conversion::create([
                'user_id'           => $user->id,
                'source_file_id'    => $sourceFile->id,
                'source_format'     => $sourceFile->extension,
                'target_format'     => $targetFormat,
                'original_filename' => $sourceFile->original_name,
                'status'            => 'pending',
            ]);

            ProcessConversion::dispatch($conversion, $user)->onQueue('conversions');
            $dispatched++;
        }

        if ($dispatched === 0) {
            return back()->withErrors(['batch' => 'No conversions were started. Check your daily limit.']);
        }

        return redirect()->route('conversions.index')
            ->with('success', "{$dispatched} conversion(s) queued.");
    }

    /**
     * Show conversion history.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $conversions = $user->conversions()
            ->with(['sourceFile', 'outputFile'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->format, fn($q, $f) => $q->where('target_format', $f))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('convert.history', compact('conversions'));
    }

    /**
     * Show a specific conversion.
     */
    public function show(Conversion $conversion)
    {
        $this->authorize('view', $conversion);

        return view('convert.show', compact('conversion'));
    }

    /**
     * Poll conversion status (AJAX).
     */
    public function status(Conversion $conversion)
    {
        $this->authorize('view', $conversion);

        return response()->json([
            'status'     => $conversion->status,
            'output_id'  => $conversion->output_file_id,
            'error'      => $conversion->error_message,
            'warnings'   => $conversion->warnings,
            'download'   => $conversion->outputFile ? route('files.download', $conversion->output_file_id) : null,
        ]);
    }
}
