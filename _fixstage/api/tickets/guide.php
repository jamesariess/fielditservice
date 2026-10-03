<?php
/** API: Ticket Field Guide (GET)
 * Returns everything a technician needs for the ticket's issue, from real DB data:
 *  - steps:  the approved checklist steps for the issue (troubleshooting_steps)
 *  - tools:  merged tools (troubleshooting_issues.tools_needed + device_models.required_tools)
 *  - videos: service manual + per-step media links
 *  - tips:   safety warnings + known model issues
 * Nothing is invented — only what exists in the database is returned.
 */
if (!defined('APP_ROOT')) { define('APP_ROOT', dirname(dirname(__DIR__))); }
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/demo.php';
require_once APP_ROOT . '/includes/helpers.php';
if (!defined('DEMO_MODE') || !DEMO_MODE) { require_once APP_ROOT . '/includes/Database.php'; }
require_once APP_ROOT . '/includes/Auth.php';
Auth::start();
Auth::requireLogin();

header('Content-Type: application/json');

$issueId      = (int)($_GET['issue_id'] ?? 0);
$modelName    = trim($_GET['model'] ?? '');
$manufacturer = trim($_GET['manufacturer'] ?? '');

$out = [
    'steps' => [], 'tools' => [], 'videos' => [], 'tips' => [],
    'symptoms' => [], 'common_cause' => '', 'knowledge_id' => 0, 'knowledge_title' => '',
    'estimated_time' => '', 'description' => ''
];

if (!defined('DEMO_MODE') || !DEMO_MODE) {
    try {
        // ---- Issue: checklist steps + tools + safety tips ----
        if ($issueId > 0) {
            $issue = Database::fetch(
                "SELECT i.description, i.symptoms AS issue_symptoms, i.estimated_time,
                        i.tools_needed, i.safety_warnings,
                        ka.id AS knowledge_id, ka.title AS knowledge_title,
                        ka.symptoms AS knowledge_symptoms, ka.root_cause
                 FROM troubleshooting_issues i
                 LEFT JOIN knowledge_articles ka ON ka.id = (
                     SELECT linked.id FROM knowledge_articles linked
                     WHERE linked.troubleshooting_issue_id = i.id
                       AND linked.status = 'published' AND linked.deleted_at IS NULL
                     ORDER BY linked.updated_at DESC, linked.id DESC LIMIT 1
                 )
                 WHERE i.id = ? LIMIT 1",
                [$issueId]
            );
            if ($issue) {
                $out['description']    = (string)($issue['description'] ?? '');
                $out['estimated_time'] = (string)($issue['estimated_time'] ?? '');
                $out['common_cause']   = trim((string)($issue['root_cause'] ?? ''));
                $out['knowledge_id']   = (int)($issue['knowledge_id'] ?? 0);
                $out['knowledge_title']= (string)($issue['knowledge_title'] ?? '');
                $symptomsRaw = (string)(($issue['knowledge_symptoms'] ?? '') ?: ($issue['issue_symptoms'] ?? ''));
                $symptoms = json_decode($symptomsRaw, true);
                if (!is_array($symptoms)) { $symptoms = preg_split('/[,;|]+/', $symptomsRaw); }
                foreach ($symptoms ?: [] as $symptom) {
                    $symptom = trim((string)$symptom);
                    if ($symptom !== '' && !in_array($symptom, $out['symptoms'], true)) { $out['symptoms'][] = $symptom; }
                }
                $tools = json_decode((string)($issue['tools_needed'] ?? ''), true);
                if (is_array($tools)) {
                    foreach ($tools as $t) { $t = trim((string)$t); if ($t !== '' && !in_array($t, $out['tools'], true)) { $out['tools'][] = $t; } }
                }
                $warnings = json_decode((string)($issue['safety_warnings'] ?? ''), true);
                if (is_array($warnings)) {
                    foreach ($warnings as $w) {
                        $w = trim((string)$w);
                        if ($w !== '' && stripos($w, 'none') !== 0 && !in_array($w, $out['tips'], true)) { $out['tips'][] = $w; }
                    }
                }
            }
            $rows = Database::fetchAll(
                "SELECT step_number, title, risk_level, media_url FROM troubleshooting_steps WHERE issue_id = ? ORDER BY step_number ASC",
                [$issueId]
            ) ?: [];
            foreach ($rows as $r) {
                $out['steps'][] = [
                    'n'    => (int)$r['step_number'],
                    'title' => (string)$r['title'],
                    'risk' => (string)($r['risk_level'] ?? 'safe'),
                    'media' => (string)($r['media_url'] ?? ''),
                ];
                if (!empty($r['media_url'])) {
                    $out['videos'][] = ['label' => 'Step ' . $r['step_number'] . ' video', 'url' => (string)$r['media_url']];
                }
            }
        }

        // ---- Device model: service manual + required tools + known issues ----
        if ($modelName !== '') {
            $sql = "SELECT dm.service_manual_url, dm.required_tools, dm.known_issues
                    FROM device_models dm
                    LEFT JOIN manufacturers m ON dm.manufacturer_id = m.id
                    WHERE dm.name = ?";
            $params = [$modelName];
            if ($manufacturer !== '') { $sql .= " AND m.name = ?"; $params[] = $manufacturer; }
            $sql .= " LIMIT 1";
            $dm = Database::fetch($sql, $params);
            if ($dm) {
                if (!empty($dm['service_manual_url'])) {
                    $out['videos'][] = ['label' => 'Service manual', 'url' => (string)$dm['service_manual_url']];
                }
                $rawTools = (string)($dm['required_tools'] ?? '');
                $tools = json_decode($rawTools, true);
                if (!is_array($tools) && trim($rawTools) !== '') {
                    $tools = preg_split('/[,;\r\n]+/', $rawTools);
                }
                if (is_array($tools)) {
                    foreach ($tools as $t) { $t = trim((string)$t); if ($t !== '' && !in_array($t, $out['tools'], true)) { $out['tools'][] = $t; } }
                }
                foreach (preg_split('/\r?\n/', (string)($dm['known_issues'] ?? '')) as $k) {
                    $k = trim($k);
                    if ($k !== '' && !in_array($k, $out['tips'], true)) { $out['tips'][] = $k; }
                }
            }
        }
    } catch (Exception $e) {
        // Non-fatal: return whatever was collected.
    }
}

json_response($out);
