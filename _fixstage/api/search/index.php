<?php
/**
 * API: Global Search
 * GET ?q=display - search across all content
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(__DIR__))); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) { require_once APP_ROOT . '/includes/Database.php'; }
require_once APP_ROOT . '/includes/Auth.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) { require_once APP_ROOT . '/includes/DemoData.php'; }
Auth::start();

if (!Auth::isLoggedIn()) { json_response(['error' => 'Unauthorized'], 401); exit; }

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) { json_response(['error' => 'Query must be at least 2 characters'], 400); exit; }

$results = [];
$like = "%{$q}%";

if (defined('DEMO_MODE') && DEMO_MODE) {
    // Search troubleshooting issues
    $issues = DemoData::issues();
    foreach ($issues as $issue) {
        if (stripos($issue['title'], $q) !== false || stripos(implode(' ', $issue['symptoms'] ?? []), $q) !== false) {
            $results[] = ['type' => 'troubleshooting', 'title' => $issue['title'], 'description' => implode(', ', array_slice($issue['symptoms'] ?? [], 0, 2)), 'url' => '/troubleshoot/wizard?issue=' . $issue['id'], 'icon' => $issue['icon'] ?? 'help-circle'];
        }
    }
    // Search KB
    $articles = DemoData::knowledgeArticles();
    foreach ($articles as $a) {
        if (stripos($a['title'], $q) !== false || stripos($a['content'], $q) !== false) {
            $results[] = ['type' => 'knowledge', 'title' => $a['title'], 'description' => $a['content'], 'url' => '/knowledge/view?id=' . $a['id'], 'icon' => 'book-open'];
        }
    }
    // Search commands
    $commands = DemoData::commands();
    foreach ($commands as $c) {
        if (stripos($c['command'], $q) !== false || stripos($c['description'], $q) !== false) {
            $results[] = ['type' => 'command', 'title' => $c['command'], 'description' => $c['description'], 'url' => '/commands', 'icon' => 'terminal'];
        }
    }
} else {
    // Database search
    try {
        // Troubleshooting issues
        $issues = Database::fetchAll(
            "SELECT * FROM troubleshooting_issues WHERE title LIKE ? OR symptoms LIKE ? LIMIT 10",
            [$like, $like]
        );
        foreach ($issues as $issue) {
            $syms = json_decode($issue['symptoms'] ?? '[]', true);
            $results[] = ['type' => 'troubleshooting', 'title' => $issue['title'], 'description' => implode(', ', array_slice($syms, 0, 2)), 'url' => '/troubleshoot/wizard?issue=' . $issue['id'], 'icon' => $issue['icon'] ?? 'help-circle'];
        }

        // Knowledge articles
        $articles = Database::fetchAll(
            "SELECT * FROM knowledge_articles WHERE (title LIKE ? OR content LIKE ?) AND status = 'published' LIMIT 10",
            [$like, $like]
        );
        foreach ($articles as $a) {
            $results[] = ['type' => 'knowledge', 'title' => $a['title'], 'description' => substr($a['content'] ?? '', 0, 120), 'url' => '/knowledge/view?id=' . $a['id'], 'icon' => 'book-open'];
        }

        // Commands
        $commands = Database::fetchAll(
            "SELECT * FROM commands WHERE command LIKE ? OR description LIKE ? LIMIT 10",
            [$like, $like]
        );
        foreach ($commands as $c) {
            $results[] = ['type' => 'command', 'title' => $c['command'], 'description' => $c['description'], 'url' => '/commands', 'icon' => 'terminal'];
        }

        // Equipment
        $models = Database::fetchAll(
            "SELECT * FROM device_models WHERE model LIKE ? OR manufacturer_name LIKE ? OR device_type LIKE ? LIMIT 10",
            [$like, $like, $like]
        );
        foreach ($models as $m) {
            $results[] = ['type' => 'equipment', 'title' => $m['manufacturer_name'] . ' ' . $m['model'], 'description' => $m['device_type'], 'url' => '/equipment', 'icon' => 'package'];
        }
    } catch (Exception $e) {
        error_log('Search error: ' . $e->getMessage());
    }
}

json_response(['query' => $q, 'results' => $results, 'total' => count($results)]);
