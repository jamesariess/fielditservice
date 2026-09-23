<?php

class TicketRouteOrigin {
    public static function forUser(int $userId): array {
        $base = Database::fetch(
            "SELECT o.name AS company, l.name AS location, l.address, l.latitude AS lat, l.longitude AS lng
             FROM users u LEFT JOIN locations l ON l.id = u.location_id
             LEFT JOIN organizations o ON o.id = l.organization_id WHERE u.id = ?",
            [$userId]
        ) ?: [];
        $origin = array_merge(['company'=>'', 'location'=>'', 'address'=>'', 'lat'=>'', 'lng'=>''], $base);
        // Session timestamps are stored in UTC; the field-work day is Manila time.
        $start = new DateTimeImmutable('today', new DateTimeZone('Asia/Manila'));
        $end = $start->modify('+1 day');
        $utc = new DateTimeZone('UTC');
        $last = Database::fetch(
            "SELECT id, company_name, location, address, latitude, longitude FROM troubleshooting_sessions
             WHERE user_id = ? AND ended_at >= ? AND ended_at < ?
               AND status IN ('solved', 'partial')
             ORDER BY ended_at DESC, id DESC LIMIT 1",
            [$userId, $start->setTimezone($utc)->format('Y-m-d H:i:s'), $end->setTimezone($utc)->format('Y-m-d H:i:s')]
        );
        if ($last) {
            $origin = [
                'company' => $last['company_name'], 'location' => $last['location'],
                'address' => $last['address'] ?: $last['location'],
                'lat' => $last['latitude'], 'lng' => $last['longitude'],
                'ticket_id' => (int)$last['id'],
            ];
        }
        $origin['day'] = $start->format('Y-m-d');
        return $origin;
    }
}
