<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Page header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Welcome back, <?php echo e(auth()->user()->name); ?>! 👋</h1>
            <p class="text-sm text-gray-500 mt-1">Here's an overview of your embroidery conversions.</p>
        </div>
        <a href="<?php echo e(route('files.upload')); ?>"
           class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Upload Files
        </a>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <?php
            $statCards = [
                ['label' => 'Total Files',        'value' => $stats['total_files'],        'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z', 'color' => 'text-blue-600 bg-blue-100'],
                ['label' => 'Total Conversions',  'value' => $stats['total_conversions'],   'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',                   'color' => 'text-green-600 bg-green-100'],
                ['label' => 'Today\'s Conversions','value' => $stats['today_conversions'],  'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',                           'color' => 'text-primary-600 bg-primary-100'],
                ['label' => 'Storage Used',        'value' => $stats['storage_used_mb'] . ' MB','icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4', 'color' => 'text-purple-600 bg-purple-100'],
            ];
        ?>

        <?php $__currentLoopData = $statCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide"><?php echo e($card['label']); ?></p>
                        <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e($card['value']); ?></p>
                    </div>
                    <div class="p-2.5 rounded-lg <?php echo e($card['color']); ?>">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($card['icon']); ?>"/>
                        </svg>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <!-- Plan usage banner -->
    <?php if($plan): ?>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="font-semibold text-gray-900"><?php echo e($plan->name); ?> Plan</h3>
                    <p class="text-xs text-gray-500">Daily conversion usage</p>
                </div>
                <a href="<?php echo e(route('plans.index')); ?>" class="text-sm text-primary-600 hover:text-primary-700 font-medium">Upgrade →</a>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <?php
                        $dailyLimit = $plan->conversions_per_day;
                        $used       = $stats['today_conversions'];
                        $pct        = $dailyLimit > 0 ? min(100, round(($used / $dailyLimit) * 100)) : 0;
                    ?>
                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-primary-500 rounded-full transition-all"
                             style="width: <?php echo e($dailyLimit === -1 ? 0 : $pct); ?>%"></div>
                    </div>
                </div>
                <span class="text-sm text-gray-600 shrink-0">
                    <?php if($dailyLimit === -1): ?>
                        Unlimited
                    <?php else: ?>
                        <?php echo e($used); ?> / <?php echo e($dailyLimit); ?>

                    <?php endif; ?>
                </span>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Recent Conversions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Recent Conversions</h3>
                <a href="<?php echo e(route('conversions.index')); ?>" class="text-sm text-primary-600 hover:text-primary-700">View all →</a>
            </div>
            <div class="divide-y divide-gray-50">
                <?php $__empty_1 = true; $__currentLoopData = $recentConversions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="flex items-center gap-3 px-5 py-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate"><?php echo e($conv->original_filename); ?></p>
                            <p class="text-xs text-gray-500"><?php echo e(strtoupper($conv->source_format)); ?> → <?php echo e(strtoupper($conv->target_format)); ?></p>
                        </div>
                        <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                            <?php echo e($conv->status === 'completed' ? 'bg-green-100 text-green-700' :
                               ($conv->status === 'failed' ? 'bg-red-100 text-red-700' :
                               ($conv->status === 'processing' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700'))); ?>">
                            <?php echo e(ucfirst($conv->status)); ?>

                        </span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="px-5 py-8 text-center text-sm text-gray-400">
                        No conversions yet.
                        <a href="<?php echo e(route('conversions.create')); ?>" class="text-primary-600 hover:underline">Start converting!</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Files -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Recent Uploads</h3>
                <a href="<?php echo e(route('files.index')); ?>" class="text-sm text-primary-600 hover:text-primary-700">View all →</a>
            </div>
            <div class="divide-y divide-gray-50">
                <?php $__empty_1 = true; $__currentLoopData = $recentFiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="flex items-center gap-3 px-5 py-3">
                        <div class="flex-shrink-0 w-9 h-9 bg-primary-50 rounded-lg flex items-center justify-center">
                            <span class="text-xs font-bold text-primary-600 uppercase"><?php echo e($file->extension); ?></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate"><?php echo e($file->original_name); ?></p>
                            <p class="text-xs text-gray-500"><?php echo e($file->size_human); ?> · <?php echo e($file->created_at->diffForHumans()); ?></p>
                        </div>
                        <a href="<?php echo e(route('conversions.create', ['file_id' => $file->id])); ?>"
                           class="shrink-0 text-xs text-primary-600 hover:text-primary-700 font-medium">Convert</a>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="px-5 py-8 text-center text-sm text-gray-400">
                        No files uploaded yet.
                        <a href="<?php echo e(route('files.upload')); ?>" class="text-primary-600 hover:underline">Upload now!</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <?php $quickActions = [
            ['href' => route('files.upload'), 'icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12', 'label' => 'Upload Files', 'color' => 'primary'],
            ['href' => route('conversions.create'), 'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4', 'label' => 'Convert File', 'color' => 'green'],
            ['href' => route('files.index'), 'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z', 'label' => 'My Library', 'color' => 'blue'],
            ['href' => route('plans.index'), 'icon' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z', 'label' => 'Upgrade Plan', 'color' => 'purple'],
        ] ?>

        <?php $__currentLoopData = $quickActions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e($action['href']); ?>"
               class="flex flex-col items-center gap-2 p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:border-primary-300 hover:shadow-md transition-all group">
                <div class="p-2.5 bg-<?php echo e($action['color']); ?>-50 rounded-lg group-hover:bg-<?php echo e($action['color']); ?>-100 transition-colors">
                    <svg class="w-5 h-5 text-<?php echo e($action['color']); ?>-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($action['icon']); ?>"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-700 text-center"><?php echo e($action['label']); ?></span>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/runner/work/Embroidery-Converter/Embroidery-Converter/resources/views/dashboard/index.blade.php ENDPATH**/ ?>