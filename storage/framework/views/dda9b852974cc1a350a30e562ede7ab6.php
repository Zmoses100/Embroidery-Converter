<?php $__env->startSection('title', 'Manage Users'); ?>
<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Users</h1>
    </div>

    <form method="GET" class="flex flex-wrap gap-3 bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search by name or email..."
               class="flex-1 min-w-48 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
        <select name="filter" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
            <option value="">All Users</option>
            <option value="admin" <?php echo e(request('filter') === 'admin' ? 'selected' : ''); ?>>Admins</option>
            <option value="deleted" <?php echo e(request('filter') === 'deleted' ? 'selected' : ''); ?>>Deleted</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-900 transition-colors">Filter</button>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">User</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Role</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Files</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Conversions</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Joined</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="hover:bg-gray-50 <?php echo e($u->trashed() ? 'opacity-50' : ''); ?>">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center text-xs font-bold text-primary-700">
                                    <?php echo e(strtoupper(substr($u->name, 0, 1))); ?>

                                </div>
                                <div>
                                    <p class="font-medium text-gray-900"><?php echo e($u->name); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo e($u->email); ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 hidden md:table-cell">
                            <?php if($u->is_admin): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">Admin</span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">User</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-3 text-gray-500 hidden lg:table-cell"><?php echo e($u->embroidery_files_count); ?></td>
                        <td class="px-5 py-3 text-gray-500 hidden lg:table-cell"><?php echo e($u->conversions_count); ?></td>
                        <td class="px-5 py-3 text-xs text-gray-500 hidden md:table-cell"><?php echo e($u->created_at->format('M d, Y')); ?></td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <?php if(!$u->trashed()): ?>
                                    <form method="POST" action="<?php echo e(route('admin.users.toggle-admin', $u)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="text-xs <?php echo e($u->is_admin ? 'text-red-500 hover:text-red-700' : 'text-primary-600 hover:text-primary-700'); ?> font-medium">
                                            <?php echo e($u->is_admin ? 'Remove Admin' : 'Make Admin'); ?>

                                        </button>
                                    </form>
                                    <form method="POST" action="<?php echo e(route('admin.users.destroy', $u)); ?>"
                                          onsubmit="return confirm('Delete user?')">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700">Delete</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="<?php echo e(route('admin.users.restore', $u->id)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="text-xs text-green-600 hover:text-green-700 font-medium">Restore</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <div><?php echo e($users->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/runner/work/Embroidery-Converter/Embroidery-Converter/resources/views/admin/users/index.blade.php ENDPATH**/ ?>