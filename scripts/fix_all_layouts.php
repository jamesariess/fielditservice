<?php
/**
 * Fix all page files to use layout_header.php + layout_footer.php pattern
 */
$files = [];
$pages = glob(__DIR__ . '/../public/pages/*.php');
$adminPages = glob(__DIR__ . '/../public/pages/admin/*.php');
$files = array_merge($pages, $adminPages);

// Exclude login (doesn't use layout)
$files = array_filter($files, fn($f) => basename($f) !== 'login.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    $original = $content;
    
    // Remove any layout_footer() calls
    $content = preg_replace("/\s*<\?php\s*layout_footer\(\);\s*\?>\s*/", "\n", $content);
    
    // Remove any require of old layout.php
    $content = preg_replace("/require(?:_once)?\s+APP_ROOT\s*\.\s*['\"]\/includes\/layout\.php['\"];\s*/", "require APP_ROOT . '/includes/layout_header.php';", $content);
    
    // Remove any $page_content = ob_get_clean() lines
    $content = preg_replace("/\\\$page_content\s*=\s*ob_get_clean\(\);\s*\n?/", "", $content);
    
    // Clean up multiple blank lines
    $content = preg_replace("/\n{3,}/", "\n\n", $content);
    
    // Make sure file starts with proper PHP tag
    $content = ltrim($content);
    
    // Add layout_footer at the end if not present
    $trimmed = rtrim($content);
    if (strpos($trimmed, 'layout_footer.php') === false || substr_count($trimmed, 'layout_footer.php') === 0) {
        // Check what the last meaningful line is
        if (substr($trimmed, -3) === '?>') {
            // Ends with PHP close tag - add footer after
            $content = $trimmed . "\n<?php require APP_ROOT . '/includes/layout_footer.php'; ?>\n";
        } elseif (substr($trimmed, -2) === '?>') {
            $content = $trimmed . "\n<?php require APP_ROOT . '/includes/layout_footer.php'; ?>\n";
        } else {
            // HTML content - just append footer include
            $content = $trimmed . "\n<?php require APP_ROOT . '/includes/layout_footer.php'; ?>\n";
        }
    }
    
    $content = rtrim($content) . "\n";
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "Fixed: " . basename($file) . "\n";
    }
}
echo "Done.\n";
