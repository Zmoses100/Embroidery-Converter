<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e(config('app.name', 'Embroidery Converter')); ?> - Convert Embroidery Files Online</title>
    <meta name="description" content="Convert PES, DST, JEF, VP3 and 30+ embroidery formats online. Fast, secure, professional.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { primary: { 50:'#eef2ff',100:'#e0e7ff',500:'#6366f1',600:'#4f46e5',700:'#4338ca' } },
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
</head>
<body class="font-sans antialiased bg-white">

<!-- Header -->
<header class="fixed top-0 inset-x-0 z-50 bg-white/90 backdrop-blur border-b border-gray-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-8 h-8 text-primary-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/>
            </svg>
            <span class="text-lg font-bold text-gray-900">Embroidery Converter</span>
        </div>
        <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-600">
            <a href="#features" class="hover:text-gray-900">Features</a>
            <a href="#formats" class="hover:text-gray-900">Formats</a>
            <a href="<?php echo e(route('plans.index')); ?>" class="hover:text-gray-900">Pricing</a>
        </nav>
        <div class="flex items-center gap-3">
            <?php if(auth()->guard()->check()): ?>
                <a href="<?php echo e(route('dashboard')); ?>" class="text-sm font-medium text-primary-600 hover:text-primary-700">Dashboard →</a>
            <?php else: ?>
                <a href="<?php echo e(route('login')); ?>" class="text-sm font-medium text-gray-600 hover:text-gray-900">Login</a>
                <a href="<?php echo e(route('register')); ?>" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                    Get Started Free
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Hero -->
<section class="pt-28 pb-20 bg-gradient-to-br from-primary-50 via-white to-indigo-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1 bg-primary-100 text-primary-700 text-xs font-semibold rounded-full mb-6">
            ✨ 30+ embroidery formats supported
        </div>
        <h1 class="text-5xl sm:text-6xl font-extrabold text-gray-900 leading-tight mb-6">
            Convert Embroidery Files<br>
            <span class="text-primary-600">Online & Instantly</span>
        </h1>
        <p class="text-xl text-gray-500 mb-10 max-w-2xl mx-auto">
            Support for PES, DST, JEF, VP3, HUS and 25+ more formats. Professional quality conversion for your embroidery machine.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="<?php echo e(route('register')); ?>"
               class="px-8 py-4 bg-primary-600 text-white text-base font-semibold rounded-xl hover:bg-primary-700 shadow-lg shadow-primary-200 transition-all hover:shadow-primary-300">
                Start Converting Free
            </a>
            <a href="<?php echo e(route('plans.index')); ?>" class="px-8 py-4 bg-white text-gray-700 text-base font-semibold rounded-xl border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-all">
                View Plans
            </a>
        </div>
        <p class="mt-4 text-sm text-gray-400">Free plan · No credit card required</p>
    </div>
</section>

<!-- Supported Formats -->
<section id="formats" class="py-16 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6">
        <h2 class="text-2xl font-bold text-gray-900 text-center mb-8">Supported Formats</h2>
        <div class="flex flex-wrap justify-center gap-3">
            <?php $__currentLoopData = ['PES','DST','JEF','EXP','VP3','HUS','XXX','SEW','VIP','PEC','PCS','SHV','CSV','DAT','DSB','DSZ','FXY','KSM','PCD','PCQ','RGB','STC','STX','TAP','U01','ZHS','ZXY']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fmt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <span class="px-3 py-1.5 bg-primary-50 text-primary-700 text-sm font-bold rounded-lg border border-primary-100"><?php echo e($fmt); ?></span>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>

<!-- Features -->
<section id="features" class="py-16 bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6">
        <h2 class="text-2xl font-bold text-gray-900 text-center mb-12">Everything You Need</h2>
        <div class="grid md:grid-cols-3 gap-6">
            <?php $features = [
                ['icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12', 'title' => 'Drag & Drop Upload', 'desc' => 'Upload multiple files at once with our intuitive drag-and-drop interface.'],
                ['icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4', 'title' => 'Instant Conversion', 'desc' => 'Fast, accurate conversion powered by pyembroidery — the most comprehensive embroidery library.'],
                ['icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'title' => 'Design Preview', 'desc' => 'See stitch counts, thread colors, dimensions, and more before downloading.'],
                ['icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z', 'title' => 'File Library', 'desc' => 'Store, search, and manage all your original and converted files in one place.'],
                ['icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'title' => 'Conversion History', 'desc' => 'Track all your conversions with full status history and re-download anytime.'],
                ['icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 'title' => 'Secure & Private', 'desc' => 'Your files are securely stored. Files auto-deleted after 30 days for your privacy.'],
            ] ?>
            <?php $__currentLoopData = $features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white rounded-xl p-6 border border-gray-100">
                    <div class="w-10 h-10 bg-primary-50 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($f['icon']); ?>"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2"><?php echo e($f['title']); ?></h3>
                    <p class="text-sm text-gray-500"><?php echo e($f['desc']); ?></p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-20 bg-primary-600">
    <div class="max-w-2xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold text-white mb-4">Ready to convert your files?</h2>
        <p class="text-primary-200 mb-8">Start with 5 free conversions per day. No credit card required.</p>
        <a href="<?php echo e(route('register')); ?>"
           class="inline-block px-8 py-4 bg-white text-primary-700 text-base font-bold rounded-xl hover:bg-primary-50 shadow-lg transition-all">
            Create Free Account
        </a>
    </div>
</section>

<!-- Footer -->
<footer class="bg-gray-900 text-gray-400 py-10">
    <div class="max-w-5xl mx-auto px-4 flex flex-col md:flex-row items-center justify-between gap-4 text-sm">
        <p>© <?php echo e(date('Y')); ?> Embroidery Converter. All rights reserved.</p>
        <div class="flex gap-6">
            <a href="<?php echo e(route('plans.index')); ?>" class="hover:text-white">Pricing</a>
            <a href="<?php echo e(route('register')); ?>" class="hover:text-white">Sign Up</a>
            <a href="<?php echo e(route('login')); ?>" class="hover:text-white">Login</a>
        </div>
    </div>
</footer>
</body>
</html>
<?php /**PATH /home/runner/work/Embroidery-Converter/Embroidery-Converter/resources/views/welcome.blade.php ENDPATH**/ ?>