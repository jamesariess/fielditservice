<?php
$files = glob(__DIR__ . '/../public/pages/**/*.php');
$files = array_merge($files, glob(__DIR__ . '/../public/pages/*.php'));

foreach ($files as $file) {
    $content = file_get_contents($file);
    $original = $content;
    
    // Remove trailing layout_footer calls
    $content = preg_replace("/\s*<\?php\s*layout_footer\(\);\s*\?>\s*$/", "", $content);
    $content = rtrim($content) . "\n";
    
    // Pattern 1: single line with <?php ... ?>
    $content = preg_replace(
        '/<\?php\s*\$page_content\s*=\s*ob_get_clean\(\);\s*require\s+APP_ROOT\s*\.\s*[\'"]\/includes\/layout\.php[\'"];\s*\?>/',
        '<?php layout_footer(); ?>',
        $content
    );
    
    // Pattern 2: two separate lines
    $content = preg_replace(
        '/\$page_content\s*=\s*ob_get_clean\(\);\s*\n\s*require\s+APP_ROOT\s*\.\s*[\'"]\/includes\/layout\.php[\'"];\s*\n/',
        "<?php layout_footer(); ?>\n",
        $content
    );
    
    $content = preg_replace("/\n{3,}/", "\n\n", $content);
    $content = rtrim($content) . "\n";
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "Fixed: " . basename($file) . "\n";
    }
}
echo "Done.\n";
