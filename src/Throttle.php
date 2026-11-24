<?php

declare(strict_types=1);

final class Throttle
{
    public static function tooMany(string $action, int $max, int $minutes): bool
    {
        $stmt = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM throttle_events WHERE action = ? AND ip_hash = ? AND created_at > NOW() - INTERVAL ? MINUTE'
        );
        $stmt->execute([$action, ip_hash(), $minutes]);
        return (int) $stmt->fetchColumn() >= $max;
    }

    public static function hit(string $action): void
    {
        Database::pdo()->prepare('INSERT INTO throttle_events (action, ip_hash) VALUES (?, ?)')
            ->execute([$action, ip_hash()]);
    }

    public static function clear(string $action): void
    {
        Database::pdo()->prepare('DELETE FROM throttle_events WHERE action = ? AND ip_hash = ?')
            ->execute([$action, ip_hash()]);
    }

    public static function prune(): void
    {
        Database::pdo()->exec('DELETE FROM throttle_events WHERE created_at < NOW() - INTERVAL 1 DAY');
    }
}
