<?php $__env->startSection('title', 'Admin Dashboard'); ?>
<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Admin Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">System overview and analytics</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <?php $adminStats = [
            ['label' => 'Total Users',       'value' => $stats['total_users'],       'sub' => '+' . $stats['new_users_month'] . ' this month', 'color' => 'blue'],
            ['label' => 'Total Conversions', 'value' => $stats['total_conversions'], 'sub' => $stats['conversions_today'] . ' today',          'color' => 'green'],
            ['label' => 'Failed Conversions','value' => $stats['failed_conversions'],'sub' => 'needs attention',                               'color' => 'red'],
            ['label' => 'Active Subscribers','value' => $stats['active_subscribers'],'sub' => $stats['storage_used_gb'] . ' GB stored',        'color' => 'purple'],
        ] ?>
        <?php $__currentLoopData = $adminStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide"><?php echo e($stat['label']); ?></p>
                <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e($stat['value']); ?></p>
                <p class="text-xs text-gray-400 mt-1"><?php echo e($stat['sub']); ?></p>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <!-- Top Formats -->
    <?php if($topFormats->isNotEmpty()): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Most Requested Target Formats</h3>
            <div class="space-y-2">
                <?php $__currentLoopData = $topFormats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fmt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center gap-3">
                        <span class="w-12 text-xs font-bold text-gray-700 uppercase"><?php echo e($fmt->target_format); ?></span>
                        <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-primary-500 rounded-full"
                                 style="width: <?php echo e(($topFormats->max('count') > 0) ? round(($fmt->count / $topFormats->max('count')) * 100) : 0); ?>%"></div>
                        </div>
                        <span class="text-xs text-gray-500 w-10 text-right"><?php echo e($fmt->count); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Recent Users -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Recent Users</h3>
                <a href="<?php echo e(route('admin.users.index')); ?>" class="text-sm text-primary-600 hover:text-primary-700">View all →</a>
            </div>
            <div class="divide-y divide-gray-50">
                <?php $__currentLoopData = $recentUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center gap-3 px-5 py-3">
                        <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center text-xs font-bold text-primary-700">
                            <?php echo e(strtoupper(substr($u->name, 0, 1))); ?>

                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate"><?php echo e($u->name); ?></p>
                            <p class="text-xs text-gray-500 truncate"><?php echo e($u->email); ?></p>
                        </div>
                        <span class="text-xs text-gray-400"><?php echo e($u->created_at->diffForHumans()); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <!-- Recent Conversions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Recent Conversions</h3>
                <a href="<?php echo e(route('admin.conversions.index')); ?>" class="text-sm text-primary-600 hover:text-primary-700">View all →</a>
            </div>
            <div class="divide-y divide-gray-50">
                <?php $__currentLoopData = $recentConversions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center gap-3 px-5 py-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate"><?php echo e($conv->original_filename); ?></p>
                            <p class="text-xs text-gray-500"><?php echo e($conv->user?->email); ?> · <?php echo e(strtoupper($conv->source_format)); ?> → <?php echo e(strtoupper($conv->target_format)); ?></p>
                        </div>
                        <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                            <?php echo e($conv->status === 'completed' ? 'bg-green-100 text-green-700' :
                               ($conv->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700')); ?>">
                            <?php echo e(ucfirst($conv->status)); ?>

                        </span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/runner/work/Embroidery-Converter/Embroidery-Converter/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>