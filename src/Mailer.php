<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

final class Mailer
{
    public static function quoteConfirmation(array $quote, int $photoCount): bool
    {
        $html = self::render('emails/quote-customer', ['quote' => $quote, 'photoCount' => $photoCount]);
        return self::send(
            $quote['email'],
            $quote['name'],
            'We hebben uw aanvraag ontvangen (' . $quote['reference'] . ')',
            $html
        );
    }

    public static function quoteNotification(array $quote, int $photoCount): bool
    {
        $html = self::render('emails/quote-company', ['quote' => $quote, 'photoCount' => $photoCount]);
        return self::send(
            (string) Env::get('MAIL_COMPANY_TO', 'info@example.test'),
            (string) Env::get('APP_NAME', 'Stevig'),
            'Nieuwe offerteaanvraag ' . $quote['reference'] . ': ' . $quote['service_name'] . ' in ' . $quote['town'],
            $html,
            $quote['email'],
            $quote['name']
        );
    }

    private static function render(string $template, array $data): string
    {
        ob_start();
        view($template, $data);
        return (string) ob_get_clean();
    }

    public static function send(string $toEmail, string $toName, string $subject, string $html, ?string $replyTo = null, ?string $replyName = null): bool
    {
        try {
            $mail = new PHPMailer(true);
            $mail->CharSet = 'UTF-8';
            $mail->setFrom((string) Env::get('MAIL_FROM', 'offerte@example.test'), (string) Env::get('MAIL_FROM_NAME', 'Stevig'));
            $mail->addAddress($toEmail, self::cleanName($toName));
            if ($replyTo !== null) {
                $mail->addReplyTo($replyTo, self::cleanName((string) $replyName));
            }
            $mail->Subject = self::cleanName($subject);
            $mail->isHTML(true);
            $mail->Body = $html;
            $mail->AltBody = trim(html_entity_decode(strip_tags(str_replace(['<br>', '</p>', '</tr>', '</h1>', '</h2>'], "\n", $html)), ENT_QUOTES, 'UTF-8'));

            if (Env::get('MAIL_DRIVER') === 'smtp' && !is_demo()) {
                $mail->isSMTP();
                $mail->Host = (string) Env::get('SMTP_HOST', '');
                $mail->Port = Env::int('SMTP_PORT', 587);
                $mail->SMTPAuth = true;
                $mail->Username = (string) Env::get('SMTP_USER', '');
                $mail->Password = (string) Env::get('SMTP_PASS', '');
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                return $mail->send();
            }

            $mail->preSend();
            $file = BASE_PATH . '/storage/mail/' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.eml';
            return file_put_contents($file, $mail->getSentMIMEMessage()) !== false;
        } catch (Throwable $e) {
            error_log('Mail failed: ' . $e->getMessage());
            return false;
        }
    }

    private static function cleanName(string $value): string
    {
        return trim(str_replace(["\r", "\n", "\0"], ' ', $value));
    }
}
