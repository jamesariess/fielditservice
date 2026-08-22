<?php
// Fix all page files to use layout_footer() instead of old pattern
$files = glob(__DIR__ . '/../public/pages/**/*.php');
foreach ($files as $file) {
    $content = file_get_contents($file);
    $original = $content;
    
    // Remove any existing layout_footer calls
    $content = preg_replace("/\s*<\?php\s*layout_footer\(\);\s*\?>\s*$/", "", $content);
    
    // Replace: $page_content = ob_get_clean();
    // Then:   require APP_ROOT . '/includes/layout.php';
    // Single line version
    $content = preg_replace(
        '/<\?php\s*\$page_content\s*=\s*ob_get_clean\(\);\s*require\s+APP_ROOT\s*\.\s*[\'"]\/includes\/layout\.php[\'"];\s*\?>/',
        '<?php layout_footer(); ?>',
        $content
    );
    
    // Two line version
    $content = preg_replace(
        '/\$page_content\s*=\s*ob_get_clean\(\);\s*\n\s*require\s+APP_ROOT\s*\.\s*[\'"]\/includes\/layout\.php[\'"];\s*/',
        '<?php layout_footer(); ?>',
        $content
    );
    
    $content = rtrim($content) . "\n";
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "Fixed: " . basename($file) . "\n";
    }
}
echo "Done.\n";
