<?php

declare(strict_types=1);

final class Validator
{
    private array $errors = [];

    public function required(string $field, string $value, string $label): self
    {
        if ($value === '' && !isset($this->errors[$field])) {
            $this->errors[$field] = "$label is verplicht.";
        }
        return $this;
    }

    public function length(string $field, string $value, int $min, int $max, string $label): self
    {
        $len = mb_strlen($value);
        if ($value !== '' && ($len < $min || $len > $max) && !isset($this->errors[$field])) {
            $this->errors[$field] = $min > 1
                ? "$label moet tussen $min en $max tekens lang zijn."
                : "$label mag maximaal $max tekens lang zijn.";
        }
        return $this;
    }

    public function email(string $field, string $value): self
    {
        if ($value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL) === false && !isset($this->errors[$field])) {
            $this->errors[$field] = 'Vul een geldig e-mailadres in.';
        }
        return $this;
    }

    public function phone(string $field, string $value): self
    {
        if ($value !== '' && !preg_match('/^\+?[0-9 ()-]{10,20}$/', $value) && !isset($this->errors[$field])) {
            $this->errors[$field] = 'Vul een geldig telefoonnummer in.';
        }
        return $this;
    }

    public function postcode(string $field, string $value): self
    {
        if ($value !== '' && !preg_match('/^[1-9][0-9]{3} ?[A-Za-z]{2}$/', $value) && !isset($this->errors[$field])) {
            $this->errors[$field] = 'Vul een geldige postcode in, bijvoorbeeld 5491 AB.';
        }
        return $this;
    }

    public function pattern(string $field, string $value, string $regex, string $message): self
    {
        if ($value !== '' && !preg_match($regex, $value) && !isset($this->errors[$field])) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    public function in(string $field, string $value, array $allowed, string $message): self
    {
        if (!in_array($value, $allowed, true) && !isset($this->errors[$field])) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    public function date(string $field, string $value, string $label): self
    {
        if ($value === '') {
            return $this;
        }
        $d = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if ((!$d || $d->format('Y-m-d') !== $value) && !isset($this->errors[$field])) {
            $this->errors[$field] = "$label is geen geldige datum.";
        }
        return $this;
    }

    public function add(string $field, string $message): self
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
