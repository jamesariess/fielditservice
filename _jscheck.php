<?php
/**
 * One-off sanity check: strips strings/comments/regex-lite from app.js and
 * verifies braces, parens and brackets are balanced. Catches gross syntax
 * breakage when node is not available.
 */
$src = file_get_contents($argv[1] ?? (__DIR__ . '/public/assets/js/app.js'));
$len = strlen($src);
$depth = ['{' => 0, '(' => 0, '[' => 0];
$pairs = ['}' => '{', ')' => '(', ']' => '['];
$inS = null; $escape = false; $line = 1;

for ($i = 0; $i < $len; $i++) {
    $c = $src[$i];
    if ($c === "\n") { $line++; }
    if ($inS !== null) {
        if ($escape) { $escape = false; continue; }
        if ($c === '\\') { $escape = true; continue; }
        if ($c === $inS) { $inS = null; }
        continue;
    }
    // line comment
    if ($c === '/' && $i + 1 < $len && $src[$i + 1] === '/') {
        while ($i < $len && $src[$i] !== "\n") { $i++; }
        $line++;
        continue;
    }
    // block comment
    if ($c === '/' && $i + 1 < $len && $src[$i + 1] === '*') {
        $i += 2;
        while ($i + 1 < $len && !($src[$i] === '*' && $src[$i + 1] === '/')) {
            if ($src[$i] === "\n") { $line++; }
            $i++;
        }
        $i++;
        continue;
    }
    if ($c === '"' || $c === "'" || $c === '`') { $inS = $c; continue; }
    if (isset($depth[$c])) { $depth[$c]++; continue; }
    if (isset($pairs[$c])) {
        $open = $pairs[$c];
        $depth[$open]--;
        if ($depth[$open] < 0) { echo "UNBALANCED '$c' near line $line\n"; exit(1); }
    }
}

foreach ($depth as $k => $v) {
    if ($v !== 0) { echo "UNCLOSED '$k' x$v\n"; exit(1); }
}
echo "brace balance OK\n";
