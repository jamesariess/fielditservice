<?php
$src = file_get_contents('public/pages/tickets.php');

// Isolate the ticket card loop body end + keep only ONE endforeach
$search = "    </div>\r\n    <?php endforeach; ?>\r\n</div>\r\n\r\n<!-- Route mini-map container (one per solved ticket; opened on demand) -->\r\n<div id=\"route-map-root\"></div>\r\n    <?php endforeach; ?>\r\n\r\n<link rel=\"stylesheet\"";
$replace = "    </div>\r\n    <?php endforeach; ?>\r\n</div>\r\n\r\n<!-- Route mini-map container (one per solved ticket; opened on demand) -->\r\n<div id=\"route-map-root\"></div>\r\n\r\n<link rel=\"stylesheet\"";

if (strpos($src, $search) !== false) {
    $src = str_replace($search, $replace, $src);
    file_put_contents('public/pages/tickets.php', $src);
    echo "PATCHED trailing duplicate endforeach.\n";
} else {
    echo "PATTERN NOT FOUND; dumping tail tokens:\n";
    // diagnostic: show raw tail
    echo substr($src, -700) . "\n";
}
