<?php
/**
 * Whole-app mobile / wiring audit.
 *
 * Two classes of problem bit this app on phones and both are cheap to spot in
 * the source, so they are checked here instead of by opening every page:
 *
 *   1. An inline handler (onclick="...") that calls a function nobody defines.
 *      The button just does nothing - on a phone that is often the only way to
 *      close a sheet, so the page looks frozen.
 *   2. A modal panel shown with a hardcoded display:block. On a phone the sheet
 *      is a flex column whose inner body scrolls, so a block panel grows to its
 *      whole content height and cannot be scrolled (the ticket sheet, the KB and
 *      the equipment drawers all hit this).
 *   3. Phone-width hazards in page CSS: fixed viewport heights, and fixed pixel
 *      widths that cannot fit a 360px screen.
 *
 * Run: php tests/mobile-audit.php      (exits non-zero when it finds something)
 */

$root = dirname(__DIR__);
$findings = [];

function phpFiles(string $dir): array
{
    $out = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') $out[] = $file->getPathname();
    }
    sort($out);
    return $out;
}

$pageFiles = phpFiles($root . '/public/pages');
$jsFiles = glob($root . '/public/assets/js/*.js') ?: [];
$supportFiles = array_merge(glob($root . '/includes/*.php') ?: [], $jsFiles);

// ---------------------------------------------------------------- definitions
$defined = [];
foreach (array_merge($pageFiles, $supportFiles) as $file) {
    $src = (string)file_get_contents($file);
    if (preg_match_all('/function\s+([A-Za-z_$][\w$]*)\s*\(/', $src, $m)) {
        foreach ($m[1] as $name) $defined[$name] = true;
    }
    // window.foo = function / foo = function / const foo = (…) => / foo: function
    if (preg_match_all('/(?:window\.)?([A-Za-z_$][\w$]*)\s*=\s*(?:async\s+)?(?:function|\()/', $src, $m)) {
        foreach ($m[1] as $name) $defined[$name] = true;
    }
    if (preg_match_all('/\b([A-Za-z_$][\w$]*)\s*:\s*(?:async\s+)?function/', $src, $m)) {
        foreach ($m[1] as $name) $defined[$name] = true;
    }
}

// Names that are the language, the DOM or a well-known bundled library.
$builtins = array_flip([
    'if', 'for', 'while', 'switch', 'return', 'typeof', 'new', 'void', 'delete', 'catch', 'try',
    'function', 'in', 'of', 'instanceof', 'do', 'else', 'case',
    'alert', 'confirm', 'prompt', 'print', 'open', 'close', 'stop', 'focus', 'blur', 'submit',
    'click', 'scrollTo', 'scrollBy', 'setTimeout', 'setInterval', 'clearTimeout', 'clearInterval',
    'requestAnimationFrame', 'parseInt', 'parseFloat', 'isNaN', 'encodeURIComponent', 'decodeURIComponent',
    'String', 'Number', 'Boolean', 'Array', 'Object', 'JSON', 'Math', 'Date', 'RegExp', 'Promise',
    'Error', 'Set', 'Map', 'Symbol', 'BigInt', 'Function', 'eval', 'fetch', 'btoa', 'atob',
    'window', 'document', 'this', 'event', 'navigator', 'location', 'history', 'localStorage',
    'sessionStorage', 'console', 'lucide', 'Swal', 'bootstrap', 'jQuery', 'tt', 'ft',
]);

// --------------------------------------------------- 1. inline handler wiring
$handlerCalls = [];
$inlineScriptCalls = [];
foreach ($pageFiles as $file) {
    $src = (string)file_get_contents($file);
    $rel = str_replace($root . DIRECTORY_SEPARATOR, '', $file);
    // Attribute handlers: onclick="doThing(1)" and href="javascript:doThing()"
    if (preg_match_all('/(?:on[a-z]+\s*=\s*"([^"]*)"|href\s*=\s*"javascript:([^"]*)")/i', $src, $m, PREG_SET_ORDER)) {
        foreach ($m as $hit) {
            $body = $hit[1] !== '' ? $hit[1] : ($hit[2] ?? '');
            if (stripos($body, 'function') !== false) continue; // inline anonymous fn
            // Only calls outside strings count: a handler that writes CSS by hand
            // (style.transform='translateY(-50%) scale(1.02)' or a 'rgba(...)' shadow)
            // would otherwise look like a missing function, and a PHP block inside
            // the attribute is server-side code, not JavaScript.
            $body = preg_replace(["/'(?:[^'\\\\]|\\\\.)*'/s", '/"(?:[^"\\\\]|\\\\.)*"/s'], ['', ''], $body) ?? $body;
            $body = preg_replace('/<\?(?:php|=).*?\?>/s', '', $body) ?? $body;
            if (preg_match_all('/(?<![.\w$])([A-Za-z_$][\w$]*)\s*\(/', $body, $calls)) {
                foreach ($calls[1] as $name) {
                    if (isset($builtins[$name]) || isset($defined[$name])) continue;
                    $handlerCalls[$name][] = $rel;
                }
            }
        }
    }
}
foreach ($handlerCalls as $name => $files) {
    // A name called nowhere else is a genuinely dead handler.
    $findings[] = sprintf(
        'BROKEN HANDLER  %s() is called from an inline handler but is never defined (%s)',
        $name,
        implode(', ', array_unique($files))
    );
}

// ------------------------------------------- 2. modal panels forced to block
$panelNames = 'modal|panel|overlay|drawer|sheet|editor|viewer';
foreach (array_merge($pageFiles, $jsFiles) as $file) {
    $src = (string)file_get_contents($file);
    $rel = str_replace($root . DIRECTORY_SEPARATOR, '', $file);
    $lines = preg_split('/\r?\n/', $src);
    foreach ($lines as $i => $line) {
        if (!preg_match('/\.style\.display\s*=\s*[\'"]block[\'"]/', $line)) continue;
        if (!preg_match('/[\'"]([a-z0-9-]*(?:' . $panelNames . ')[a-z0-9-]*)[\'"]/i', $line, $id)) continue;
        if (preg_match('/(overlay|backdrop)/i', $id[1])) continue; // a backdrop may stay block
        $findings[] = sprintf(
            'BLOCK PANEL     %s:%d opens "%s" with display:block - use ftShowModal() so a phone sheet keeps its scrollable column',
            $rel,
            $i + 1,
            $id[1]
        );
    }
}

// --------------------------------------------------- 3. phone-width CSS traps
foreach (array_merge($pageFiles, $jsFiles, glob($root . '/public/assets/css/*.css') ?: []) as $file) {
    $rel = str_replace($root . DIRECTORY_SEPARATOR, '', $file);
    $lines = preg_split('/\r?\n/', (string)file_get_contents($file));
    foreach ($lines as $i => $line) {
        // A viewport-height box that ignores mobile browser chrome. The codebase
        // pairs every 100vh with a 100dvh line right after it, which is fine.
        if (preg_match('/height\s*:\s*(?:calc\([^)]*)?100vh/', $line) && stripos($line, 'dvh') === false
            && stripos($line, 'min-height') === false) {
            $lookAhead = implode("\n", array_slice($lines, $i + 1, 3));
            if (stripos($lookAhead, 'dvh') === false) {
                $findings[] = sprintf('100VH           %s:%d uses 100vh - on a phone the browser bars make that taller than the screen (prefer 100dvh)', $rel, $i + 1);
            }
        }
        // A container that cannot fit a 360px phone. A wide table is fine when
        // it sits in a scroll wrapper, which in this codebase always sits within
        // a few lines of it (…-scroll / …-wrap with overflow:auto).
        if (preg_match('/(?:^|[{;\s])min-width\s*:\s*(\d{3,})px/i', $line, $w) && (int)$w[1] >= 400) {
            $window = implode("\n", array_slice($lines, max(0, $i - 6), 13));
            if (!preg_match('/overflow(-x)?\s*:\s*auto/', $window)) {
                $findings[] = sprintf('FIXED WIDTH     %s:%d min-width:%spx - needs an overflow-x:auto wrapper or a phone override', $rel, $i + 1, $w[1]);
            }
        }
    }
}

if (!$findings) {
    echo "PASS - no broken handlers, no block-opened modal panels, no phone-width traps\n";
    exit(0);
}

echo count($findings) . " finding(s):\n\n";
foreach ($findings as $finding) echo '  ' . $finding . "\n";
exit(1);
