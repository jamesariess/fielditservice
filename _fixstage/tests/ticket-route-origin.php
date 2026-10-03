<?php
require_once dirname(__DIR__) . '/includes/TicketRouteOrigin.php';

class Database {
    public static ?array $stop = null;
    public static function fetch(string $sql, array $params): ?array {
        if (str_contains($sql, 'FROM users')) {
            return ['address'=>'Office', 'lat'=>'14.63', 'lng'=>'120.99'];
        }
        if ($params[0] !== 7 || !str_contains($sql, 'user_id = ?')) throw new Exception('Owner scope missing');
        $manila = new DateTimeImmutable('today', new DateTimeZone('Asia/Manila'));
        $utc = new DateTimeZone('UTC');
        if ($params[1] !== $manila->setTimezone($utc)->format('Y-m-d H:i:s')) throw new Exception('Wrong day start');
        if ($params[2] !== $manila->modify('+1 day')->setTimezone($utc)->format('Y-m-d H:i:s')) throw new Exception('Wrong day end');
        return self::$stop;
    }
}
function verify(bool $condition, string $message): void {
    if (!$condition) throw new Exception($message);
}
verify(TicketRouteOrigin::forUser(7)['address'] === 'Office', 'New day must use office');
Database::$stop = ['id'=>38, 'company_name'=>'Client', 'location'=>'MMC', 'address'=>'Makati Medical Center', 'latitude'=>'14.5544', 'longitude'=>'121.0148'];
$origin = TicketRouteOrigin::forUser(7);
verify($origin['address'] === 'Makati Medical Center' && $origin['ticket_id'] === 38, 'Completed stop must replace office');
Database::$stop['latitude'] = null;
Database::$stop['longitude'] = null;
verify(TicketRouteOrigin::forUser(7)['ticket_id'] === 38, 'Unresolved last stop must not silently use office');
Database::$stop = null;
verify(TicketRouteOrigin::forUser(7)['address'] === 'Office', 'No completion today must reset to office');
echo "Route origin checks passed\n";
