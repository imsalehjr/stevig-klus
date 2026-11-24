<?php

declare(strict_types=1);

final class QuoteRequestService
{
    public static function create(array $data, array $photos): array
    {
        $pdo = Database::pdo();
        $dir = BASE_PATH . '/storage/uploads/requests';
        $stored = [];

        $pdo->beginTransaction();
        try {
            $pdo->prepare(
                'INSERT INTO quote_requests
                    (name, email, phone, street, house_number, postcode, town, service_id, service_name, description, preferred_period, privacy_consent_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
            )->execute([
                $data['name'], $data['email'], $data['phone'], $data['street'], $data['house_number'],
                $data['postcode'], $data['town'], $data['service_id'], $data['service_name'],
                $data['description'], $data['preferred_period'],
            ]);
            $id = (int) $pdo->lastInsertId();
            $reference = sprintf('STV-%s-%04d', date('Y'), $id);
            $pdo->prepare('UPDATE quote_requests SET reference = ? WHERE id = ?')->execute([$reference, $id]);

            $insertPhoto = $pdo->prepare(
                'INSERT INTO quote_request_photos (quote_request_id, file, mime, bytes, width, height) VALUES (?, ?, ?, ?, ?, ?)'
            );
            foreach ($photos as $photo) {
                $name = ImageUpload::store($photo, $dir);
                $stored[] = $dir . '/' . $name;
                $insertPhoto->execute([$id, $name, 'image/webp', $photo['bytes'], $photo['width'], $photo['height']]);
            }

            $pdo->prepare('INSERT INTO quote_request_status_log (quote_request_id, old_status, new_status) VALUES (?, NULL, ?)')
                ->execute([$id, 'new']);

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            foreach ($stored as $path) {
                @unlink($path);
            }
            foreach ($photos as $photo) {
                ImageUpload::discard($photo);
            }
            throw $e;
        }

        return ['id' => $id, 'reference' => $reference, 'photo_count' => count($stored)];
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM quote_requests WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function changeStatus(int $id, string $newStatus, int $adminId): bool
    {
        if (!array_key_exists($newStatus, quote_statuses())) {
            return false;
        }
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('SELECT status FROM quote_requests WHERE id = ? FOR UPDATE');
        $stmt->execute([$id]);
        $old = $stmt->fetchColumn();
        if ($old === false || $old === $newStatus) {
            $pdo->rollBack();
            return false;
        }
        $pdo->prepare('UPDATE quote_requests SET status = ? WHERE id = ?')->execute([$newStatus, $id]);
        $pdo->prepare('INSERT INTO quote_request_status_log (quote_request_id, admin_user_id, old_status, new_status) VALUES (?, ?, ?, ?)')
            ->execute([$id, $adminId, $old, $newStatus]);
        $pdo->commit();
        return true;
    }
}
