<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    return rtrim(Env::get('APP_URL', ''), '/') . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    $path = ltrim($path, '/');
    if (Env::get('APP_ENV', 'production') !== 'local' && preg_match('/\.(css|js)$/', $path)) {
        $min = preg_replace('/\.(css|js)$/', '.min.$1', $path);
        if (is_file(BASE_PATH . '/public/assets/' . $min)) {
            $path = $min;
        }
    }
    $file = BASE_PATH . '/public/assets/' . $path;
    $version = is_file($file) ? (string) filemtime($file) : '1';
    return url('assets/' . ltrim($path, '/')) . '?v=' . $version;
}

function upload_url(string $file): string
{
    return url('uploads/projects/' . rawurlencode($file));
}

function redirect(string $path): never
{
    header('Location: ' . (str_starts_with($path, 'http') ? $path : url($path)), true, 303);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function post_string(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim(str_replace("\0", '', $value)) : '';
}

function get_string(string $key): string
{
    $value = $_GET[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

function get_int(string $key): int
{
    $value = $_GET[$key] ?? '';
    return (is_string($value) && ctype_digit($value) && strlen($value) < 10) ? (int) $value : 0;
}

function post_int(string $key): int
{
    $value = $_POST[$key] ?? '';
    return (is_string($value) && ctype_digit($value) && strlen($value) < 10) ? (int) $value : 0;
}

function view(string $template, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require BASE_PATH . '/templates/' . $template . '.php';
}

function not_found(): never
{
    http_response_code(404);
    view('layout/header', ['title' => 'Pagina niet gevonden']);
    view('partials/not-found');
    view('layout/footer');
    exit;
}

function is_demo(): bool
{
    return Env::get('DEMO_MODE', 'false') === 'true';
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function ip_hash(): string
{
    return hash_hmac('sha256', client_ip(), (string) Env::get('APP_KEY'));
}

function format_date(?string $date, bool $withTime = false): string
{
    if ($date === null || $date === '') {
        return '';
    }
    $months = ['januari', 'februari', 'maart', 'april', 'mei', 'juni', 'juli', 'augustus', 'september', 'oktober', 'november', 'december'];
    $d = new DateTimeImmutable($date);
    $out = $d->format('j') . ' ' . $months[(int) $d->format('n') - 1] . ' ' . $d->format('Y');
    return $withTime ? $out . ', ' . $d->format('H:i') : $out;
}

function format_month(?string $date): string
{
    if ($date === null || $date === '') {
        return '';
    }
    $months = ['januari', 'februari', 'maart', 'april', 'mei', 'juni', 'juli', 'augustus', 'september', 'oktober', 'november', 'december'];
    $d = new DateTimeImmutable($date);
    return ucfirst($months[(int) $d->format('n') - 1]) . ' ' . $d->format('Y');
}

function slugify(string $text): string
{
    $text = strtr($text, ['ë' => 'e', 'é' => 'e', 'è' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u', 'á' => 'a', '&' => 'en']);
    $text = strtolower(trim((string) preg_replace('/[^A-Za-z0-9]+/', '-', $text), '-'));
    return substr($text, 0, 80) ?: 'item';
}

function stars(int $rating): string
{
    $rating = max(1, min(5, $rating));
    return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
}

function quote_statuses(): array
{
    return [
        'new' => 'Nieuw',
        'contacted' => 'Contact gehad',
        'quote_sent' => 'Offerte verstuurd',
        'accepted' => 'Geaccepteerd',
        'rejected' => 'Afgewezen',
        'done' => 'Afgerond',
    ];
}

function quote_periods(): array
{
    return [
        'asap' => 'Zo snel mogelijk',
        'within_1_month' => 'Binnen 1 maand',
        '1_3_months' => 'Over 1 tot 3 maanden',
        '3_6_months' => 'Over 3 tot 6 maanden',
        'flexible' => 'Flexibel / in overleg',
    ];
}

function company(): array
{
    return [
        'name' => 'Stevig Klus & Renovatie',
        'street' => 'Ambachtsweg 14',
        'postcode' => '5491 KT',
        'town' => 'Eikenrode',
        'phone' => '0123 456 789',
        'phone_link' => '+31123456789',
        'email' => 'info@stevig-klus.test',
        'kvk' => '00000000',
    ];
}
