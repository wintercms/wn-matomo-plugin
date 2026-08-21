<?php

namespace Winter\Matomo\Classes\Helpers;

/**
 * Locale-aware formatter for report widget displayed values.
 *
 * Widgets should keep raw metrics in their payloads; partials call this helper
 * to render localized numbers, percentages and durations.
 */
final class ReportValueFormatter
{
    /**
     * Formats an integer-like value with locale-specific grouping.
     */
    public static function integer(int|float $value): string
    {
        return self::localizedNumber((float) $value, 0, 0);
    }

    /**
     * Formats a decimal value with locale-specific separators.
     */
    public static function decimal(int|float $value, int $maxFractionDigits = 2): string
    {
        return self::localizedNumber((float) $value, 0, max(0, $maxFractionDigits));
    }

    /**
     * Formats a percentage value from mixed numeric/string input.
     */
    public static function percentage(int|float|string|null $value, int $maxFractionDigits = 0): string
    {
        $numeric = self::numericValue($value);
        $suffix = str_starts_with(self::locale(), 'fr') ? ' %' : '%';

        return self::localizedNumber($numeric, 0, max(0, $maxFractionDigits)) . $suffix;
    }

    /**
     * Formats a duration in seconds using mm:ss.
     */
    public static function duration(int|float|string|null $seconds): string
    {
        $normalizedSeconds = max(0, (int) round(self::numericValue($seconds)));
        $minutes = intdiv($normalizedSeconds, 60);
        $remainingSeconds = $normalizedSeconds % 60;

        return sprintf('%02d:%02d', $minutes, $remainingSeconds);
    }

    /**
     * Extracts a numeric float from Matomo mixed values (e.g. "42%", "2,35").
     */
    public static function numericValue(int|float|string|null $value): float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (!is_string($value)) {
            return 0.0;
        }

        $normalized = trim($value);
        if ($normalized === '') {
            return 0.0;
        }

        $normalized = str_replace(["\u{00A0}", ' '], '', $normalized);
        $normalized = str_replace('%', '', $normalized);
        $normalized = str_replace(',', '.', $normalized);

        if (!is_numeric($normalized)) {
            return 0.0;
        }

        return (float) $normalized;
    }

    /**
     * Formats a number with NumberFormatter when available, fallback otherwise.
     */
    private static function localizedNumber(float $value, int $minFractionDigits = 0, int $maxFractionDigits = 0): string
    {
        $minFractionDigits = max(0, $minFractionDigits);
        $maxFractionDigits = max($minFractionDigits, $maxFractionDigits);

        if (class_exists('NumberFormatter')) {
            $formatter = new \NumberFormatter(self::locale(), \NumberFormatter::DECIMAL);
            $formatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $minFractionDigits);
            $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $maxFractionDigits);
            $formatted = $formatter->format($value);

            if (is_string($formatted)) {
                return $formatted;
            }
        }

        $locale = self::locale();
        $decimalSeparator = str_starts_with($locale, 'fr') ? ',' : '.';
        $thousandsSeparator = str_starts_with($locale, 'fr') ? ' ' : ',';

        return number_format($value, $maxFractionDigits, $decimalSeparator, $thousandsSeparator);
    }

    /**
     * Resolves current app locale for formatting.
     */
    private static function locale(): string
    {
        $locale = (string) app()->getLocale();

        return $locale !== ''
            ? str_replace('_', '-', strtolower($locale))
            : 'en';
    }
}
