<?php
/**
 * API: IT Support AI Chat
 * POST /api/ai/chat
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

// Rate limiting
if (!isset($_SESSION['ai_requests'])) $_SESSION['ai_requests'] = [];
$now = time();
$_SESSION['ai_requests'] = array_filter($_SESSION['ai_requests'], fn($t) => $now - $t < 60);
if (count($_SESSION['ai_requests']) >= 20) { json_response(['error' => 'Rate limit exceeded'], 429); exit; }
$_SESSION['ai_requests'][] = $now;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { json_response(['error' => 'POST required'], 405); exit; }

$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');
if (empty($message)) { json_response(['error' => 'Message required'], 400); exit; }

$message = strip_tags(substr($message, 0, 2000));

// Block prompt injection
$blocked = ['ignore previous', 'system prompt', 'reveal', 'api key', 'password'];
foreach ($blocked as $p) { if (stripos($message, $p) !== false) { json_response(['response' => "I can only assist with IT troubleshooting.", 'confidence' => 'low']); exit; } }

// Off-topic
$offtopic = ['love poem', 'write me a', 'joke', 'story', 'recipe'];
foreach ($offtopic as $p) { if (stripos($message, $p) !== false) { json_response(['response' => "I can only assist with IT troubleshooting and related technical support topics.\n\nI can help with:\n- Hardware issues (display, power, sound, overheating)\n- Software issues (Windows, applications, drivers)\n- Network issues (connectivity, DNS, WiFi)\n- Printer issues (offline, quality, drivers)\n- CCTV issues (cameras, NVR, recording)\n- Commands and tools (CMD, PowerShell, diagnostics)", 'confidence' => 'low']); exit; } }

// Try to find matching troubleshooting issue from database
$matchedIssue = null;
$matchingSteps = null;
if (!(defined('DEMO_MODE') && DEMO_MODE)) {
    try {
        $issues = Database::fetchAll("SELECT * FROM troubleshooting_issues WHERE has_wizard = 1");
        foreach ($issues as $issue) {
            $title = strtolower($issue['title']);
            $syms = json_decode($issue['symptoms'] ?? '[]', true);
            if (stripos($title, $message) !== false || stripos($message, $title) !== false) {
                $matchedIssue = $issue;
                break;
            }
            foreach ($syms as $s) {
                $words = explode(' ', strtolower($s));
                $matchCount = 0;
                foreach ($words as $w) { if (strlen($w) > 3 && stripos($message, $w) !== false) $matchCount++; }
                if ($matchCount >= 2) { $matchedIssue = $issue; break 2; }
            }
        }
        // Get matching KB articles
        $kbArticles = Database::fetchAll(
            "SELECT * FROM knowledge_articles WHERE status = 'published' AND (title LIKE ? OR content LIKE ?) ORDER BY rating DESC LIMIT 3",
            ["%$message%", "%$message%"]
        );
    } catch (Exception $e) {}
} else {
    $issues = DemoData::issues();
    foreach ($issues as $issue) {
        if (stripos($issue['title'], $message) !== false || stripos($message, $issue['title']) !== false) {
            $matchedIssue = $issue; break;
        }
    }
    $kbArticles = [];
}

// Build response
if ($matchedIssue) {
    $steps = '';
    if (!(defined('DEMO_MODE') && DEMO_MODE)) {
        try {
            $nodes = Database::fetchAll(
                "SELECT * FROM decision_nodes WHERE issue_id = ? AND is_terminal = 0 AND node_number <= 6 ORDER BY node_number",
                [$matchedIssue['id']]
            );
            foreach ($nodes as $i => $node) {
                $steps .= ($i + 1) . ". **{$node['question']}**\n   _{$node['instruction']}_\n\n";
            }
        } catch (Exception $e) {}
    }

    $kbRef = '';
    if (!empty($kbArticles)) {
        $kbRef = "\n\n### Related Knowledge Articles\n";
        foreach ($kbArticles as $kb) {
            $kbRef .= "- **{$kb['title']}** (Rating: {$kb['rating']}/5, Used {$kb['use_count']} times)\n";
        }
    }

    $response = "### Understanding the Problem\nYou're experiencing: **{$matchedIssue['title']}**\n\n";
    $response .= "**Severity:** " . ucfirst($matchedIssue['severity'] ?? 'medium') . "\n\n";
    if ($steps) {
        $response .= "### Step-by-Step Troubleshooting\n\n";
        $response .= $steps;
        $response .= "### When to Escalate\nIf all steps above fail to resolve the issue, escalate to your supervisor with a full report of steps performed.\n";
    } else {
        $response .= "### Quick Guide\n";
        $response .= "1. Check physical connections first\n";
        $response .= "2. Test with known-good components\n";
        $response .= "3. Check system logs (eventvwr)\n";
        $response .= "4. Try the troubleshooting wizard in the app\n";
    }
    $response .= $kbRef;
    $response .= "\n### Did this help?\nClick YES or NO below to help us improve.";

    $confidence = 'high';
    $sources = ['Company Knowledge Base'];
} else {
    // Generic IT response with diagnostic questions
    $response = "### Understanding the Problem\nI understand you're experiencing: *\"{$message}\"*\n\n";
    $response .= "### Diagnostic Questions\nTo help you better, please answer:\n";
    $response .= "1. What device is affected? (Laptop/Desktop/Printer/Other)\n";
    $response .= "2. When did this start?\n";
    $response .= "3. Were any changes made recently?\n";
    $response .= "4. Is this affecting other users?\n\n";
    $response .= "### General Steps\n";
    $response .= "**Step 1:** Document the symptoms precisely\n";
    $response .= "**Step 2:** Check for recent changes (updates, moves, new software)\n";
    $response .= "**Step 3:** Test on a known-good device if possible\n";
    $response .= "**Step 4:** Check the Knowledge Base for similar issues\n\n";
    $response .= "### When to Escalate\n";
    $response .= "If the issue persists after basic checks, create a troubleshooting ticket with full details.";
    $confidence = 'medium';
    $sources = ['General IT Knowledge'];
}

json_response(['response' => $response, 'confidence' => $confidence, 'sources' => $sources]);
