<?php $__env->startSection('title', 'Settings'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-2xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Application Settings</h1>

    <form method="POST" action="<?php echo e(route('admin.settings.update')); ?>" class="space-y-6">
        <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>

        <?php $__currentLoopData = $settings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group => $groupSettings): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-semibold text-gray-900 capitalize mb-4"><?php echo e($group); ?></h3>
                <div class="space-y-4">
                    <?php $__currentLoopData = $groupSettings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $setting): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <?php echo e(ucwords(str_replace(['_', 'app', 'max', 'mb', 'sec'], [' ', 'App', 'Max', 'MB', 'Seconds'], $setting->key))); ?>

                            </label>
                            <?php if($setting->type === 'boolean'): ?>
                                <select name="<?php echo e($setting->key); ?>"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="1" <?php echo e($setting->value === '1' ? 'selected' : ''); ?>>Enabled</option>
                                    <option value="0" <?php echo e($setting->value === '0' ? 'selected' : ''); ?>>Disabled</option>
                                </select>
                            <?php else: ?>
                                <input type="<?php echo e($setting->type === 'integer' ? 'number' : 'text'); ?>"
                                       name="<?php echo e($setting->key); ?>"
                                       value="<?php echo e($setting->value); ?>"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
            Save Settings
        </button>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/runner/work/Embroidery-Converter/Embroidery-Converter/resources/views/admin/settings/index.blade.php ENDPATH**/ ?>