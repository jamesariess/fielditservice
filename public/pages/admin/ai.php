<?php
$page_title = 'AI Settings';
$active_menu = 'admin-ai';
require APP_ROOT . '/includes/layout_header.php';
Auth::requirePermission('ai.manage');
?>
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">AI Configuration</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure the IT Support AI assistant</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h2 class="font-semibold text-gray-900 dark:text-white mb-4">AI Provider</h2>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Provider</label>
                <select class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700">
                    <option value="ollama" selected>Ollama (Local) — Free</option>
                    <option value="openai">OpenAI API (External — Paid)</option>
                    <option value="none">Disabled</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ollama URL</label>
                <input type="text" value="http://localhost:11434" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Model</label>
                <input type="text" value="llama3.2" class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rate Limit (requests/minute/user)</label>
                <input type="number" value="30" class="w-32 px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700">
            </div>
            <div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" checked class="w-4 h-4 text-brand-600 border-gray-300 rounded">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Enable web research for AI</span>
                </label>
            </div>
        </div>
        <button class="mt-4 px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition">Save Settings</button>
    </div>
    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4">
        <div class="flex items-start gap-2">
            <i data-lucide="info" class="w-5 h-5 text-amber-600 mt-0.5"></i>
            <div class="text-sm text-amber-800 dark:text-amber-300">
                <p class="font-medium mb-1">About Local AI (Ollama)</p>
                <p>Local AI runs on your company's hardware. Performance depends on available CPU/GPU/RAM. For better results, use a GPU-capable machine with at least 8GB VRAM. The AI will only use information from your approved knowledge base and publicly available documentation.</p>
            </div>
        </div>
    </div>
</div>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
