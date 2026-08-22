<?php
/**
 * Documentation - Submit New Solution
 */
$page_title = 'Document Solution';
$active_menu = 'documentation';
require APP_ROOT . '/includes/layout_header.php';

?>

<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Document New Solution</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Submit a troubleshooting solution for admin review and potential publication</p>
        <div class="mt-2 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-xs text-blue-700 dark:text-blue-400">
            <strong>Note:</strong> Submitted documentation will go through admin review before being published to the knowledge base.
        </div>
    </div>
    
    <form id="doc-form" onsubmit="docSubmit(event)" class="space-y-6">
        <!-- Basic Info -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Issue Information</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Issue Title *</label>
                    <input type="text" required placeholder="e.g., No Display — Dell OptiPlex 7090" 
                           class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 focus:ring-2 focus:ring-brand-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category *</label>
                        <select required class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 focus:ring-2 focus:ring-brand-500">
                            <option value="">Select category</option>
                            <option>Hardware</option><option>Software</option><option>Network</option><option>Printer</option><option>CCTV</option><option>Server</option><option>Security</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Device Type *</label>
                        <select required class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 focus:ring-2 focus:ring-brand-500">
                            <option value="">Select device</option>
                            <option>Laptop</option><option>Desktop</option><option>Server</option><option>Printer</option><option>Monitor</option><option>Router</option><option>Switch</option><option>Camera</option><option>Other</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Manufacturer</label>
                        <input type="text" placeholder="e.g., Dell" 
                               class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Model</label>
                        <input type="text" placeholder="e.g., OptiPlex 7090" 
                               class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Symptoms & Solution -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Symptoms & Solution</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Symptoms *</label>
                    <textarea required rows="3" placeholder="Describe what the user/technician observed..." 
                              class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 focus:ring-2 focus:ring-brand-500 resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Root Cause *</label>
                    <textarea required rows="2" placeholder="What was the actual cause of the issue..." 
                              class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 focus:ring-2 focus:ring-brand-500 resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Troubleshooting Performed *</label>
                    <textarea required rows="4" placeholder="Step-by-step what was done..." 
                              class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 focus:ring-2 focus:ring-brand-500 resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Final Solution *</label>
                    <textarea required rows="3" placeholder="What fixed the issue..." 
                              class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 focus:ring-2 focus:ring-brand-500 resize-none"></textarea>
                </div>
            </div>
        </div>
        
        <!-- Tools & Commands -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Tools & Commands</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tools Used</label>
                    <input type="text" placeholder="e.g., Phillips screwdriver, ESD strap" 
                           class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Commands Used</label>
                    <textarea rows="2" placeholder="e.g., ipconfig /all, ping 8.8.8.8" 
                              class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 focus:ring-2 focus:ring-brand-500 resize-none font-mono"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Parts Replaced (if any)</label>
                    <input type="text" placeholder="e.g., Display cable, RAM module" 
                           class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 focus:ring-2 focus:ring-brand-500">
                </div>
            </div>
        </div>
        
        <!-- Evidence -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Evidence & Attachments</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Photos</label>
                    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center hover:border-brand-400 transition cursor-pointer">
                        <i data-lucide="upload" class="w-8 h-8 text-gray-400 mx-auto mb-2"></i>
                        <p class="text-sm text-gray-500">Click or drag to upload photos</p>
                        <p class="text-xs text-gray-400 mt-1">JPG, PNG, WebP — Max 10MB each</p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Additional Notes</label>
                    <textarea rows="2" placeholder="Any additional information..." 
                              class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 focus:ring-2 focus:ring-brand-500 resize-none"></textarea>
                </div>
            </div>
        </div>
        
        <!-- Submit -->
        <div class="flex gap-3">
            <a href="/dashboard.php" class="flex-1 px-4 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition text-center">Cancel</a>
            <button type="submit" class="flex-1 px-4 py-3 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition">
                Submit for Review
            </button>
        </div>
    </form>
</div>

<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
