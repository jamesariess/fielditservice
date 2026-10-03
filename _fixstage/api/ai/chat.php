<?php
/**
 * API: IT Bot — AI Support Chat v4 (Conversational)
 * POST /api/ai/chat — Send message, get response
 * GET /api/ai/chat — Get conversation history
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(__DIR__))); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) { require_once APP_ROOT . '/includes/Database.php'; }
require_once APP_ROOT . '/config/ai_db.php';
require_once APP_ROOT . '/includes/AIDatabase.php';
require_once APP_ROOT . '/includes/Auth.php';
require_once APP_ROOT . '/includes/AIService.php';
Auth::start();

if (!Auth::isLoggedIn()) { json_response(['error' => 'Unauthorized'], 401); exit; }

$userId = $_SESSION['user_id'] ?? 0;
$method = $_SERVER['REQUEST_METHOD'];

// ===== GET: Return conversation history =====
if ($method === 'GET') {
    $sessionId = $_GET['session'] ?? '';
    $personality = null;
    if (!(defined('DEMO_MODE') && DEMO_MODE)) {
        try { $personality = AIDatabase::fetch("SELECT * FROM ai_personality WHERE is_active = 1 ORDER BY id DESC LIMIT 1"); } catch (Exception $e) {}
    }
    if (!$personality) {
        $personality = ['bot_name' => 'IT Bot', 'greeting' => "Hey there! 👋 I'm **IT Bot**, your IT support buddy. Tell me what's going on with your device and I'll help you sort it out.\n\nYou can describe the problem in your own words — no need for technical jargon!"];
    }
    $history = [];
    if ($sessionId && !(defined('DEMO_MODE') && DEMO_MODE)) {
        try { $rawLogs = AIDatabase::fetchAll("SELECT message, response FROM ai_conversation_logs WHERE session_id = ? AND user_id = ? ORDER BY created_at ASC LIMIT 50", [$sessionId, $userId]); $history = []; foreach ($rawLogs as $log) { if (!empty($log['message'])) $history[] = ['role' => 'user', 'content' => $log['message']]; if (!empty($log['response'])) $history[] = ['role' => 'assistant', 'content' => $log['response']]; } } catch (Exception $e) {}
    }
    $trainingCount = 0;
    if (!(defined('DEMO_MODE') && DEMO_MODE)) {
        try { $row = AIDatabase::fetch("SELECT COUNT(*) as cnt FROM ai_training_files WHERE is_active = 1"); $trainingCount = $row['cnt'] ?? 0; } catch (Exception $e) {}
    }
    json_response(['personality' => $personality, 'history' => $history, 'training_count' => $trainingCount, 'quota' => aiQuotaInfo($userId)]);
    exit;
}

// ===== POST: Send message =====
if ($method !== 'POST') { json_response(['error' => 'POST or GET required'], 405); exit; }

// ===== AI usage limits: admins unlimited; others get burst protection + daily quota =====
$quota = aiQuotaInfo($userId);
if (!isset($_SESSION['ai_requests'])) $_SESSION['ai_requests'] = [];
$now = time();
$_SESSION['ai_requests'] = array_filter($_SESSION['ai_requests'], fn($t) => $now - $t < 60);
$burstLimit = defined('AI_BURST_LIMIT') ? (int)AI_BURST_LIMIT : 30;
if (count($_SESSION['ai_requests']) >= $burstLimit) {
    json_response(['error' => 'You are sending messages too quickly. Please wait a minute and try again.', 'limit_type' => 'burst', 'retry_after' => 60], 429); exit;
}
if (!$quota['unlimited'] && $quota['remaining_today'] <= 0) {
    json_response(['error' => 'You have used all ' . $quota['daily_limit'] . ' free AI messages for today. Your allowance resets tomorrow. Admins have unlimited AI access.', 'limit_type' => 'daily', 'quota' => $quota], 429); exit;
}
$_SESSION['ai_requests'][] = $now;

$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');
$sessionId = $input['session_id'] ?? 'session_' . $userId . '_' . date('Ymd');
$conversationHistory = $input['history'] ?? [];
if (empty($message)) { json_response(['error' => 'Message required'], 400); exit; }

$message = strip_tags(substr($message, 0, 2000));
$lowerMsg = strtolower($message);

// Block prompt injection
$blocked = ['ignore previous', 'system prompt', 'reveal', 'api key', 'password', 'ignore all', 'you are now'];
foreach ($blocked as $p) { if (stripos($lowerMsg, $p) !== false) { json_response(['response' => "I can only assist with IT troubleshooting. What's going on with your device?", 'confidence' => 'low', 'session_id' => $sessionId, 'quota' => $quota]); exit; } }

// Always load conversation history from DB for context
$conversationHistory = [];
if (!(defined('DEMO_MODE') && DEMO_MODE)) {
    try {
        $dbLogs = AIDatabase::fetchAll("SELECT message, response FROM ai_conversation_logs WHERE session_id = ? AND user_id = ? ORDER BY created_at ASC LIMIT 10", [$sessionId, $userId]);
        foreach ($dbLogs as $log) {
            if (!empty($log['message'])) $conversationHistory[] = ['role' => 'user', 'content' => $log['message']];
            if (!empty($log['response'])) $conversationHistory[] = ['role' => 'assistant', 'content' => $log['response']];
        }
    } catch (Exception $e) {}
}
$preCtx = buildConversationContext($conversationHistory);

// ===== CHECK CONVERSATIONAL PATTERNS FIRST (before database search) =====
$conversationalReply = checkConversationalPatterns($lowerMsg, $message, $preCtx);
if ($conversationalReply !== null) {
    // Try Groq first so small-talk gets natural, smart replies; template is only a fallback
    $groqReply = tryGroqChat($message, $conversationHistory);
    if ($groqReply !== false) {
        json_response(['response' => $groqReply, 'confidence' => 'high', 'sources' => ['AI Assistant'], 'session_id' => $sessionId, 'bot_name' => 'IT Bot', 'quota' => $quota]);
        exit;
    }
    json_response(['response' => $conversationalReply, 'confidence' => 'high', 'sources' => ['IT Bot'], 'session_id' => $sessionId, 'bot_name' => 'IT Bot', 'quota' => $quota]);
    exit;
}

// Get bot personality
$botName = 'IT Bot';
if (!(defined('DEMO_MODE') && DEMO_MODE)) {
    try { $personality = AIDatabase::fetch("SELECT * FROM ai_personality WHERE is_active = 1 ORDER BY id DESC LIMIT 1"); if ($personality) $botName = $personality['bot_name']; } catch (Exception $e) {}
}

// Detect category and extract keywords
$category = detectCategory($lowerMsg);
$keywords = extractKeywords($lowerMsg);

// ===== SEARCH ALL DATA SOURCES =====
$results = [];

if (!(defined('DEMO_MODE') && DEMO_MODE)) {
    try {
        // 1. Training Files
        $trainingResults = AIDatabase::fetchAll("SELECT * FROM ai_training_files WHERE is_active = 1 AND (content LIKE ? OR title LIKE ? OR tags LIKE ?) LIMIT 3", ["%$message%", "%$message%", "%$message%"]);
        foreach ($trainingResults as $tf) { $score = scoreItem($lowerMsg, $keywords, ($tf['title'] ?? '') . ' ' . ($tf['content'] ?? ''), 'training'); $results[] = ['type' => 'training', 'data' => $tf, 'score' => $score]; }

        // 2. Error Codes — search by full message and each keyword individually
        $ecSql = "SELECT * FROM error_codes WHERE code LIKE ? OR code LIKE ? OR title LIKE ? OR description LIKE ? OR common_causes LIKE ?";
        $ecParams = ["%$message%", "%{$keywords[0]}%", "%$message%", "%$message%", "%$message%"];
        foreach (array_slice($keywords, 1, 5) as $kw) { $ecSql .= " OR code LIKE ? OR title LIKE ?"; $ecParams[] = "%$kw%"; $ecParams[] = "%$kw%"; }
        $ecSql .= " LIMIT 5";
        $ecResults = AIDatabase::fetchAll($ecSql, $ecParams);
        foreach ($ecResults as $ec) { $score = scoreItem($lowerMsg, $keywords, $ec['code'] . ' ' . $ec['title'] . ' ' . ($ec['description'] ?? ''), 'error_code'); $results[] = ['type' => 'error_code', 'data' => $ec, 'score' => $score]; }

        // 3. Knowledge Base
        $kbResults = AIDatabase::fetchAll("SELECT * FROM knowledge_articles WHERE status = 'published' AND (title LIKE ? OR title LIKE ? OR issue LIKE ? OR symptoms LIKE ? OR solution LIKE ?) LIMIT 5", ["%$message%", "%{$keywords[0]}%", "%$message%", "%$message%", "%$message%"]);
        foreach ($kbResults as $kb) { $score = scoreItem($lowerMsg, $keywords, ($kb['title'] ?? '') . ' ' . ($kb['solution'] ?? ''), 'knowledge'); $results[] = ['type' => 'knowledge', 'data' => $kb, 'score' => $score]; }

        // 4. Troubleshooting Issues
        $issResults = AIDatabase::fetchAll("SELECT * FROM troubleshooting_issues WHERE status = 'approved' AND (title LIKE ? OR title LIKE ? OR description LIKE ? OR symptoms LIKE ?) LIMIT 5", ["%$message%", "%{$keywords[0]}%", "%$message%", "%$message%"]);
        foreach ($issResults as $iss) { $score = scoreItem($lowerMsg, $keywords, ($iss['title'] ?? '') . ' ' . ($iss['description'] ?? ''), 'issue'); if ($category && stripos($iss['title'], $category) !== false) $score += 5; $results[] = ['type' => 'issue', 'data' => $iss, 'score' => $score]; }

        // 5. Decision Nodes
        $nodeResults = AIDatabase::fetchAll("SELECT dn.*, ti.title as issue_title FROM decision_nodes dn JOIN troubleshooting_issues ti ON dn.issue_id = ti.id WHERE (dn.question LIKE ? OR dn.description LIKE ?) AND dn.node_type = 'step' AND dn.is_terminal = 0 LIMIT 5", ["%$message%", "%$message%"]);
        foreach ($nodeResults as $node) { $score = scoreItem($lowerMsg, $keywords, ($node['question'] ?? '') . ' ' . ($node['issue_title'] ?? ''), 'step'); $results[] = ['type' => 'step', 'data' => $node, 'score' => $score]; }

        // 6. Commands
        $cmdResults = AIDatabase::fetchAll("SELECT * FROM commands WHERE name LIKE ? OR description LIKE ? OR syntax LIKE ? LIMIT 3", ["%$message%", "%$message%", "%$message%"]);
        foreach ($cmdResults as $cmd) { $score = scoreItem($lowerMsg, $keywords, ($cmd['name'] ?? '') . ' ' . ($cmd['description'] ?? ''), 'command'); $results[] = ['type' => 'command', 'data' => $cmd, 'score' => $score]; }

        // 7. Tools
        $toolResults = AIDatabase::fetchAll("SELECT * FROM tools WHERE name LIKE ? OR purpose LIKE ? OR how_to_use LIKE ? LIMIT 3", ["%$message%", "%$message%", "%$message%"]);
        foreach ($toolResults as $tool) { $score = scoreItem($lowerMsg, $keywords, ($tool['name'] ?? '') . ' ' . ($tool['description'] ?? ''), 'tool'); $results[] = ['type' => 'tool', 'data' => $tool, 'score' => $score]; }
    } catch (Exception $e) { error_log("AI Chat Error: " . $e->getMessage()); }
}

// Sort and deduplicate
usort($results, fn($a, $b) => $b['score'] - $a['score']);
$seen = [];
$uniqueResults = [];
foreach ($results as $r) { $key = $r['type'] . '_' . ($r['data']['id'] ?? md5($r['data']['title'] ?? $r['data']['code'] ?? '')); if (!isset($seen[$key])) { $seen[$key] = true; $uniqueResults[] = $r; } }
$topResults = array_slice($uniqueResults, 0, 6);

// Load conversation history from DB if frontend didn't send enough
if (empty($conversationHistory) && !(defined('DEMO_MODE') && DEMO_MODE)) {
    try { $rawLogs = AIDatabase::fetchAll("SELECT message, response FROM ai_conversation_logs WHERE session_id = ? AND user_id = ? ORDER BY created_at ASC LIMIT 10", [$sessionId, $userId]); $conversationHistory = []; foreach ($rawLogs as $log) { if (!empty($log['message'])) $conversationHistory[] = ['role' => 'user', 'content' => $log['message']]; if (!empty($log['response'])) $conversationHistory[] = ['role' => 'assistant', 'content' => $log['response']]; } } catch (Exception $e) {}
}

// Build rich conversation context
$chatCtx = buildConversationContext($conversationHistory);

// ===== BUILD RESPONSE =====
if (!empty($topResults) && $topResults[0]['score'] > 2) {
    $response = buildSmartResponse($message, $topResults, $botName, $chatCtx);
    $confidence = $topResults[0]['score'] > 10 ? 'high' : ($topResults[0]['score'] > 5 ? 'medium' : 'low');
    $sources = [];
    foreach ($topResults as $r) { switch($r['type']) { case 'training': $sources[] = 'Training Data'; break; case 'error_code': $sources[] = 'Error Codes'; break; case 'knowledge': $sources[] = 'Knowledge Base'; break; case 'issue': $sources[] = 'Troubleshooting'; break; case 'step': $sources[] = 'Steps'; break; case 'command': $sources[] = 'Commands'; break; case 'tool': $sources[] = 'Tools'; break; } }
    $sources = array_values(array_unique($sources));
} else {
    $response = buildConversationalResponse($message, $category, $botName, $chatCtx, $conversationHistory);
    $confidence = 'medium';
    $sources = ['General IT Knowledge'];
}

// Save conversation
if (!(defined('DEMO_MODE') && DEMO_MODE)) {
    try { AIDatabase::insert('ai_conversation_logs', ['session_id' => $sessionId, 'user_id' => $userId, 'message' => $message, 'response' => $response, 'sources_used' => implode(',', $sources), 'confidence' => $confidence]); } catch (Exception $e) {}
}

// Enhance response with OpenAI (if configured) — uses DB results as grounding context
if (defined('AI_PROVIDER') && AI_PROVIDER === 'openai') {
    $ai = new AIService();
    if ($ai->isAvailable()) {
        $grounding = '';
        foreach ($topResults as $r) {
            $d = $r['data'];
            switch ($r['type']) {
                case 'training':   $grounding .= "\n\n[Training]: " . ($d['title'] ?? '') . "\n" . ($d['content'] ?? ''); break;
                case 'error_code': $grounding .= "\n\n[Error]: " . ($d['code'] ?? '') . " — " . ($d['title'] ?? '') . "\n" . ($d['description'] ?? ''); break;
                case 'knowledge':  $grounding .= "\n\n[Knowledge]: " . ($d['title'] ?? '') . "\n" . ($d['content'] ?? ''); break;
                case 'issue':      $grounding .= "\n\n[Issue]: " . ($d['title'] ?? '') . "\n" . ($d['description'] ?? ''); break;
                case 'step':       $grounding .= "\n\n[Step]: " . ($d['question'] ?? ''); break;
                case 'command':    $grounding .= "\n\n[Command]: " . ($d['name'] ?? '') . " — " . ($d['syntax'] ?? ''); break;
                case 'tool':       $grounding .= "\n\n[Tool]: " . ($d['name'] ?? '') . " — " . ($d['how_to_use'] ?? ''); break;
            }
        }
        $messages = [['role' => 'system', 'content' => $ai->getSystemPrompt($botName)]];
        foreach (array_slice($conversationHistory, -6) as $msg) {
            $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
        }
        $userMsg = $message;
        if ($grounding) {
            $userMsg .= "\n\nReference context from our knowledge base:\n" . substr($grounding, 0, 4000);
        }
        $messages[] = ['role' => 'user', 'content' => $userMsg];
        $aiResponse = $ai->chat($messages, 0.7, 1024);
        if ($aiResponse !== false) {
            $response = trim($aiResponse);
            if (empty($sources)) { $sources = ['AI Assistant']; }
            $confidence = 'high';
        }
    }
}

json_response(['response' => $response, 'confidence' => $confidence, 'sources' => $sources, 'session_id' => $sessionId, 'bot_name' => $botName, 'quota' => $quota]);


// ===== HELPER FUNCTIONS =====

function detectCategory($msg) {
    $categories = [
        'display' => ['display', 'monitor', 'screen', 'black screen', 'no image', 'flicker', 'resolution', 'vga', 'hdmi'],
        'power' => ['power', 'turn on', 'turning on', 'wont turn', 'startup', 'boot', 'psu', 'battery', 'charging', 'dead', 'shutdown', 'not turning'],
        'sound' => ['sound', 'audio', 'speaker', 'headphone', 'microphone', 'volume', 'no sound', 'mute', 'noise', 'noisy', 'clicking', 'buzzing', 'humming', 'whirring', 'crackling', 'beeping', 'whine', 'coil whine'],
        'network' => ['network', 'internet', 'wifi', 'ethernet', 'dns', 'ip address', 'dhcp', 'ping', 'connection', 'lan'],
        'printer' => ['printer', 'print', 'paper jam', 'ink', 'toner', 'cartridge'],
        'software' => ['windows', 'application', 'crash', 'freeze', 'slow', 'error', 'update', 'install', 'bsod', 'blue screen', 'driver'],
        'cctv' => ['camera', 'cctv', 'nvr', 'dvr', 'recording', 'surveillance'],
    ];
    foreach ($categories as $cat => $terms) {
        foreach ($terms as $term) {
            if (strpos($msg, $term) !== false) return $cat;
        }
    }
    return null;
}

function extractKeywords($msg) {
    $stopWords = ['the', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'can', 'to', 'of', 'in', 'for', 'on', 'with', 'at', 'by', 'from', 'it', 'its', 'my', 'your', 'our', 'their', 'this', 'that', 'a', 'an', 'and', 'or', 'but', 'not', 'so', 'if', 'than', 'too', 'very', 'just', 'about', 'i', 'me', 'we', 'us', 'he', 'she', 'him', 'her', 'they', 'them', 'what', 'how', 'when', 'where', 'why', 'which', 'who', 'having', 'problem', 'issue', 'help'];
    $words = preg_split('/\s+/', preg_replace('/[^a-z0-9\s]/', '', $msg));
    return array_values(array_filter($words, fn($w) => strlen($w) >= 2 && !in_array($w, $stopWords)));
}

function scoreItem($query, $keywords, $text, $type) {
    $lowerText = strtolower($text);
    $score = 0;
    if (strpos($lowerText, $query) !== false) $score += 15;
    foreach ($keywords as $kw) {
        if (strlen($kw) < 2) continue;
        if (strpos($lowerText, $kw) !== false) $score += 4;
        foreach (explode(' ', $lowerText) as $tw) { if (strlen($tw) >= 3 && strpos($tw, $kw) !== false) $score += 1; }
    }
    $titleWords = array_slice(explode(' ', $lowerText), 0, 5);
    foreach ($keywords as $kw) { if (in_array($kw, $titleWords)) $score += 6; }
    switch ($type) { case 'training': $score += 5; break; case 'error_code': $score += 2; break; case 'knowledge': $score += 3; break; case 'issue': $score += 2; break; }
    return $score;
}

// ===== SMART RESPONSE (when we find data) =====
function buildSmartResponse($query, $results, $botName, $context) {
    $training = array_filter($results, fn($r) => $r['type'] === 'training');
    $errorCodes = array_filter($results, fn($r) => $r['type'] === 'error_code');
    $kbArticles = array_filter($results, fn($r) => $r['type'] === 'knowledge');
    $issues = array_filter($results, fn($r) => $r['type'] === 'issue');
    $steps = array_filter($results, fn($r) => $r['type'] === 'step');
    $commands = array_filter($results, fn($r) => $r['type'] === 'command');

    $response = '';

    // Training data — build natural conversational response
    if (!empty($training)) {
        $tf = $training[0]['data'];
        if (!empty($tf)) {
            $tfTitle = $tf['title'] ?? '';
            $tfContent = $tf['content'] ?? '';
            $response .= pickRandom(["I found something in our docs that might help!", "Oh yeah, we have info on that!", "Let me check what we have... Found it!"]);
            $response .= "\n\n";
            // Extract actionable lines — skip metadata headers
            $tfLines = explode("\n", $tfContent);
            $actionLines = [];
            foreach ($tfLines as $line) {
                $line = trim($line);
                if (empty($line) || strlen($line) < 4) continue;
                $ll = strtolower($line);
                if (strpos($ll, 'field it') === 0) continue;
                if (strpos($ll, 'troubleshooting') === 0) continue;
                if (strpos($ll, 'category:') === 0) continue;
                if (strpos($ll, 'severity:') === 0) continue;
                if (strpos($ll, 'estimated time:') === 0) continue;
                if (strpos($ll, 'issue:') === 0) continue;
                if (strpos($ll, 'title:') === 0) continue;
                $actionLines[] = $line;
                if (count($actionLines) >= 10) break;
            }
            if (!empty($actionLines)) {
                $response .= implode("\n\n", $actionLines) . "\n\n";
            }
            if (!empty($errorCodes)) { $ec = $errorCodes[0]['data'] ?? []; $ecCode = $ec['code'] ?? ''; if (!empty($ecCode)) { $response .= "Related error: `{$ecCode}`" . (!empty($ec['title']) ? " — {$ec['title']}" : '') . "\n"; } }
            return $response;
        }
    }

    // Error codes
    if (!empty($errorCodes)) {
        $ec = $errorCodes[0]['data'];
        $code = $ec['code'] ?? '';
        $title = $ec['title'] ?? '';
        if (empty($code) && empty($title)) { /* skip empty records */ } else {
            $response .= pickRandom(["Ah, that error code — I know it well.", "Found it! That's a common one.", "Okay, here's what that error means:"]);
            $response .= "\n\n";
            if (!empty($code)) $response .= "### `{$code}`" . (!empty($title) ? " — {$title}" : '') . "\n\n";
            if (!empty($ec['description'])) $response .= "{$ec['description']}\n\n";
            if (!empty($ec['common_causes'])) $response .= "**Common causes:**\n{$ec['common_causes']}\n\n";
            if (!empty($ec['fix_steps'])) $response .= "**Here's what to try:**\n{$ec['fix_steps']}\n\n";
            return $response;
        }
    }

    // Knowledge base
    if (!empty($kbArticles)) {
        $kb = $kbArticles[0]['data'];
        $response .= pickRandom(["I found a guide that covers this!", "We have a write-up on this. Here's the gist:"]);
        $response .= "\n\n### {$kb['title']}\n\n";
        if (!empty($kb['root_cause'])) $response .= "**What's causing it:** {$kb['root_cause']}\n\n";
        if (!empty($kb['solution']) && strlen($kb['solution']) > 10) $response .= "**How to fix it:**\n{$kb['solution']}\n\n";
        if (!empty($kb['tools_used'])) $response .= "**You'll need:** {$kb['tools_used']}\n\n";
        // Add decision nodes
        if (!(defined('DEMO_MODE') && DEMO_MODE) && !empty($issues)) {
            try {
                $issId = $issues[0]['data']['id'] ?? null;
                if ($issId) {
                    $nodes = AIDatabase::fetchAll("SELECT * FROM decision_nodes WHERE issue_id = ? AND node_type = 'step' AND is_terminal = 0 ORDER BY step_order LIMIT 5", [$issId]);
                    if (!empty($nodes)) {
                        $response .= "**Walk-through:**\n";
                        foreach ($nodes as $i => $node) { $response .= ($i + 1) . ". {$node['question']}\n"; if (!empty($node['description'])) $response .= "   → {$node['description']}\n"; }
                    }
                }
            } catch (Exception $e) {}
        }
        return $response;
    }

    // Issue matches
    if (!empty($issues)) {
        $iss = $issues[0]['data'];
        $response .= pickRandom(["That sounds like something I've seen before.", "Okay, I think I know what's going on.", "I've got something on that."]);
        $response .= "\n\n### {$iss['title']}\n\n";
        if (!empty($iss['description'])) $response .= "{$iss['description']}\n\n";
        if (!empty($iss['symptoms'])) $response .= "**Symptoms:** {$iss['symptoms']}\n\n";
        if (!(defined('DEMO_MODE') && DEMO_MODE) && !empty($iss['id'])) {
            try {
                // Get questions first
                $questions = AIDatabase::fetchAll("SELECT * FROM decision_nodes WHERE issue_id = ? AND node_type = 'question' AND is_terminal = 0 ORDER BY step_order LIMIT 3", [$iss['id']]);
                if (!empty($questions)) {
                    $response .= "**Key questions to check:**\n";
                    foreach ($questions as $q) { $response .= "→ {$q['question']}\n"; }
                    $response .= "\n";
                }
                // Get steps
                $nodes = AIDatabase::fetchAll("SELECT * FROM decision_nodes WHERE issue_id = ? AND node_type = 'step' ORDER BY step_order LIMIT 5", [$iss['id']]);
                if (!empty($nodes)) {
                    $response .= "**Try these steps:**\n";
                    foreach ($nodes as $i => $node) {
                        $response .= ($i + 1) . ". **{$node['question']}**\n";
                        if (!empty($node['description'])) $response .= "   {$node['description']}\n";
                        if (!empty($node['expected_result'])) $response .= "   *Expected: {$node['expected_result']}*\n";
                    }
                    $response .= "\n";
                }
                // Get terminal results
                $terminals = AIDatabase::fetchAll("SELECT * FROM decision_nodes WHERE issue_id = ? AND is_terminal = 1 ORDER BY step_order LIMIT 3", [$iss['id']]);
                if (!empty($terminals)) {
                    foreach ($terminals as $t) {
                        $type = $t['result_type'] ?? 'solved';
                        $emoji = $type === 'solved' ? '✅' : ($type === 'escalate' ? '⚠️' : '🔧');
                        $response .= "{$emoji} **{$t['question']}**" . (!empty($t['result_solution']) ? " — {$t['result_solution']}" : '') . "\n";
                    }
                }
            } catch (Exception $e) {}
        }
        return $response;
    }

    // Step matches
    if (!empty($steps)) {
        $step = $steps[0]['data'];
        $response .= pickRandom(["Here's a step that might help:", "Try this:"]);
        $response .= "\n\n**{$step['question']}**\n\n";
        if (!empty($step['description'])) $response .= "{$step['description']}\n\n";
        if (!empty($step['expected_result'])) $response .= "**What should happen:** {$step['expected_result']}\n\n";
        if (!empty($step['tools_needed'])) $response .= "**Tools needed:** {$step['tools_needed']}\n\n";
        return $response;
    }

    // Command matches
    if (!empty($commands)) {
        $response .= "Here are some commands that might help:\n\n";
        foreach (array_slice($commands, 0, 2) as $cmd) { $c = $cmd['data']; $response .= "**`{$c['name']}`** — {$c['description']}\n"; if (!empty($c['syntax'])) $response .= "```\n{$c['syntax']}\n```\n\n"; }
        return $response;
    }

    return buildConversationalResponse($query, null, $botName, $context, []);
}

// ===== CONVERSATIONAL RESPONSE (no data match) =====
function buildConversationalResponse($query, $category, $botName, $context, $history) {
    $lowerQuery = strtolower($query);

    // Conversational patterns
    $thankPatterns = ['thank', 'thanks', 'thx', 'ty', 'appreciate'];
    foreach ($thankPatterns as $tp) {
        if (stripos($lowerQuery, $tp) !== false) {
            return pickRandom([
                "No problem at all! 😊 That's what I'm here for. Let me know if anything else comes up!",
                "Happy to help! Don't hesitate to ask if you run into anything else.",
                "You got it! If you need anything else, just holler. 👍"
            ]);
        }
    }

    $greetPatterns = ['hello', 'hi', 'hey', 'good morning', 'good afternoon'];
    foreach ($greetPatterns as $gp) {
        if (preg_match('/^' . preg_quote($gp) . '[\s!.]*$/i', $lowerQuery)) {
            return pickRandom([
                "Hey! 👋 What's going on? Tell me about the issue you're dealing with.",
                "Hi there! What can I help you with today?",
                "Hello! Got a tech problem? I'm all ears."
            ]);
        }
    }

    $helpPatterns = ['what can you do', 'features', 'capabilities', 'help me'];
    foreach ($helpPatterns as $hp) {
        if (strpos($lowerQuery, $hp) !== false) {
            return "I'm **{$botName}** — think of me as your IT support buddy! Here's what I can do:\n\n" .
                "🔍 **Find solutions** — Just describe your problem in plain English. I'll search our knowledge base, error code database, and troubleshooting guides to find the best answer.\n\n" .
                "💡 **Walk you through fixes** — I can give you step-by-step guidance for hardware, software, network, printer, and CCTV issues.\n\n" .
                "📋 **Look up error codes** — Got a BSOD code or Windows error? Tell me the code and I'll tell you what it means and how to fix it.\n\n" .
                "Just type what's happening and I'll do my best to help! No need for technical jargon — describe it like you'd tell a colleague.";
        }
    }

    $whoPatterns = ['who are you', 'what are you', 'your name'];
    foreach ($whoPatterns as $wp) {
        if (strpos($lowerQuery, $wp) !== false) {
            return "I'm **{$botName}**, your IT support assistant! I'm powered by our company's knowledge base — so I know our systems, our procedures, and common issues we deal with.\n\nI'm not perfect, but I'll do my best to help you figure things out. If I can't solve it, I'll point you to the right person or resource.\n\nSo — what's the problem you're dealing with?";
        }
    }

    // Category-specific conversational responses
    $catResponses = [
        'power' => function($q) {
            $device = 'your computer';
            if (strpos($q, 'laptop') !== false) $device = 'your laptop';
            if (strpos($q, 'desktop') !== false || strpos($q, 'pc') !== false) $device = 'your desktop';

            $noPowerReplies = [
                "Won't turn on? That's frustrating. Let's figure this out together.\n\n" .
                "First — when you press the power button, do you see **anything** happen? Like fans spinning, lights blinking, or any beeps?\n\n" .
                "If it's completely dead (no lights, no fans, nothing), then we're probably looking at a power delivery issue — either the outlet, the cable, or the power supply itself.\n\n" .
                "Can you try plugging something else into the same outlet — like a phone charger or a lamp — to make sure the outlet is working?",

                "Okay, {$device} not turning on. Before we dive deep, let's check the obvious stuff first:\n\n" .
                "1. Is the power cable **firmly plugged in** at both ends? (wall and computer)\n" .
                "2. If it's a desktop, check the **switch on the back of the power supply** — it should be in the \"I\" position\n" .
                "3. Try a **different outlet** if you can\n\n" .
                "Let me know what happens when you try these — especially whether you hear any fans or see any lights when you hit the power button.",
            ];
            return pickRandom($noPowerReplies);
        },
        'cctv' => function($q) {
            return pickRandom([
                "CCTV issue? Let me help you sort that out.\n\n" .
                "First, what's going on exactly? Is it:\n" .
                "→ A camera showing **no image** (black screen)?\n" .
                "→ The **recording stopped** or the NVR/DVR isn't saving?\n" .
                "→ You can't **access it remotely** from your phone/PC?\n" .
                "→ The image is **blurry or glitchy**?\n\n" .
                "Also, did anything change recently — like a power outage, network change, or someone messing with the setup?",

                "CCTV problems can be tricky. Tell me more about what you're seeing.\n\n" .
                "Quick checks:\n" .
                "→ Are the cameras powered on? Check if the IR lights come on at night\n" .
                "→ Is the NVR/DVR showing on the monitor?\n" .
                "→ Can you ping the NVR from your computer?\n" .
                "→ Are the cables (BNC/Ethernet) securely connected?\n\n" .
                "What's the symptom — is it all cameras or just one?",
            ]);
        },
        'display' => function($q) {
            $replies = [
                "No display? Okay, let's work through this.\n\n" .
                "When you turn on the computer, do you see the **power light** come on? And do you hear **fans spinning**?\n\n" .
                "If yes to both but the screen stays black, it's likely a cable or GPU issue. Here's what I'd try first:\n\n" .
                "→ Unplug the video cable (HDMI/DP/VGA) from **both ends** and plug it back in firmly\n" .
                "→ If that doesn't work, try a **different cable** or a **different port** on your GPU\n\n" .
                "Can you tell me: does the monitor show \"No Signal\" or is it just completely black?",

                "That sounds like a display issue. Let me help narrow it down.\n\n" .
                "Quick question: when you power on the PC, does the **monitor's power light** come on? And does it show any message like \"No Signal\"?\n\n" .
                "If the monitor is on but showing nothing, try these:\n" .
                "→ Make sure the video cable is plugged in securely\n" .
                "→ Try pressing the **input/source** button on the monitor to cycle through inputs\n" .
                "→ Swap the cable if you have a spare\n\n" .
                "If the PC itself seems dead (no lights, no fans), that's a different issue — let me know!",
            ];
            return pickRandom($replies);
        },
        'network' => function($q) {
            $replies = [
                "Network trouble — the worst! Let's get you back online.\n\n" .
                "First, the basics: is this affecting **just your device** or **everyone** on the network?\n\n" .
                "If it's just you, try this quick fix:\n" .
                "→ Open CMD as admin and run:\n" .
                "```\nipconfig /release\nipconfig /renew\nipconfig /flushdns\n```\n" .
                "That resets your network connection. If that doesn't work, we'll dig deeper.\n\n" .
                "Can you also check: does it say \"Connected, no internet\" or just \"No connection\"?",

                "Oh no, connectivity issues! I feel your pain.\n\n" .
                "Let's start simple — can you try **pinging** something? Open CMD and type:\n" .
                "```\nping 8.8.8.8\n```\n" .
                "If that works, it's a DNS issue (try `ipconfig /flushdns`).\n" .
                "If it doesn't work, it's a connection issue — check your cable or WiFi.\n\n" .
                "Also — are other devices on the same network working? That'll tell us if it's your PC or the whole network.",
            ];
            return pickRandom($replies);
        },
        'printer' => function($q) {
            $replies = [
                "Printer acting up? Let me guess — it's showing as offline or just not responding, right?\n\n" .
                "Here's the quickest fix that works most of the time:\n" .
                "→ Open CMD as admin\n" .
                "→ Type `net stop spooler` and press Enter\n" .
                "→ Then `net start spooler` and press Enter\n\n" .
                "That restarts the print service and fixes about half of printer issues.\n\n" .
                "If that doesn't work, tell me: what does the printer's display panel show? Any error messages or blinking lights?",

                "Printer problems — always at the worst time, right? 😅\n\n" .
                "Let's figure out what's going on. First, is the printer showing as **offline** in Windows, or is it online but just not printing?\n\n" .
                "If it's offline:\n" .
                "→ Check if it's powered on and connected (USB or WiFi)\n" .
                "→ Go to Settings > Devices > Printers — right-click it and see if \"Use Printer Offline\" is checked\n\n" .
                "If it's online but not printing:\n" .
                "→ Try restarting the print spooler (CMD: `net stop spooler` then `net start spooler`)\n\n" .
                "What does the printer's own display show?",
            ];
            return pickRandom($replies);
        },
        'software' => function($q) {
            if (strpos($q, 'slow') !== false || strpos($q, 'lag') !== false || strpos($q, 'freeze') !== false) {
                return pickRandom([
                    "Running slow? That's usually either too many things running at once, or something hogging your resources.\n\n" .
                    "Open **Task Manager** (`Ctrl+Shift+Esc`) and check the **Performance** tab — is your CPU or RAM maxed out?\n\n" .
                    "Also check the **Processes** tab — sort by CPU or Memory to see what's eating your resources.\n\n" .
                    "If something looks suspicious, try ending that task. If it's just everything being slow, try:\n" .
                    "→ Restart the computer (seriously, it fixes a lot)\n" .
                    "→ Run `sfc /scannow` in CMD to check for corrupted system files\n\n" .
                    "What does Task Manager show?",
                ]);
            }
            return pickRandom([
                "Software issues can be tricky, but let's narrow it down.\n\n" .
                "What exactly is happening? Is it:\n" .
                "→ An app that keeps crashing?\n" .
                "→ Windows showing an error?\n" .
                "→ Something just not working right?\n\n" .
                "If there's an error message, can you tell me what it says (or the error code)? That'll help me find the right fix.",
            ]);
        },
        'sound' => function($q) {
            return pickRandom([
                "Noisy sound when you open it? That's annoying. Let me help narrow it down.\n\n" .
                "First — where's the noise coming from? Is it:\n" .
                "→ A **buzzing/humming** from inside the case (could be a fan or hard drive)?\n" .
                "→ A **crackling/popping** from the speakers?\n" .
                "→ A **beeping** sound when you first turn it on?\n\n" .
                "Also, did this start recently or has it been happening for a while? And does it happen every time you turn it on, or only sometimes?",

                "Weird noise? That's usually a hardware thing — fans, hard drives, or coils.\n\n" .
                "Can you describe the sound? Like:\n" .
                "→ Is it a **clicking** noise? (That's usually a hard drive dying — backup your data ASAP)\n" .
                "→ A **whirring** that gets louder? (Fan bearings going bad)\n" .
                "→ A high-pitched **whine**? (Coil whine — annoying but not dangerous)\n" .
                "→ A **beeping** pattern? (Could be RAM or BIOS issue)\n\n" .
                "Tell me what it sounds like and I'll tell you what to do about it.",
            ]);
        },
        'cctv' => function($q) {
            return pickRandom([
                "CCTV issue? Let me help.\n\n" .
                "Is a **single camera** down, or are **multiple cameras** not recording?\n\n" .
                "If it's one camera:\n" .
                "→ Check if the camera has power (LED lights on?)\n" .
                "→ Can you ping its IP address?\n" .
                "→ Try power cycling it (unplug, wait 30 sec, plug back in)\n\n" .
                "If it's the whole system:\n" .
                "→ Check the NVR/DVR — is it on and showing an image?\n" .
                "→ Check if it has available storage space\n\n" .
                "What are you seeing on the NVR/DVR interface?",
            ]);
        },
    ];

    // Try category response
    if ($category && isset($catResponses[$category])) {
        return $catResponses[$category]($lowerQuery);
    }

    // Generic — ask naturally
    $genericReplies = [
        "Hmm, I'm not sure I fully understand what's going on. Can you tell me a bit more?\n\n" .
        "→ What **device** is having the problem? (Desktop, laptop, printer, etc.)\n" .
        "→ What **exactly** is happening? (Error message, blank screen, won't turn on, etc.)\n" .
        "→ When did it **start**? (Just now, today, after an update?)",

        "I want to help, but I need a bit more detail! 😊\n\n" .
        "Can you tell me:\n" .
        "1. What device is affected?\n" .
        "2. What's the symptom?\n" .
        "3. Any error codes or messages you've seen?",
    ];

    return pickRandom($genericReplies);
}

function pickRandom($arr) { return $arr[array_rand($arr)]; }

// ===== CONVERSATIONAL PATTERNS (checked FIRST before any database search) =====
function checkConversationalPatterns($lowerMsg, $originalMsg, $ctx = null) {
    // Greetings
    $greetings = ['^hi$', '^hey$', '^hello$', '^hi there$', '^hey there$', '^hello there$', '^yo$', '^sup$', '^good morning$', '^good afternoon$', '^good evening$', '^hola$', '^hi!', '^hey!', '^hello!', '^hi bot', '^hey bot', '^hello bot'];
    foreach ($greetings as $g) {
        if (preg_match('/' . $g . '/i', trim($lowerMsg))) {
            return pickRandom([
                "Hey! 👋 What's going on? Tell me about the issue you're dealing with.",
                "Hi there! What can I help you with today?",
                "Hello! Got a tech problem? I'm all ears.",
                "Hey! 👋 I'm here to help. What's up with your device?",
                "What's up! Need help with something tech-related?",
            ]);
        }
    }

    // Only short messages (under 5 chars) that aren't real questions — catch remaining short greetings
    if (strlen(trim($lowerMsg)) <= 3 && !preg_match('/no|ok|yes|off|on|bug|err|fix|slow|hot/i', $lowerMsg)) {
        return pickRandom([
            "Hey! 👋 What can I help you with?",
            "Hi there! Got a tech issue?",
            "What's up? Tell me what's going on.",
        ]);
    }

    // Thanks
    $thankPatterns = ['thank', 'thanks', 'thx', 'ty', 'appreciate', 'that helped', 'great', 'awesome', 'perfect', 'nice', 'cool'];
    foreach ($thankPatterns as $tp) {
        if (stripos($lowerMsg, $tp) !== false && strlen($lowerMsg) < 40) {
            return pickRandom([
                "No problem at all! 😊 That's what I'm here for. Let me know if anything else comes up!",
                "Happy to help! Don't hesitate to ask if you run into anything else.",
                "You got it! If you need anything else, just holler. 👍",
                "Anytime! I'm here 24/7 if you need me.",
            ]);
        }
    }

    // Yes/No answers (to follow-up questions)
    $yesPatterns = ['yes', 'yep', 'yeah', '^y$', 'it worked', 'its working', 'it works', 'fixed', 'solved', 'done', 'got it', 'works now', 'working now', 'problem solved'];
    foreach ($yesPatterns as $yp) {
        if (preg_match('/' . $yp . '/i', trim($lowerMsg))) {
            if ($ctx && !empty($ctx['last_step'])) {
                return pickRandom([
                    "Glad **{$ctx['last_step']}** worked! 🎉 Anything else you need help with?",
                    "Nice! **{$ctx['last_step']}** did the trick. Need help with anything else?",
                    "Awesome, that's sorted then! 👍 What else can I help with?",
                ]);
            }
            if ($ctx && !empty($ctx['topic'])) {
                return "Great! Glad the {$ctx['topic']} issue is sorted. Anything else?";
            }
            return pickRandom([
                "Awesome, glad it's sorted! 🎉 Let me know if anything else comes up.",
                "Great! Problem solved. Anything else you need help with?",
                "Nice one! 👍 If you run into anything else, I'm here.",
                "Perfect! Glad that did the trick. Need anything else?",
            ]);
        }
    }

    $noPatterns = ['^no$', '^nope', '^n$', 'didnt work', 'did not work', 'still broken', 'still not working', 'same issue', 'no change', 'did not fix', 'didnt fix'];
    foreach ($noPatterns as $np) {
        if (preg_match('/' . $np . '/i', trim($lowerMsg))) {
            if ($ctx && !empty($ctx['last_step'])) {
                return pickRandom([
                    "Okay, **{$ctx['last_step']}** didn't do it. Let's try the next approach. Can you tell me what happened when you tried it?",
                    "Alright, that didn't work. Let me think of another way to fix this. What exactly are you seeing now?",
                    "Hmm, that step didn't help. Let's try something different — tell me what's happening.",
                ]);
            }
            return pickRandom([
                "Okay, no worries. Let's try something else. Can you tell me what exactly happens when you try?",
                "Alright, that didn't do it. Let's dig deeper — what are you seeing now?",
                "Hmm, okay. Let me think of another approach. Can you describe what happens?",
                "Got it, that didn't work. Let's try the next thing — tell me what you're seeing.",
            ]);
        }
    }

    // Follow-up questions — only fire when there IS context
    $followUpPatterns = ['how to fix', 'how do i fix', 'what should i do', 'what do i do', 'tell me the steps', 'show me', 'next step', 'now what', 'and then', 'what else', 'how can i fix', 'solution', 'give me the solution'];
    $hasContext = !empty($ctx) && !empty($ctx['topic']);
    foreach ($followUpPatterns as $fup) {
        if ($hasContext && strpos($lowerMsg, $fup) !== false) {
            $topic = $ctx['topic'] ?? '';
            $device = $ctx['device'] ?? '';
            $turn = $ctx['turn_count'] ?? 0;
            $fixResponses = [
                'power' => "Let's go step by step for the power issue:\n\n**Step 1:** Check the outlet — plug a phone charger into the same outlet. Does it charge?\n\n**Step 2:** Check the PSU switch on the back — make sure it's in the **I** (on) position.\n\n**Step 3:** Unplug the power cable, hold the power button for 30 seconds, then plug back in and try.\n\n**Step 4:** Open the case and check that the 24-pin motherboard and 8-pin CPU power connectors are seated firmly.\n\n**Step 5:** If you have a spare PSU or multimeter, test the PSU output.\n\nWhich step should we start with?",
                'display' => "Here's how to fix the display issue:\n\n**Step 1:** Check monitor power light — if off, the monitor isn't getting power.\n\n**Step 2:** Replug the video cable on both ends.\n\n**Step 3:** Try a different cable or different port (HDMI, DP, VGA).\n\n**Step 4:** Reseat RAM — power off, remove RAM, reinsert firmly.\n\n**Step 5:** Reseat GPU — remove and reinsert the graphics card.\n\nWhich can you try right now?",
                'network' => "Here's how to fix network issues:\n\n**Quick Fix:**\nOpen CMD as admin and run:\n```\nipconfig /release\nipconfig /renew\nipconfig /flushdns\n```\n\n**If that doesn't work:**\n→ Ping 8.8.8.8 — if it works, it's DNS. If not, it's your connection.\n→ For WiFi: forget the network and reconnect\n→ For Ethernet: check cable at both ends\n→ Try netsh winsock reset in CMD, then restart\n\nWhich one should we try?",
                'printer' => "Let's fix the printer:\n\n**Step 1:** Restart Print Spooler — CMD admin: `net stop spooler` then `net start spooler`\n\n**Step 2:** Settings > Devices > Printers — right-click printer, make sure Use Printer Offline is NOT checked.\n\n**Step 3:** For paper jam — open printer panels and gently remove stuck paper.\n\n**Step 4:** Reinstall printer driver if still not working.\n\nWhich applies to your situation?",
            ];
            if (isset($fixResponses[$topic])) return $fixResponses[$topic];
            return "Let me walk you through the fix for your {$topic} issue. Can you describe the specific symptoms you're seeing?";
        }
    }

    // Help / capabilities
    $helpPatterns = ['what can you do', 'features', 'capabilities', 'help me', 'what do you do', 'how do you work', 'what are you'];
    foreach ($helpPatterns as $hp) {
        if (strpos($lowerMsg, $hp) !== false) {
            return "I'm **IT Bot** — think of me as your IT support buddy! Here's what I can do:\n\n"
                . "🔍 **Find solutions** — Describe your problem and I'll search our knowledge base, error codes, and troubleshooting guides.\n\n"
                . "💡 **Walk you through fixes** — Step-by-step guidance for hardware, software, network, printer, and CCTV issues.\n\n"
                . "📋 **Look up error codes** — Got a BSOD code or Windows error? Tell me the code and I'll explain it.\n\n"
                . "Just describe what's happening and I'll help you out!";
        }
    }

    // Who are you
    $whoPatterns = ['who are you', 'what are you', 'your name', 'whats your name', 'introduce yourself'];
    foreach ($whoPatterns as $wp) {
        if (strpos($lowerMsg, $wp) !== false) {
            return "I'm **IT Bot**, your IT support assistant! I know our systems, our procedures, and common issues we deal with.\n\nI'll do my best to help you figure things out. If I can't solve it, I'll point you to the right person.\n\nSo — what's the problem?";
        }
    }

    // No response — let the database search handle it
    return null;
}

// ===== BUILD CONVERSATION CONTEXT =====
function buildConversationContext($history) {
    $ctx = [
        'topic' => '',
        'device' => '',
        'last_step' => '',
        'last_question' => '',
        'last_user_answer' => '',
        'turn_count' => 0,
        'resolved' => false,
        'escalated' => false,
        'mentioned_codes' => [],
        'steps_tried' => [],
    ];
    if (empty($history)) return $ctx;
    $recent = array_slice($history, -8);
    $ctx['turn_count'] = count($recent);
    $allUserText = '';
    $allBotText = '';
    foreach ($recent as $msg) {
        $role = strtolower($msg['role'] ?? 'user');
        $text = $msg['content'] ?? '';
        $textLower = strtolower($text);
        if ($role === 'user') {
            $allUserText .= ' ' . $textLower;
            $ctx['last_user_answer'] = $text;
        } else {
            $allBotText .= ' ' . $textLower;
            $steps = [];
            if (preg_match_all('/(?:try|check|run|open|type|restart|click|unplug|reseat|test|swap|plug)\s+(.{10,80})/i', $text, $m)) {
                $steps = $m[0];
            }
            if (!empty($steps)) $ctx['last_step'] = end($steps);
            $lines = explode("\n", $text);
            foreach (array_reverse($lines) as $line) {
                $line = trim($line);
                if (!empty($line) && strpos($line, '?') !== false) {
                    $ctx['last_question'] = $line;
                    break;
                }
            }
        }
    }
    $combined = $allUserText . ' ' . $allBotText;
    $topicMap = ['display' => ['display', 'monitor', 'screen', 'black screen', 'no image', 'flicker', 'flickering', 'resolution', 'vga', 'hdmi'], 'power' => ['power', 'turn on', 'wont turn', 'startup', 'boot', 'psu', 'battery', 'charging', 'dead', 'not turning'], 'sound' => ['sound', 'audio', 'speaker', 'headphone', 'noise', 'clicking', 'buzzing', 'no sound', 'microphone'], 'network' => ['network', 'internet', 'wifi', 'ethernet', 'dns', 'connection', 'lan', 'ping'], 'printer' => ['printer', 'print', 'paper jam', 'ink', 'toner', 'cartridge', 'offline'], 'software' => ['windows', 'application', 'crash', 'freeze', 'slow', 'error', 'bsod', 'blue screen', 'driver', 'update'], 'cctv' => ['camera', 'cctv', 'nvr', 'dvr', 'recording', 'surveillance']];
    foreach ($topicMap as $topic => $terms) {
        foreach ($terms as $term) {
            if (strpos($combined, $term) !== false) { $ctx['topic'] = $topic; break 2; }
        }
    }
    $deviceMap = ['desktop' => ['desktop', 'pc', 'tower', 'workstation'], 'laptop' => ['laptop', 'notebook'], 'server' => ['server'], 'printer' => ['printer'], 'camera' => ['camera', 'cctv']];
    foreach ($deviceMap as $device => $terms) {
        foreach ($terms as $term) {
            if (strpos($combined, $term) !== false) { $ctx['device'] = $device; break 2; }
        }
    }
    if (preg_match('/(fixed|solved|working|resolved|done|works now)/i', $allUserText)) $ctx['resolved'] = true;
    if (preg_match('/(escalat|supervisor|manager|cant fix|give up|not working at all)/i', $allUserText)) $ctx['escalated'] = true;
    if (preg_match_all('/[A-Z][A-Z_]{5,}/', strtoupper($allUserText . ' ' . $allBotText), $m)) $ctx['mentioned_codes'] = array_unique($m[0]);
    return $ctx;
}

/**
 * Send a message to Groq (OpenAI-compatible) and return the reply, or false on any failure.
 * Used for conversational/small-talk messages that skip the knowledge-base search.
 */
function tryGroqChat($message, $conversationHistory) {
    if (!defined('AI_PROVIDER') || AI_PROVIDER !== 'openai') return false;
    if (!class_exists('AIService')) return false;
    try { $ai = new AIService(); } catch (Exception $e) { return false; }
    if (!$ai->isAvailable()) return false;
    $messages = [['role' => 'system', 'content' => $ai->getSystemPrompt('IT Bot')]];
    foreach (array_slice($conversationHistory, -6) as $m) {
        $messages[] = ['role' => $m['role'], 'content' => $m['content']];
    }
    $messages[] = ['role' => 'user', 'content' => $message];
    $reply = $ai->chat($messages, 0.7, 400);
    return ($reply !== false && trim($reply) !== '') ? trim($reply) : false;
}

/**
 * Is the current user an admin (unlimited AI)?
 */
function aiUserIsAdmin() {
    $role = strtolower((string)($_SESSION['role'] ?? $_SESSION['role_name'] ?? ''));
    if (in_array($role, ['admin', 'super_admin', 'superadmin'], true)) return true;
    try { if (class_exists('Auth') && Auth::hasPermission('*.*')) return true; } catch (Exception $e) {}
    return false;
}

/**
 * Quota info for the current user: admins are always unlimited.
 * Daily count comes from ai_conversation_logs (works across devices/browsers).
 */
function aiQuotaInfo($userId) {
    if (aiUserIsAdmin()) {
        return ['unlimited' => true, 'remaining_today' => null, 'daily_limit' => null, 'used_today' => null];
    }
    $limit = defined('AI_DAILY_LIMIT') ? (int)AI_DAILY_LIMIT : 0;
    if ($limit <= 0) {
        return ['unlimited' => true, 'remaining_today' => null, 'daily_limit' => null, 'used_today' => null];
    }
    $used = 0;
    if (!(defined('DEMO_MODE') && DEMO_MODE)) {
        try {
            $row = AIDatabase::fetch("SELECT COUNT(*) AS c FROM ai_conversation_logs WHERE user_id = ? AND created_at >= CURDATE()", [$userId]);
            $used = (int)($row['c'] ?? 0);
        } catch (Exception $e) { $used = 0; }
    }
    return ['unlimited' => false, 'remaining_today' => max(0, $limit - $used), 'daily_limit' => $limit, 'used_today' => $used];
}
