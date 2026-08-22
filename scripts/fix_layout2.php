<?php
// Comprehensive fix for all page files
$files = glob(__DIR__ . '/../public/pages/**/*.php');
$files = array_merge($files, glob(__DIR__ . '/../public/pages/*.php'));

foreach ($files as $file) {
    $content = file_get_contents($file);
    $original = $content;
    
    // Remove trailing layout_footer calls first (to avoid duplication)
    $content = preg_replace("/\s*<\?php\s*layout_footer\(\);\s*\?>\s*$/", "", $content);
    $content = rtrim($content) . "\n";
    
    // Pattern 1: single line with <?php ... ?>
    // <?php $page_content = ob_get_clean(); require APP_ROOT . '/includes/layout.php'; ?>
    $content = preg_replace(
        '/<\?php\s*\$page_content\s*=\s*ob_get_clean\(\);\s*require\s+APP_ROOT\s*\.\s*[\'"]\/includes\/layout\.php[\'"];\s*\?>/',
        '<?php layout_footer(); ?>',
        $content
    );
    
    // Pattern 2: two separate lines without <?php
    // $page_content = ob_get_clean();
    // require APP_ROOT . '/includes/layout.php';
    $content = preg_replace(
        '/\$page_content\s*=\s*ob_get_clean\(\);\s*\n\s*require\s+APP_ROOT\s*\.\s*[\'"]\/includes\/layout\.php[\'"];\s*\n/',
        '<?php layout_footer(); ?>' . "\n",
        $content
    );
    
    // Pattern 3: already has layout_footer after old pattern (cleanup)
    $content = preg_replace(
        '/<\?php layout_footer\(\); \?>\s*\n<\?php layout_footer\(\); \?>/',
        '<?php layout_footer(); ?>',
        $content
    );
    
    // Clean up extra newlines
    $content = preg_replace("/\n{3,}/", "\n\n", $content);
    $content = rtrim($content) . "\n";
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "Fixed: " . str_replace(__DIR__ . '/../', '', $file) . "\n";
    }
}

echo "Done.\n";
