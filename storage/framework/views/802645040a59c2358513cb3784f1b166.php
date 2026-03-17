<?php $__env->startSection('title', 'Upload Files'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Upload Embroidery Files</h1>
        <p class="text-sm text-gray-500 mt-1">
            Supported formats: <?php echo e(implode(', ', array_map('strtoupper', $supportedFormats))); ?>

        </p>
    </div>

    <form method="POST" action="<?php echo e(route('files.store')); ?>" enctype="multipart/form-data" id="upload-form">
        <?php echo csrf_field(); ?>

        <!-- Drag & Drop Zone -->
        <div x-data="{
                files: [],
                dragging: false,
                handleDrop(e) {
                    this.dragging = false;
                    const dropped = Array.from(e.dataTransfer.files);
                    this.addFiles(dropped);
                },
                addFiles(newFiles) {
                    newFiles.forEach(f => {
                        if (this.files.length < 20) this.files.push(f);
                    });
                    this.updateInput();
                },
                removeFile(idx) {
                    this.files.splice(idx, 1);
                    this.updateInput();
                },
                updateInput() {
                    const dt = new DataTransfer();
                    this.files.forEach(f => dt.items.add(f));
                    document.getElementById('file-input').files = dt.files;
                },
                formatSize(bytes) {
                    if (bytes < 1024) return bytes + ' B';
                    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
                    return (bytes / 1048576).toFixed(2) + ' MB';
                }
            }"
             @dragover.prevent="dragging = true"
             @dragleave.prevent="dragging = false"
             @drop.prevent="handleDrop($event)"
             class="relative">

            <div :class="dragging ? 'border-primary-500 bg-primary-50' : 'border-gray-300 bg-white hover:border-primary-400'"
                 class="border-2 border-dashed rounded-xl p-10 text-center transition-colors cursor-pointer"
                 @click="$refs.fileInput.click()">
                <svg class="mx-auto w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <p class="text-base font-medium text-gray-700">Drop files here or <span class="text-primary-600">browse</span></p>
                <p class="text-sm text-gray-400 mt-1">
                    Max 20 files · Supported: <?php echo e(implode(', ', array_map('strtoupper', array_slice($supportedFormats, 0, 8)))); ?>...
                </p>
                <input id="file-input" type="file" name="files[]" multiple x-ref="fileInput"
                       accept="<?php echo e(implode(',', array_map(fn($f) => '.' . $f, $supportedFormats))); ?>"
                       @change="addFiles(Array.from($event.target.files))"
                       class="hidden">
            </div>

            <!-- File list -->
            <template x-if="files.length > 0">
                <div class="mt-4 bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
                    <template x-for="(file, idx) in files" :key="idx">
                        <div class="flex items-center gap-3 px-4 py-3">
                            <div class="flex-shrink-0 w-9 h-9 bg-primary-50 rounded-lg flex items-center justify-center">
                                <span class="text-xs font-bold text-primary-600 uppercase" x-text="file.name.split('.').pop()"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate" x-text="file.name"></p>
                                <p class="text-xs text-gray-500" x-text="formatSize(file.size)"></p>
                            </div>
                            <button type="button" @click="removeFile(idx)"
                                    class="text-gray-400 hover:text-red-500 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <div class="flex items-center justify-between mt-4">
            <a href="<?php echo e(route('files.index')); ?>" class="text-sm text-gray-500 hover:text-gray-700">← Back to library</a>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Upload Files
            </button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/runner/work/Embroidery-Converter/Embroidery-Converter/resources/views/files/upload.blade.php ENDPATH**/ ?>