<?php $__env->startSection('title', 'All Conversions (Admin)'); ?>
<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">All Conversions</h1>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">File</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">User</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Format</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php $__currentLoopData = $conversions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <p class="font-medium text-gray-900 truncate max-w-xs"><?php echo e($conv->original_filename); ?></p>
                        </td>
                        <td class="px-5 py-3 text-gray-500 text-xs hidden md:table-cell"><?php echo e($conv->user?->email); ?></td>
                        <td class="px-5 py-3 hidden md:table-cell">
                            <span class="font-medium text-xs uppercase text-gray-700"><?php echo e($conv->source_format); ?> → <?php echo e($conv->target_format); ?></span>
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                <?php echo e($conv->status === 'completed' ? 'bg-green-100 text-green-700' :
                                   ($conv->status === 'failed' ? 'bg-red-100 text-red-700' :
                                   ($conv->status === 'processing' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700'))); ?>">
                                <?php echo e(ucfirst($conv->status)); ?>

                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-500 hidden lg:table-cell"><?php echo e($conv->created_at->format('M d, Y H:i')); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <div><?php echo e($conversions->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/runner/work/Embroidery-Converter/Embroidery-Converter/resources/views/admin/conversions/index.blade.php ENDPATH**/ ?>