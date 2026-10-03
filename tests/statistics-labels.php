<?php
/**
 * Statistics helper checks: the label tidy-up that stops multi-line session
 * titles from turning a ranked list into a wall of joined fragments, the
 * solved-rate traffic light, and the "nice" chart ceiling.
 *
 * These functions live in public/pages/admin/statistics.php, which cannot be
 * included here (it needs a login and a database), so their source is sliced
 * out of that file and evaluated as-is. A copy would drift and stop proving
 * anything.
 *
 * Run: php tests/statistics-labels.php     (exits non-zero on failure)
 */

$source = @file_get_contents(dirname(__DIR__) . '/public/pages/admin/statistics.php');
if ($source === false) {
    fwrite(STDERR, "Could not read public/pages/admin/statistics.php\n");
    exit(1);
}

$failures = 0;
$checks = 0;

/**
 * Copy one function out of the page source. A regex would run past the end of a
 * one-line function and swallow the rest of the file, so walk the braces and
 * skip string literals where braces are just text.
 */
function sliceFunction(string $source, string $name): string
{
    $start = strpos($source, 'function ' . $name . '(');
    $brace = $start === false ? false : strpos($source, '{', $start);
    if ($brace === false) {
        fwrite(STDERR, "Could not find function {$name}() in the statistics page.\n");
        exit(1);
    }
    $depth = 0;
    $length = strlen($source);
    for ($i = $brace; $i < $length; $i++) {
        $char = $source[$i];
        if ($char === '{') {
            $depth++;
        } elseif ($char === '}') {
            if (--$depth === 0) return substr($source, $start, $i - $start + 1);
        } elseif ($char === "'" || $char === '"') {
            $quote = $char;
            while (++$i < $length && $source[$i] !== $quote) {
                if ($source[$i] === '\\') $i++;
            }
        }
    }
    fwrite(STDERR, "Function {$name}() is not closed in the statistics page.\n");
    exit(1);
}

eval(sliceFunction($source, 'statLabel'));
eval(sliceFunction($source, 'statRateBand'));
eval(sliceFunction($source, 'chartCeiling'));

function check(string $name, bool $ok, string $detail = ''): void
{
    global $failures, $checks;
    $checks++;
    if (!$ok) $failures++;
    echo ($ok ? 'PASS' : 'FAIL') . ' | ' . $name . ($detail !== '' ? "  [{$detail}]" : '') . "\n";
}

// A real session title: several lines of free text joined by newlines.
$messy = "i Unable to power on\ni\nUnable to power on ih Upon checking unit has no any sign\nif For Onsite Checking of power supply unit";
$clean = statLabel($messy);
check('newlines and runs of spaces collapse into one line', strpos($clean, "\n") === false && strpos($clean, '  ') === false, $clean);
check('the tidied label keeps the first line intact', strpos($clean, 'i Unable to power on') === 0, substr($clean, 0, 40));

$short = statLabel('Microphone Not Working');
check('a short label is left untouched', $short === 'Microphone Not Working', $short);
check('the cleaned label fits the row budget', mb_strlen(statLabel($messy)) <= 96, mb_strlen(statLabel($messy)) . ' chars');

$tabs = statLabel("Paper\tJam\n\n  on tray 2  ");
check('tabs and blank lines collapse too', $tabs === 'Paper Jam on tray 2', $tabs);

check('an empty label falls back to a readable word', statLabel('') === 'Unspecified' && statLabel("  \n ") === 'Unspecified', statLabel(''));
check('a long label is clipped with an ellipsis', mb_substr(statLabel(str_repeat('printer offline ', 20)), -1) === '…', mb_substr(statLabel(str_repeat('printer offline ', 20)), -40));
check('clipping never leaves a trailing space before the ellipsis',
    !preg_match('/\s…$/u', statLabel(str_repeat('OptiPlex 7010 SFF overheating ', 10))), mb_substr(statLabel(str_repeat('OptiPlex 7010 SFF overheating ', 10)), -12));
check('a 400-char label keeps the whole text for the hover title', mb_strlen(statLabel($messy, 400)) > 96, mb_strlen(statLabel($messy, 400)) . ' chars');

check('solved rates at or above 70% read as good', statRateBand(70) === 'good' && statRateBand(100) === 'good', statRateBand(70));
check('mid-band rates read as mid', statRateBand(40) === 'mid' && statRateBand(69) === 'mid', statRateBand(69));
check('low rates read as low', statRateBand(0) === 'low' && statRateBand(39) === 'low', statRateBand(39));
check('the watched 33% solved case is flagged low', statRateBand(33) === 'low', statRateBand(33));

// The chart ceiling: bars are drawn as a share of the axis, so it has to clear
// the busiest day and still put the 50% gridline on a whole number.
foreach ([0 => 1, 1 => 1, 2 => 2, 3 => 4, 4 => 4, 5 => 6, 10 => 10, 12 => 16, 25 => 26, 26 => 30, 60 => 60, 61 => 80, 140 => 140] as $peak => $expected) {
    check("axis ceiling for a peak of {$peak} is {$expected}", chartCeiling($peak) === $expected, 'got ' . chartCeiling($peak));
}
$oddCeilings = array_filter([7, 12, 23, 27, 61], fn($peak) => chartCeiling($peak) % 2 !== 0 && chartCeiling($peak) > 2);
check('the axis ceiling always halves on a whole number', $oddCeilings === [], implode('/', $oddCeilings));

echo "\n" . ($checks - $failures) . '/' . $checks . " checks passed\n";
exit($failures === 0 ? 0 : 1);
