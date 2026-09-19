<?php
$src = file_get_contents('public/pages/tickets.php');
$tokens = token_get_all($src);
echo "first foreach token index: ";
foreach ($tokens as $i => $tk) {
    if (is_array($tk) && $tk[0] === T_OPEN_TAG && strpos($tk[1], 'foreach') !== false) {
        echo $i . "\n" . json_encode(substr($tk[1], 0, 120)) . "\n\n";
        break;
    }
}
// show the closing endforeach context
for ($i = 0; $i < count($tokens); $i++) {
    $tk = $tokens[$i];
    if (is_array($tk) && $tk[0] === T_CLOSE_TAG && strpos($tk[1], '?>') !== false) {
        // peek next few tokens
        $ctx = [];
        for ($j = $i + 1; $j < min(count($tokens), $i + 8); $j++) {
            $n = $tokens[$j];
            $ctx[] = is_array($n) ? token_name($n[0]) . ':' . json_encode(substr($n[1], 0, 40)) : json_encode($n);
        }
        echo "CLOSE_TAG at $i, following: " . implode(' | ', $ctx) . "\n";
    }
}
