<?php $__env->startSection('title', 'Login'); ?>
<?php $__env->startSection('content'); ?>
<h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Sign in to your account</h2>

<form method="POST" action="<?php echo e(route('login')); ?>" class="space-y-5">
    <?php echo csrf_field(); ?>
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
        <input id="email" type="email" name="email" value="<?php echo e(old('email')); ?>" required autofocus autocomplete="email"
               class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <div class="flex items-center justify-between">
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <a href="<?php echo e(route('password.request')); ?>" class="text-xs text-primary-600 hover:text-primary-500">Forgot password?</a>
        </div>
        <input id="password" type="password" name="password" required autocomplete="current-password"
               class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
    </div>

    <div class="flex items-center">
        <input id="remember" type="checkbox" name="remember" class="h-4 w-4 text-primary-600 border-gray-300 rounded">
        <label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
    </div>

    <button type="submit"
            class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
        Sign in
    </button>
</form>

<p class="mt-6 text-center text-sm text-gray-500">
    Don't have an account?
    <a href="<?php echo e(route('register')); ?>" class="font-medium text-primary-600 hover:text-primary-500">Sign up free</a>
</p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/runner/work/Embroidery-Converter/Embroidery-Converter/resources/views/auth/login.blade.php ENDPATH**/ ?>