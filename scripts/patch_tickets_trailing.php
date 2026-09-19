<?php
$src = file_get_contents('public/pages/tickets.php');
$target = "    <?php endforeach; ?>\r\n    <?php endforeach; ?>\r\n</div>";
$repl   = "    <?php endforeach; ?>\r\n</div>";
if (strpos($src, $target) !== false) {
    $src = str_replace($target, $repl, $src);
    file_put_contents('public/pages/tickets.php', $src);
    echo "PATCHED (double endforeach removed)\n";
} else {
    echo "PATTERN NOT FOUND\n";
}

// verify
$tokens = token_get_all($src);
$lastCloseIdx = null;
for ($i = count($tokens) - 1; $i >= 0; $i--) {
    if (is_array($tokens[$i]) && $tokens[$i][0] === T_CLOSE_TAG) { $lastCloseIdx = $i; break; }
}
$ctx = [];
for ($j = max(0, $lastCloseIdx - 3); $j < min(count($tokens), $lastCloseIdx + 6); $j++) {
    $n = $tokens[$j];
    $ctx[] = is_array($n) ? token_name($n[0]) . ':' . json_encode(substr($n[1], 0, 30)) : json_encode($n);
}
echo "Last close context: " . implode(' | ', $ctx) . "\n";
