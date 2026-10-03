<?php
final class DecisionFlow {
    public static function steps(array $nodes, array $answers): array {
        return array_values(array_filter($nodes, function($node) use ($answers) {
            if (($node['node_type'] ?? '') !== 'step' || !empty($node['is_terminal']) || !empty($node['result_type'])) return false;
            $mode=$node['visibility_mode'] ?? 'always';
            $mode=['yes'=>'yes_only','no'=>'no_only'][$mode] ?? $mode;
            if (in_array($mode,['always','both'],true)) return true;
            $question=$node['visible_for_question_id'] ?? null;
            if (!$question) return false;
            if (!in_array($mode,['yes_only','no_only'],true)) return false;
            return ($answers[$question] ?? null) === ($mode==='yes_only' ? 'yes' : 'no');
        }));
    }
    public static function next(array $steps, array $current, array $visited): ?array {
        $visited=array_map('intval',$visited); $visited[]=(int)$current['id'];
        $linked=(int)($current['no_next'] ?? 0);
        foreach ($steps as $step) if ((int)$step['id']===$linked && !in_array($linked,$visited,true)) return $step;
        foreach ($steps as $step) if (!in_array((int)$step['id'],$visited,true)) return $step;
        return null;
    }
}
