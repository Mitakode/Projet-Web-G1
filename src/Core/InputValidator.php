<?php

namespace App\Core;

use InvalidArgumentException;

final class InputValidator
{
    public static function regex(?string $value, string $pattern): bool
    {
        if ($value === null) {
            return false;
        }

        // Applique le motif regex fourni sur la valeur.
        return preg_match($pattern, $value) === 1;
    }

    public static function getInt(
        array $input,
        string $key,
        int $default = 0,
        ?int $min = null,
        ?int $max = null
    ): int {
        $raw = $input[$key] ?? null;
        if ($raw === null || $raw === '') {
            return $default;
        }

        $value = (string) $raw;
        // Entier signé uniquement (pas de lettres ni symbole).
        if (!self::regex($value, '/^-?\d+$/')) {
            return $default;
        }

        $intValue = (int) $value;
        if ($min !== null && $intValue < $min) {
            return $default;
        }
        if ($max !== null && $intValue > $max) {
            return $default;
        }

        return $intValue;
    }

    public static function requireString(
        array $input,
        string $key,
        string $pattern,
        int $maxLen = 255,
        bool $allowEmpty = false
    ): string {
        $value = trim((string) ($input[$key] ?? ''));
        $len = mb_strlen($value);

        if (!$allowEmpty && $value === '') {
            throw new InvalidArgumentException("Le champ {$key} est requis.");
        }

        if ($len > $maxLen) {
            throw new InvalidArgumentException("Le champ {$key} est trop long.");
        }

        // Vérifie le format texte du champ selon le motif attendu.
        if ($value !== '' && !self::regex($value, $pattern)) {
            throw new InvalidArgumentException("Le champ {$key} est invalide.");
        }

        return $value;
    }

    public static function requireEmail(array $input, string $key): string
    {
        $email = trim((string) ($input[$key] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Le champ {$key} est invalide.");
        }

        // Email strict: local@domaine.tld sans espaces.
        if (!self::regex($email, '/^[^\s@]{1,64}@[A-Za-z0-9.-]{1,190}\.[A-Za-z]{2,63}$/')) {
            throw new InvalidArgumentException("Le champ {$key} est invalide.");
        }

        return $email;
    }

    public static function requireDate(array $input, string $key): string
    {
        $date = trim((string) ($input[$key] ?? ''));
        // Date au format ISO YYYY-MM-DD.
        if (!self::regex($date, '/^\d{4}-\d{2}-\d{2}$/')) {
            throw new InvalidArgumentException("Le champ {$key} est invalide.");
        }

        [$year, $month, $day] = array_map('intval', explode('-', $date));
        if (!checkdate($month, $day, $year)) {
            throw new InvalidArgumentException("Le champ {$key} est invalide.");
        }

        return $date;
    }

    public static function requireEnum(array $input, string $key, array $allowedValues): string
    {
        $value = (string) ($input[$key] ?? '');
        if (!in_array($value, $allowedValues, true)) {
            throw new InvalidArgumentException("Le champ {$key} est invalide.");
        }

        return $value;
    }

    public static function getEnum(array $input, string $key, array $allowedValues, string $default = ''): string
    {
        $value = (string) ($input[$key] ?? '');
        if (!in_array($value, $allowedValues, true)) {
            return $default;
        }

        return $value;
    }
}
