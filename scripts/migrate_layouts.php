<?php
/**
 * Migrate all pages from old layout pattern to header/footer pattern
 */
$pagesDir = __DIR__ . '/../public/pages';
$files = array_merge(
    glob($pagesDir . '/*.php'),
    glob($pagesDir . '/admin/*.php')
);

foreach ($files as $file) {
    $basename = basename($file);
    if ($basename === 'login.php') continue;
    
    $content = file_get_contents($file);
    $original = $content;
    
    // Step 1: Remove ob_start(), $page_content = '', and layout_footer() calls
    $content = preg_replace('/\$page_content\s*=\s*[\'"][\'"]\s*;\s*\r?\n?/', '', $content);
    $content = preg_replace('/ob_start\(\)\s*;\s*\r?\n?/', '', $content);
    $content = preg_replace('/\$page_content\s*=\s*ob_get_clean\(\)\s*;\s*\r?\n?/', '', $content);
    $content = preg_replace('/<\?php\s*layout_footer\(\);\s*\?>\s*/', "\n", $content);
    
    // Step 2: Replace old layout.php include with layout_header.php
    $content = preg_replace(
        '/require(?:_once)?\s+APP_ROOT\s*\.\s*[\'"]\/includes\/layout\.php[\'"];\s*/',
        "require APP_ROOT . '/includes/layout_header.php';\n",
        $content
    );
    
    // Step 3: Clean up multiple blank lines
    $content = preg_replace('/\r?\n{3,}/', "\n\n", $content);
    
    // Step 4: Add layout_footer if not present
    $trimmed = rtrim($content);
    if (strpos($trimmed, 'layout_footer.php') === false) {
        $trimmed .= "\n<?php require APP_ROOT . '/includes/layout_footer.php'; ?>";
    }
    $content = $trimmed . "\n";
    
    // Step 5: Make sure it has layout_header
    if (strpos($content, 'layout_header.php') === false) {
        // Add header after variable setup
        $content = preg_replace(
            '/(\$page_title\s*=\s*[^\n]+\n\$active_menu\s*=\s*[^\n]+\n)/',
            "$1require APP_ROOT . '/includes/layout_header.php';\n",
            $content,
            1
        );
    }
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "Fixed: $basename\n";
    } else {
        echo "No change: $basename\n";
    }
}
echo "Done.\n";
