<?php
$page_title = 'Knowledge Management';
$active_menu = 'admin-kb';
require APP_ROOT . '/includes/layout_header.php';
Auth::requirePermission('knowledge.manage');
?>
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div><h1 class="text-2xl font-bold text-gray-900 dark:text-white">Knowledge Base Management</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Review, approve, and manage knowledge articles</p></div>
        <button class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition flex items-center gap-2"><i data-lucide="plus" class="w-4 h-4"></i> New Article</button>
    </div>
    <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
        <button class="px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg whitespace-nowrap">All (12)</button>
        <button class="px-4 py-2 bg-yellow-100 text-yellow-700 text-sm font-medium rounded-lg whitespace-nowrap">Pending Review (3)</button>
        <button class="px-4 py-2 bg-green-100 text-green-700 text-sm font-medium rounded-lg whitespace-nowrap">Published (8)</button>
        <button class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg whitespace-nowrap">Drafts (1)</button>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full">
            <thead><tr class="border-b border-gray-200 dark:border-gray-700">
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Article</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hide-mobile">Author</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hide-mobile">Quality</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <?php
                $articles = [
                    ['title' => 'No Display — Desktop Troubleshooting', 'author' => 'Admin', 'status' => 'published', 'quality' => '92%'],
                    ['title' => 'Printer Offline — HP LaserJet', 'author' => 'Maria S.', 'status' => 'published', 'quality' => '88%'],
                    ['title' => 'New WiFi Issue Guide', 'author' => 'Juan D.', 'status' => 'submitted', 'quality' => '—'],
                    ['title' => 'POS Printer Blinking Red', 'author' => 'Ana T.', 'status' => 'submitted', 'quality' => '—'],
                    ['title' => 'Server Room Temperature Guide', 'author' => 'Carlos R.', 'status' => 'under_review', 'quality' => '—'],
                ];
                foreach ($articles as $a): ?>
                    <tr class="table-row">
                        <td class="px-5 py-3"><span class="text-sm font-medium text-gray-900 dark:text-white"><?= $a['title'] ?></span></td>
                        <td class="px-5 py-3 hide-mobile"><span class="text-sm text-gray-500"><?= $a['author'] ?></span></td>
                        <td class="px-5 py-3"><?= status_badge($a['status']) ?></td>
                        <td class="px-5 py-3 hide-mobile"><span class="text-sm text-gray-500"><?= $a['quality'] ?></span></td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <?php if ($a['status'] === 'submitted' || $a['status'] === 'under_review'): ?>
                                    <button class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded hover:bg-green-200">Approve</button>
                                    <button class="px-2 py-1 bg-red-100 text-red-700 text-xs rounded hover:bg-red-200">Reject</button>
                                <?php endif; ?>
                                <button class="p-1.5 text-gray-400 hover:text-brand-600 rounded hover:bg-brand-50 dark:hover:bg-brand-900/20"><i data-lucide="edit-2" class="w-4 h-4"></i></button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
