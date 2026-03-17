<?php $__env->startSection('title', 'My Files'); ?>
<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">My File Library</h1>
            <p class="text-sm text-gray-500 mt-1">Manage your embroidery files</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?php echo e(route('files.upload')); ?>"
               class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Upload
            </a>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="flex flex-wrap gap-3 bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search files..."
               class="flex-1 min-w-48 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
        <select name="type" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
            <option value="">All Types</option>
            <option value="original" <?php echo e(request('type') === 'original' ? 'selected' : ''); ?>>Original</option>
            <option value="converted" <?php echo e(request('type') === 'converted' ? 'selected' : ''); ?>>Converted</option>
        </select>
        <select name="format" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
            <option value="">All Formats</option>
            <?php $__currentLoopData = $formats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ext => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($ext); ?>" <?php echo e(request('format') === $ext ? 'selected' : ''); ?>>
                    <?php echo e(strtoupper($ext)); ?> (<?php echo e($count); ?>)
                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-900 transition-colors">
            Filter
        </button>
        <?php if(request()->hasAny(['search', 'type', 'format'])): ?>
            <a href="<?php echo e(route('files.index')); ?>" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Clear</a>
        <?php endif; ?>
    </form>

    <!-- File Grid -->
    <?php $__empty_1 = true; $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div x-data="{ selected: false }" class="hidden"><!-- for batch select --></div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <?php endif; ?>

    <?php if($files->isEmpty()): ?>
        <div class="text-center py-16 bg-white rounded-xl border border-gray-100">
            <svg class="mx-auto w-16 h-16 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-500">No files found</h3>
            <p class="text-sm text-gray-400 mt-1">Upload your first embroidery file to get started.</p>
            <a href="<?php echo e(route('files.upload')); ?>"
               class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                Upload File
            </a>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">File</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Format</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Size</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Type</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Uploaded</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0 w-9 h-9 bg-primary-50 rounded-lg flex items-center justify-center">
                                        <span class="text-xs font-bold text-primary-600 uppercase"><?php echo e($file->extension); ?></span>
                                    </div>
                                    <div class="min-w-0">
                                        <a href="<?php echo e(route('files.show', $file)); ?>"
                                           class="font-medium text-gray-900 hover:text-primary-600 truncate block max-w-xs">
                                            <?php echo e($file->original_name); ?>

                                        </a>
                                        <?php if($file->stitch_count): ?>
                                            <span class="text-xs text-gray-400"><?php echo e(number_format($file->stitch_count)); ?> stitches</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 hidden md:table-cell">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 uppercase">
                                    <?php echo e($file->extension); ?>

                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-500 hidden lg:table-cell"><?php echo e($file->size_human); ?></td>
                            <td class="px-5 py-3 hidden lg:table-cell">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    <?php echo e($file->type === 'original' ? 'bg-green-50 text-green-700' : 'bg-orange-50 text-orange-700'); ?>">
                                    <?php echo e(ucfirst($file->type)); ?>

                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-500 text-xs hidden md:table-cell"><?php echo e($file->created_at->diffForHumans()); ?></td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="<?php echo e(route('conversions.create', ['file_id' => $file->id])); ?>"
                                       class="text-xs text-primary-600 hover:text-primary-700 font-medium">Convert</a>
                                    <a href="<?php echo e(route('files.download', $file)); ?>"
                                       class="text-xs text-gray-500 hover:text-gray-700">Download</a>
                                    <form method="POST" action="<?php echo e(route('files.destroy', $file)); ?>"
                                          onsubmit="return confirm('Delete this file?')">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4"><?php echo e($files->links()); ?></div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/runner/work/Embroidery-Converter/Embroidery-Converter/resources/views/files/index.blade.php ENDPATH**/ ?>