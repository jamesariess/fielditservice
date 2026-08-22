<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Access Denied</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="h-full bg-gray-50 flex items-center justify-center">
    <div class="text-center">
        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i data-lucide="shield-x" class="w-10 h-10 text-red-500"></i>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Access Denied</h1>
        <p class="text-gray-500 mb-6">You don't have permission to access this page.</p>
        <a href="/" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition">Return to Dashboard</a>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
<?php require APP_ROOT . '/includes/layout_footer.php'; ?>
