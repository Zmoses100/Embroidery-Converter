<?php $__env->startSection('title', $plan->exists ? 'Edit Plan' : 'Create Plan'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="<?php echo e(route('admin.plans.index')); ?>" class="text-gray-400 hover:text-gray-600">←</a>
        <h1 class="text-2xl font-bold text-gray-900"><?php echo e($plan->exists ? 'Edit Plan: ' . $plan->name : 'Create Plan'); ?></h1>
    </div>

    <form method="POST" action="<?php echo e($plan->exists ? route('admin.plans.update', $plan) : route('admin.plans.store')); ?>"
          class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">
        <?php echo csrf_field(); ?>
        <?php if($plan->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Name *</label>
                <input type="text" name="name" value="<?php echo e(old('name', $plan->name)); ?>" required
                       class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Slug *</label>
                <input type="text" name="slug" value="<?php echo e(old('slug', $plan->slug)); ?>" required
                       class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                <?php $__errorArgs = ['slug'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" rows="2"
                      class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"><?php echo e(old('description', $plan->description)); ?></textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Monthly Price ($)</label>
                <input type="number" name="price_monthly" step="0.01" min="0" value="<?php echo e(old('price_monthly', $plan->price_monthly ?? 0)); ?>" required
                       class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Yearly Price ($)</label>
                <input type="number" name="price_yearly" step="0.01" min="0" value="<?php echo e(old('price_yearly', $plan->price_yearly ?? 0)); ?>" required
                       class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Stripe Monthly Price ID</label>
                <input type="text" name="stripe_monthly_price_id" value="<?php echo e(old('stripe_monthly_price_id', $plan->stripe_monthly_price_id)); ?>" placeholder="price_..."
                       class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Stripe Yearly Price ID</label>
                <input type="text" name="stripe_yearly_price_id" value="<?php echo e(old('stripe_yearly_price_id', $plan->stripe_yearly_price_id)); ?>" placeholder="price_..."
                       class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Conversions/Day (-1 = unlimited)</label>
                <input type="number" name="conversions_per_day" min="-1" value="<?php echo e(old('conversions_per_day', $plan->conversions_per_day ?? 5)); ?>" required
                       class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Storage Limit (MB)</label>
                <input type="number" name="storage_limit_mb" min="1" value="<?php echo e(old('storage_limit_mb', $plan->storage_limit_mb ?? 100)); ?>" required
                       class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Max File Size (MB)</label>
                <input type="number" name="max_file_size_mb" min="1" value="<?php echo e(old('max_file_size_mb', $plan->max_file_size_mb ?? 10)); ?>" required
                       class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Max Batch Size</label>
                <input type="number" name="max_batch_size" min="1" value="<?php echo e(old('max_batch_size', $plan->max_batch_size ?? 1)); ?>" required
                       class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
        </div>

        <!-- Feature Toggles -->
        <div class="grid grid-cols-2 gap-3">
            <?php $__currentLoopData = [
                ['name' => 'preview_enabled',  'label' => 'Design Preview'],
                ['name' => 'history_enabled',  'label' => 'Conversion History'],
                ['name' => 'api_access',        'label' => 'API Access'],
                ['name' => 'priority_queue',   'label' => 'Priority Queue'],
                ['name' => 'is_active',         'label' => 'Active'],
                ['name' => 'is_featured',       'label' => 'Featured'],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $toggle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="<?php echo e($toggle['name']); ?>" value="1"
                           <?php echo e(old($toggle['name'], $plan->{$toggle['name']}) ? 'checked' : ''); ?>

                           class="h-4 w-4 text-primary-600 border-gray-300 rounded">
                    <span class="text-sm text-gray-700"><?php echo e($toggle['label']); ?></span>
                </label>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                <?php echo e($plan->exists ? 'Update Plan' : 'Create Plan'); ?>

            </button>
            <a href="<?php echo e(route('admin.plans.index')); ?>" class="px-6 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Cancel</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/runner/work/Embroidery-Converter/Embroidery-Converter/resources/views/admin/plans/form.blade.php ENDPATH**/ ?>