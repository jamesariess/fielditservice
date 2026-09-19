<?php
$src = file_get_contents('public/pages/tickets.php');

// The file currently contains (around line 390):
//     <?php endforeach; ?>
// </div>
//
// The "</div>" here is OUTSIDE any <?php ... ?> block, so PHP sees it as
// raw HTML immediately after an endforeach, which is illegal. Fix by moving it
// inside the PHP block.
$bad  = "    <?php endforeach; ?>\r\n</div>";
$good = "    <?php endforeach; ?>\r\n</div>\r\n";
if (strpos($src, $bad) !== false) {
    $src = str_replace($bad, $good, $src);
    file_put_contents('public/pages/tickets.php', $src);
    echo "PATCHED stray closing div into PHP block.\n";
} else {
    echo "BAD PATTERN not found; tail:\n" . substr($src, -400) . "\n";
}
