<?php $__env->startSection('title', 'Conversion #' . $conversion->id); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="<?php echo e(route('conversions.index')); ?>" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Conversion #<?php echo e($conversion->id); ?></h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5"
         x-data="conversionStatus('<?php echo e($conversion->id); ?>', '<?php echo e($conversion->status); ?>')"
         x-init="init()">

        <!-- Status -->
        <div class="flex items-center gap-4">
            <div x-show="status === 'pending' || status === 'processing'"
                 class="flex items-center gap-3 text-blue-700 bg-blue-50 px-4 py-2 rounded-lg">
                <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span class="font-medium" x-text="status === 'processing' ? 'Converting...' : 'Queued...'"></span>
            </div>

            <div x-show="status === 'completed'" x-cloak
                 class="flex items-center gap-3 text-green-700 bg-green-50 px-4 py-2 rounded-lg">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="font-medium">Conversion complete!</span>
            </div>

            <div x-show="status === 'failed'" x-cloak
                 class="flex items-center gap-3 text-red-700 bg-red-50 px-4 py-2 rounded-lg">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span class="font-medium">Conversion failed</span>
            </div>
        </div>

        <!-- Details -->
        <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
            <div>
                <dt class="text-gray-500">File</dt>
                <dd class="font-medium text-gray-900"><?php echo e($conversion->original_filename); ?></dd>
            </div>
            <div>
                <dt class="text-gray-500">Conversion</dt>
                <dd class="font-medium text-gray-900 uppercase"><?php echo e($conversion->source_format); ?> → <?php echo e($conversion->target_format); ?></dd>
            </div>
            <div>
                <dt class="text-gray-500">Started</dt>
                <dd class="text-gray-900"><?php echo e($conversion->created_at->format('M d, Y H:i:s')); ?></dd>
            </div>
            <?php if($conversion->processing_time_ms): ?>
            <div>
                <dt class="text-gray-500">Processing Time</dt>
                <dd class="text-gray-900"><?php echo e(number_format($conversion->processing_time_ms / 1000, 2)); ?>s</dd>
            </div>
            <?php endif; ?>
        </dl>

        <!-- Warnings -->
        <?php if($conversion->warnings): ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <p class="text-sm font-medium text-yellow-800 mb-1">⚠ Conversion Warnings</p>
                <?php $__currentLoopData = $conversion->warnings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <p class="text-sm text-yellow-700">• <?php echo e($warning); ?></p>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <!-- Error -->
        <?php if($conversion->error_message): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <p class="text-sm font-medium text-red-800 mb-1">Error Details</p>
                <p class="text-sm text-red-700 font-mono"><?php echo e($conversion->error_message); ?></p>
            </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="flex flex-wrap gap-3 pt-2" x-show="status === 'completed'">
            <a :href="downloadUrl" x-show="downloadUrl" x-cloak
               class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download Converted File
            </a>
        </div>

        <?php if($conversion->status === 'completed' && $conversion->outputFile): ?>
            <div class="flex flex-wrap gap-3 pt-2">
                <a href="<?php echo e(route('files.download', $conversion->outputFile)); ?>"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download <?php echo e(strtoupper($conversion->target_format)); ?> File
                </a>
                <a href="<?php echo e(route('conversions.create')); ?>"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Convert Another
                </a>
            </div>
        <?php elseif($conversion->status === 'failed'): ?>
            <div class="flex flex-wrap gap-3 pt-2">
                <a href="<?php echo e(route('conversions.create', ['file_id' => $conversion->source_file_id])); ?>"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                    Try Again
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
function conversionStatus(conversionId, initialStatus) {
    return {
        status: initialStatus,
        downloadUrl: null,
        pollingInterval: null,

        init() {
            if (this.status === 'pending' || this.status === 'processing') {
                this.poll();
            }
        },

        poll() {
            this.pollingInterval = setInterval(async () => {
                try {
                    const res = await fetch(`/convert/${conversionId}/status`);
                    const data = await res.json();

                    this.status = data.status;

                    if (data.download) {
                        this.downloadUrl = data.download;
                    }

                    if (data.status === 'completed' || data.status === 'failed') {
                        clearInterval(this.pollingInterval);
                        if (data.status === 'completed') {
                            // Reload to show full download section
                            setTimeout(() => window.location.reload(), 1000);
                        }
                    }
                } catch (e) {
                    console.error('Polling error:', e);
                }
            }, 3000);
        }
    }
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/runner/work/Embroidery-Converter/Embroidery-Converter/resources/views/convert/show.blade.php ENDPATH**/ ?>