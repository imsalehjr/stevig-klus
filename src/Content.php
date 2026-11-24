<?php

declare(strict_types=1);

final class Content
{
    public static function services(bool $onlyActive = true): array
    {
        $sql = 'SELECT * FROM services' . ($onlyActive ? ' WHERE is_active = 1' : '') . ' ORDER BY sort_order, name';
        return Database::pdo()->query($sql)->fetchAll();
    }

    public static function areas(): array
    {
        return Database::pdo()->query('SELECT * FROM service_areas ORDER BY distance_km, sort_order, town')->fetchAll();
    }

    public static function projects(?int $serviceId = null, ?int $limit = null, bool $featuredFirst = false): array
    {
        $sql = 'SELECT p.*, s.name AS service_name, s.slug AS service_slug,
                    (SELECT file FROM project_photos f WHERE f.project_id = p.id AND f.kind = \'after\' ORDER BY f.sort_order, f.id LIMIT 1) AS cover_file,
                    (SELECT alt FROM project_photos f WHERE f.project_id = p.id AND f.kind = \'after\' ORDER BY f.sort_order, f.id LIMIT 1) AS cover_alt
                FROM projects p
                LEFT JOIN services s ON s.id = p.service_id
                WHERE p.is_published = 1';
        $params = [];
        if ($serviceId !== null) {
            $sql .= ' AND p.service_id = ?';
            $params[] = $serviceId;
        }
        $sql .= $featuredFirst ? ' ORDER BY p.is_featured DESC, p.completed_on DESC, p.id DESC' : ' ORDER BY p.completed_on DESC, p.id DESC';
        if ($limit !== null) {
            $sql .= ' LIMIT ' . max(1, $limit);
        }
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function project(string $slug): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT p.*, s.name AS service_name, s.slug AS service_slug
             FROM projects p LEFT JOIN services s ON s.id = p.service_id
             WHERE p.slug = ? AND p.is_published = 1'
        );
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }

    public static function photos(int $projectId): array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM project_photos WHERE project_id = ? ORDER BY sort_order, id');
        $stmt->execute([$projectId]);
        $out = ['before' => [], 'after' => []];
        foreach ($stmt->fetchAll() as $row) {
            $out[$row['kind']][] = $row;
        }
        return $out;
    }

    public static function featuredPair(): ?array
    {
        foreach (self::projects(null, 6, true) as $project) {
            $photos = self::photos((int) $project['id']);
            if ($photos['before'] && $photos['after']) {
                return ['project' => $project, 'before' => $photos['before'][0], 'after' => $photos['after'][0]];
            }
        }
        return null;
    }

    public static function reviews(?int $limit = null): array
    {
        $sql = 'SELECT r.*, s.name AS service_name FROM reviews r LEFT JOIN services s ON s.id = r.service_id
                WHERE r.is_published = 1 ORDER BY r.reviewed_on DESC, r.id DESC';
        if ($limit !== null) {
            $sql .= ' LIMIT ' . max(1, $limit);
        }
        return Database::pdo()->query($sql)->fetchAll();
    }

    public static function ratingSummary(): array
    {
        $row = Database::pdo()->query('SELECT COUNT(*) AS total, AVG(rating) AS avg FROM reviews WHERE is_published = 1')->fetch();
        return ['total' => (int) $row['total'], 'avg' => $row['avg'] !== null ? round((float) $row['avg'], 1) : 0.0];
    }
}
