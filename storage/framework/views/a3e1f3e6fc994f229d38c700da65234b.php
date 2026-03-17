<?php $__env->startSection('title', 'Convert File'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Convert Embroidery File</h1>
        <p class="text-sm text-gray-500 mt-1">Select a file and choose your target format.</p>
    </div>

    <!-- Usage indicator -->
    <?php if($plan): ?>
        <div class="flex items-center justify-between bg-white border border-gray-100 rounded-xl px-5 py-3 shadow-sm text-sm">
            <span class="text-gray-600">
                Daily conversions:
                <strong class="text-gray-900"><?php echo e($todayCount); ?></strong>
                <?php if($dailyLimit > 0): ?> / <?php echo e($dailyLimit); ?> <?php else: ?> <?php endif; ?>
            </span>
            <?php if($dailyLimit > 0 && $todayCount >= $dailyLimit): ?>
                <a href="<?php echo e(route('plans.index')); ?>" class="text-primary-600 font-medium hover:text-primary-700">Upgrade to convert more →</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if($dailyLimit > 0 && $todayCount >= $dailyLimit): ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5 text-center">
            <p class="text-yellow-800 font-medium">Daily conversion limit reached</p>
            <p class="text-yellow-700 text-sm mt-1">You've used all <?php echo e($dailyLimit); ?> conversions for today.</p>
            <a href="<?php echo e(route('plans.index')); ?>"
               class="mt-3 inline-block px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-lg hover:bg-yellow-700 transition-colors">
                Upgrade Plan
            </a>
        </div>
    <?php else: ?>
        <form method="POST" action="<?php echo e(route('conversions.store')); ?>" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">
            <?php echo csrf_field(); ?>

            <!-- Source file selector -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Source File</label>
                <?php if($files->isEmpty()): ?>
                    <div class="border-2 border-dashed border-gray-200 rounded-lg p-8 text-center">
                        <p class="text-gray-500 text-sm">No files uploaded yet.</p>
                        <a href="<?php echo e(route('files.upload')); ?>"
                           class="mt-2 inline-block text-primary-600 hover:text-primary-700 text-sm font-medium">Upload a file first</a>
                    </div>
                <?php else: ?>
                    <select name="source_file_id" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">-- Select a file --</option>
                        <?php $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($file->id); ?>" <?php echo e(($selectedFile?->id === $file->id || old('source_file_id') == $file->id) ? 'selected' : ''); ?>>
                                <?php echo e($file->original_name); ?> (<?php echo e(strtoupper($file->extension)); ?>, <?php echo e($file->size_human); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['source_file_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                <?php endif; ?>
            </div>

            <!-- Target format -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Target Format</label>
                <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                    <?php $__currentLoopData = $targetFormats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fmt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex flex-col items-center gap-1 cursor-pointer">
                            <input type="radio" name="target_format" value="<?php echo e($fmt); ?>"
                                   <?php echo e(old('target_format') === $fmt ? 'checked' : ''); ?>

                                   class="sr-only peer">
                            <div class="w-full py-2 border-2 rounded-lg text-center text-xs font-bold uppercase transition-all
                                        peer-checked:border-primary-500 peer-checked:bg-primary-50 peer-checked:text-primary-700
                                        border-gray-200 text-gray-600 hover:border-gray-300">
                                <?php echo e($fmt); ?>

                            </div>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php $__errorArgs = ['target_format'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    Start Conversion
                </button>
            </div>
        </form>

        <!-- Batch conversion -->
        <?php if($plan && $plan->max_batch_size > 1): ?>
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <p class="text-sm text-blue-800">
                    <strong>Batch conversion available!</strong>
                    Your <?php echo e($plan->name); ?> plan supports converting up to <?php echo e($plan->max_batch_size); ?> files at once.
                    <a href="<?php echo e(route('files.index')); ?>" class="underline">Go to your library</a> to select multiple files.
                </p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/runner/work/Embroidery-Converter/Embroidery-Converter/resources/views/convert/create.blade.php ENDPATH**/ ?>