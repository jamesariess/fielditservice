<?php
/**
 * Global Helper Functions
 */

function e(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function json_response(mixed $data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function redirect(string $url): never {
    header("Location: {$url}");
    exit;
}

function sanitize(string $input): string {
    return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
}

function time_ago(string $datetime): string {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', $time);
}

function status_badge(string $status): string {
    $colors = [
        'draft' => 'bg-gray-100 text-gray-700',
        'submitted' => 'bg-blue-100 text-blue-700',
        'under_review' => 'bg-yellow-100 text-yellow-700',
        'approved' => 'bg-green-100 text-green-700',
        'published' => 'bg-green-100 text-green-700',
        'rejected' => 'bg-red-100 text-red-700',
        'archived' => 'bg-gray-100 text-gray-500',
        'new' => 'bg-blue-100 text-blue-700',
        'in_progress' => 'bg-yellow-100 text-yellow-700',
        'solved' => 'bg-green-100 text-green-700',
        'escalated' => 'bg-red-100 text-red-700',
        'partial' => 'bg-orange-100 text-orange-700',
        'unsolved' => 'bg-red-100 text-red-700',
    ];
    $class = $colors[strtolower($status)] ?? 'bg-gray-100 text-gray-700';
    return "<span class=\"inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$class}\">" . ucfirst(str_replace('_', ' ', $status)) . "</span>";
}

function risk_badge(string $level): string {
    $map = [
        'safe' => '<span class="inline-flex items-center gap-1 text-xs font-medium text-green-700"><span class="w-2 h-2 rounded-full bg-green-500"></span>Safe</span>',
        'caution' => '<span class="inline-flex items-center gap-1 text-xs font-medium text-yellow-700"><span class="w-2 h-2 rounded-full bg-yellow-500"></span>Caution</span>',
        'danger' => '<span class="inline-flex items-center gap-1 text-xs font-medium text-red-700"><span class="w-2 h-2 rounded-full bg-red-500"></span>High Risk</span>',
    ];
    return $map[strtolower($level)] ?? $map['safe'];
}
