<?php $__env->startSection('title', 'Plans'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto space-y-8">
    <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900">Choose Your Plan</h1>
        <p class="text-gray-500 mt-2">Simple, transparent pricing. Start free today.</p>
    </div>

    <div class="grid md:grid-cols-<?php echo e($plans->count()); ?> gap-6 items-stretch">
        <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="relative bg-white rounded-2xl shadow-sm border <?php echo e($plan->is_featured ? 'border-primary-400 ring-2 ring-primary-400' : 'border-gray-200'); ?> p-7 flex flex-col">
                <?php if($plan->is_featured): ?>
                    <div class="absolute -top-3.5 left-1/2 -translate-x-1/2">
                        <span class="inline-flex items-center px-3 py-1 bg-primary-600 text-white text-xs font-semibold rounded-full">
                            Most Popular
                        </span>
                    </div>
                <?php endif; ?>

                <div class="mb-6">
                    <h3 class="text-xl font-bold text-gray-900"><?php echo e($plan->name); ?></h3>
                    <p class="text-sm text-gray-500 mt-1"><?php echo e($plan->description); ?></p>
                </div>

                <div class="mb-6">
                    <?php if($plan->price_monthly == 0): ?>
                        <div class="text-4xl font-extrabold text-gray-900">Free</div>
                        <p class="text-sm text-gray-400 mt-1">Forever</p>
                    <?php else: ?>
                        <div class="text-4xl font-extrabold text-gray-900">$<?php echo e($plan->price_monthly); ?></div>
                        <p class="text-sm text-gray-400 mt-1">per month · or $<?php echo e($plan->price_yearly); ?>/yr (save <?php echo e(round((1 - ($plan->price_yearly / ($plan->price_monthly * 12))) * 100)); ?>%)</p>
                    <?php endif; ?>
                </div>

                <!-- Features -->
                <ul class="space-y-2 mb-8 flex-1">
                    <?php $__currentLoopData = ($plan->features ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="flex items-center gap-2 text-sm text-gray-700">
                            <svg class="w-4 h-4 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <?php echo e($feature); ?>

                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>

                <!-- CTA -->
                <?php if(auth()->guard()->check()): ?>
                    <?php if($plan->price_monthly == 0): ?>
                        <?php $activePlan = auth()->user()->activePlan(); ?>
                        <?php if(!$activePlan || $activePlan->id === $plan->id): ?>
                            <button disabled class="w-full py-3 bg-gray-100 text-gray-500 text-sm font-medium rounded-xl cursor-not-allowed">
                                Current Plan
                            </button>
                        <?php else: ?>
                            <a href="<?php echo e(route('subscription.cancel')); ?>"
                               class="block w-full text-center py-3 bg-gray-100 text-gray-700 text-sm font-medium rounded-xl hover:bg-gray-200 transition-colors">
                                Downgrade to Free
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <form method="POST" action="<?php echo e(route('subscription.checkout', $plan)); ?>">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="interval" value="monthly">
                            <button type="submit"
                                    class="w-full py-3 <?php echo e($plan->is_featured ? 'bg-primary-600 text-white hover:bg-primary-700' : 'bg-gray-900 text-white hover:bg-gray-800'); ?> text-sm font-medium rounded-xl transition-colors">
                                Get <?php echo e($plan->name); ?>

                            </button>
                        </form>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?php echo e(route('register')); ?>"
                       class="block w-full text-center py-3 <?php echo e($plan->is_featured ? 'bg-primary-600 text-white hover:bg-primary-700' : 'bg-gray-900 text-white hover:bg-gray-800'); ?> text-sm font-medium rounded-xl transition-colors">
                        Get Started
                    </a>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="text-center text-sm text-gray-400">
        <p>All plans include secure file handling and 30+ format support.</p>
        <p class="mt-1">Questions? Email us at <a href="mailto:support@example.com" class="text-primary-600 hover:underline">support@example.com</a></p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/runner/work/Embroidery-Converter/Embroidery-Converter/resources/views/plans/index.blade.php ENDPATH**/ ?>