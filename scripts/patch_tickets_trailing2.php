<?php
$src = file_get_contents('public/pages/tickets.php');
$anchor = "                    </div>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </div>\r\n";
$pos = strrpos($src, $anchor);
if ($pos === false) {
    echo "ANCHOR NOT FOUND\n";
    exit(1);
}
$before = substr($src, 0, $pos + strlen($anchor));
$after  = substr($src, $pos + strlen($anchor));
// Drop everything after the closing </div> of the ticket card loop, then re-append footer
$newTail = "</div>\r\n\r\n<!-- Route mini-map container (one per solved ticket; opened on demand) -->\r\n<div id=\"route-map-root\"></div>\r\n\r\n";
$newSrc  = $before . $newTail;
// append the final trailing script + footer that already existed
$footerStart = strpos($src, '</script>', strrpos($src, '</script>') - 1);
if ($footerStart !== false) {
    // find start of the last script block
    $s = strrpos($src, '<script>', $footerStart - 4096);
    if ($s !== false) {
        $newSrc .= substr($src, $s);
    }
}
if (trim($newSrc) === '') {
    echo "FAILED: empty after patch\n";
    exit(1);
}
file_put_contents('public/pages/tickets.php', $newSrc);
echo "PATCHED. New length: " . strlen($newSrc) . "\n";
