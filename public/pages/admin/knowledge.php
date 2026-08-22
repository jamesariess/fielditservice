<?php
$page_title = 'Knowledge Management';
$active_menu = 'admin-kb';
require APP_ROOT . '/includes/layout_header.php';
Auth::requirePermission('knowledge.manage');

// Fetch articles from database
$articles = Database::fetchAll(
    "SELECT ka.id, ka.title, ka.category, ka.status, ka.quality_score, ka.use_count,
            u.full_name as author_name
     FROM knowledge_articles ka
     LEFT JOIN users u ON ka.author_id = u.id
     WHERE ka.deleted_at IS NULL
     ORDER BY ka.created_at DESC"
);

$countAll = count($articles);
$countPending = 0;
$countPublished = 0;
$countDraft = 0;
foreach ($articles as $a) {
    if (in_array($a['status'], ['submitted', 'under_review'])) $countPending++;
    elseif ($a['status'] === 'published') $countPublished++;
    elseif ($a['status'] === 'draft') $countDraft++;
}
?>
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Knowledge Base Management</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Review, approve, and manage knowledge articles</p>
        </div>
        <button class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i> New Article
        </button>
    </div>

    <!-- Filters -->
    <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
        <button onclick="kbFilterAdmin('all')" class="kb-filter-btn px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg whitespace-nowrap" data-filter="all">All (<?= $countAll ?>)</button>
        <button onclick="kbFilterAdmin('submitted')" class="kb-filter-btn px-4 py-2 bg-yellow-100 text-yellow-700 text-sm font-medium rounded-lg whitespace-nowrap" data-filter="submitted">Pending Review (<?= $countPending ?>)</button>
        <button onclick="kbFilterAdmin('published')" class="kb-filter-btn px-4 py-2 bg-green-100 text-green-700 text-sm font-medium rounded-lg whitespace-nowrap" data-filter="published">Published (<?= $countPublished ?>)</button>
        <button onclick="kbFilterAdmin('draft')" class="kb-filter-btn px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg whitespace-nowrap" data-filter="draft">Drafts (<?= $countDraft ?>)</button>
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
                <?php foreach ($articles as $a): ?>
                    <tr id="kb-row-<?= $a['id'] ?>" class="kb-row table-row" data-status="<?= e($a['status']) ?>">
                        <td class="px-5 py-3">
                            <a href="/knowledge/view?id=<?= $a['id'] ?>" class="text-sm font-medium text-gray-900 dark:text-white hover:text-brand-600"><?= e($a['title']) ?></a>
                        </td>
                        <td class="px-5 py-3 hide-mobile">
                            <span class="text-sm text-gray-500"><?= e($a['author_name'] ?? 'Unknown') ?></span>
                        </td>
                        <td class="px-5 py-3 kb-status">
                            <?= status_badge($a['status']) ?>
                        </td>
                        <td class="px-5 py-3 hide-mobile">
                            <span class="text-sm text-gray-500"><?= $a['quality_score'] > 0 ? number_format($a['quality_score'], 0) . '%' : '—' ?></span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-1 kb-actions">
                                <?php if (in_array($a['status'], ['submitted', 'under_review', 'draft'])): ?>
                                    <button onclick="kbApprove(<?= $a['id'] ?>)" class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded hover:bg-green-200 font-medium">Approve</button>
                                    <button onclick="kbReject(<?= $a['id'] ?>)" class="px-2 py-1 bg-red-100 text-red-700 text-xs rounded hover:bg-red-200 font-medium">Reject</button>
                                <?php endif; ?>
                                <a href="/knowledge/view?id=<?= $a['id'] ?>" class="p-1.5 text-gray-400 hover:text-brand-600 rounded hover:bg-brand-50 dark:hover:bg-brand-900/20"><i data-lucide="pencil" class="w-4 h-4"></i></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function kbFilterAdmin(status) {
    document.querySelectorAll('.kb-row').forEach(function(row) {
        if (status === 'all' || row.dataset.status === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    document.querySelectorAll('.kb-filter-btn').forEach(function(btn) {
        btn.classList.remove('bg-brand-600', 'text-white');
        btn.classList.add('bg-gray-100', 'text-gray-700');
        if (btn.dataset.filter === status) {
            btn.classList.remove('bg-gray-100', 'text-gray-700');
            btn.classList.add('bg-brand-600', 'text-white');
        }
    });
}
</script>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
