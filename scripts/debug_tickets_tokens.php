<?php
$src = file_get_contents('public/pages/tickets.php');
$tokens = token_get_all($src);
echo "total tokens: " . count($tokens) . "\n\n";
// List all block-control tokens and surrounding HTML near the end
for ($i = max(0, count($tokens) - 80); $i < count($tokens); $i++) {
    $tk = $tokens[$i];
    if (is_array($tk)) {
        echo "$i: " . token_name($tk[0]) . " => " . json_encode(substr($tk[1], 0, 50)) . "\n";
    } else {
        echo "$i: CHAR => " . json_encode($tk) . "\n";
    }
}
