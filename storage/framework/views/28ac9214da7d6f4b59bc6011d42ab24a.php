<?php $__env->startSection('title', 'Notifications'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
        <?php if(auth()->user()->unreadNotifications->count()): ?>
            <form method="POST" action="<?php echo e(route('notifications.read-all')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                    Mark all as read
                </button>
            </form>
        <?php endif; ?>
    </div>

    <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="flex items-start gap-4 bg-white rounded-xl shadow-sm border <?php echo e($notification->read_at ? 'border-gray-100' : 'border-primary-200 bg-primary-50/30'); ?> p-5">
            <div class="flex-shrink-0 w-9 h-9 rounded-lg flex items-center justify-center
                <?php echo e($notification->data['type'] === 'conversion_completed' ? 'bg-green-100' :
                   ($notification->data['type'] === 'conversion_failed' ? 'bg-red-100' : 'bg-blue-100')); ?>">
                <?php if($notification->data['type'] === 'conversion_completed'): ?>
                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                <?php elseif($notification->data['type'] === 'conversion_failed'): ?>
                    <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                <?php endif; ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-800"><?php echo e($notification->data['message']); ?></p>
                <?php if(!empty($notification->data['download_url'])): ?>
                    <a href="<?php echo e($notification->data['download_url']); ?>" class="text-xs text-primary-600 hover:text-primary-700 font-medium mt-1 block">Download File</a>
                <?php endif; ?>
                <p class="text-xs text-gray-400 mt-1"><?php echo e($notification->created_at->diffForHumans()); ?></p>
            </div>
            <div class="flex-shrink-0 flex items-center gap-2">
                <?php if(!$notification->read_at): ?>
                    <form method="POST" action="<?php echo e(route('notifications.read', $notification->id)); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="text-xs text-gray-400 hover:text-gray-600">Mark read</button>
                    </form>
                <?php else: ?>
                    <span class="text-xs text-gray-300">Read</span>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="text-center py-16 bg-white rounded-xl border border-gray-100">
            <svg class="mx-auto w-16 h-16 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <p class="text-gray-400">No notifications yet.</p>
        </div>
    <?php endif; ?>

    <div><?php echo e($notifications->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/runner/work/Embroidery-Converter/Embroidery-Converter/resources/views/notifications/index.blade.php ENDPATH**/ ?>