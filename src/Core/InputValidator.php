<?php

namespace App\Core;

use InvalidArgumentException;

/**
 * Small input validation / parsing helpers.
 *
 * These methods are designed to be used by controllers to validate `$_GET`/
 * `$_POST` payloads and either return safe values or throw a validation error.
 */
final class InputValidator
{
    /**
     * Regex helper: returns true when the whole value matches the pattern.
     */
    public static function regex(?string $value, string $pattern): bool
    {
        if ($value === null) {
            return false;
        }

        // Apply the provided regex pattern on the value.
        return preg_match($pattern, $value) === 1;
    }

    /**
     * Safely read an integer from an input array.
     *
     * If the key is missing/empty/invalid/out of range, returns the default.
     */
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
        // Signed integer only (no letters or other symbols).
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

    /**
     * Require a string field and validate it against a regex.
     *
     * @throws InvalidArgumentException When required/too long/invalid.
     */
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

        // Validate the field format according to the expected pattern.
        if ($value !== '' && !self::regex($value, $pattern)) {
            throw new InvalidArgumentException("Le champ {$key} est invalide.");
        }

        return $value;
    }

    /**
     * Require an email field.
     *
     * @throws InvalidArgumentException When missing/invalid.
     */
    public static function requireEmail(array $input, string $key): string
    {
        $email = trim((string) ($input[$key] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Le champ {$key} est invalide.");
        }

        // Strict email format: local@domain.tld without spaces.
        if (!self::regex($email, '/^[^\s@]{1,64}@[A-Za-z0-9.-]{1,190}\.[A-Za-z]{2,63}$/')) {
            throw new InvalidArgumentException("Le champ {$key} est invalide.");
        }

        return $email;
    }

    /**
     * Require a date in ISO format (YYYY-MM-DD) and check it is a real calendar date.
     *
     * @throws InvalidArgumentException When missing/invalid.
     */
    public static function requireDate(array $input, string $key): string
    {
        $date = trim((string) ($input[$key] ?? ''));
        // ISO date format: YYYY-MM-DD.
        if (!self::regex($date, '/^\d{4}-\d{2}-\d{2}$/')) {
            throw new InvalidArgumentException("Le champ {$key} est invalide.");
        }

        [$year, $month, $day] = array_map('intval', explode('-', $date));
        if (!checkdate($month, $day, $year)) {
            throw new InvalidArgumentException("Le champ {$key} est invalide.");
        }

        return $date;
    }

    /**
     * Require a value to be part of an allowed set.
     *
     * @throws InvalidArgumentException When missing/not allowed.
     */
    public static function requireEnum(array $input, string $key, array $allowedValues): string
    {
        $value = (string) ($input[$key] ?? '');
        if (!in_array($value, $allowedValues, true)) {
            throw new InvalidArgumentException("Le champ {$key} est invalide.");
        }

        return $value;
    }

    /**
     * Read an enum-like value, returning a default if invalid.
     */
    public static function getEnum(array $input, string $key, array $allowedValues, string $default = ''): string
    {
        $value = (string) ($input[$key] ?? '');
        if (!in_array($value, $allowedValues, true)) {
            return $default;
        }

        return $value;
    }
}
