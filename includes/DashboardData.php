<?php
final class DashboardData {
    public static function load(): array {
        $scope = Auth::canViewAllTickets() ? '1=1' : 'ts.user_id = ?';
        $params = Auth::canViewAllTickets() ? [] : [(int)Auth::userId()];
        $today = date('Y-m-d 00:00:00');
        $tomorrow = date('Y-m-d 00:00:00', strtotime('+1 day'));
        $stats = Database::fetch("SELECT COUNT(*) AS total_sessions,
            COALESCE(SUM(ts.status IN ('solved','completed') AND COALESCE(ts.resolved_at,ts.ended_at,ts.created_at) >= ? AND COALESCE(ts.resolved_at,ts.ended_at,ts.created_at) < ?),0) AS solved_today,
            COALESCE(SUM(ts.status IN ('new','in_progress','unsolved','partial')),0) AS pending_tickets,
            COALESCE(SUM(ts.status = 'escalated'),0) AS escalated_count
            FROM troubleshooting_sessions ts WHERE $scope", array_merge([$today,$tomorrow],$params));
        $stats['kb_articles'] = Database::count('knowledge_articles', "deleted_at IS NULL AND status IN ('approved','published')");
        foreach ($stats as &$value) $value = (int)$value;
        unset($value);
        $recent = Database::fetchAll("SELECT ts.id,ts.status,ts.created_at,ts.device_type,ts.model AS device_model,
            COALESCE(NULLIF(ts.problem_description,''),ti.title,'Troubleshooting session') AS issue_title,
            tc.name AS category_name
            FROM troubleshooting_sessions ts
            LEFT JOIN troubleshooting_issues ti ON ti.id=ts.issue_id
            LEFT JOIN troubleshooting_categories tc ON tc.id=ti.category_id
            WHERE $scope ORDER BY ts.created_at DESC,ts.id DESC LIMIT 6",$params);
        $top = Database::fetchAll("SELECT ti.title,COUNT(*) AS cnt
            FROM troubleshooting_sessions ts JOIN troubleshooting_issues ti ON ti.id=ts.issue_id
            WHERE $scope AND ts.created_at >= ?
            GROUP BY ti.id,ti.title ORDER BY cnt DESC,ti.title LIMIT 5",array_merge($params,[date('Y-m-d 00:00:00',strtotime('monday this week'))]));
        return ['stats'=>$stats,'recent'=>$recent,'top_issues'=>$top,'updated_at'=>date(DATE_ATOM)];
    }
}
