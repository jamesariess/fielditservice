<?php
final class TroubleshootingReport {
    public static function actions(array $history): string {
        $lines = [];
        foreach ($history as $entry) {
            if (($entry['type'] ?? '') !== 'step') continue;
            $action = trim((string)($entry['question'] ?? ''));
            if ($action === '') continue;
            $outcome = ($entry['answer'] ?? '') === 'worked' ? 'Worked' : 'Did not work';
            $lines[] = (count($lines) + 1) . '. ' . $action . ' - ' . $outcome;
        }
        return implode("\n", $lines);
    }
}
