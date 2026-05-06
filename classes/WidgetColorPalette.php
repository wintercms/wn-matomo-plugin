<?php

namespace Winter\Matomo\Classes;

/**
 * Centralized color palette and semantic mappings for Matomo widgets.
 *
 * Colors are keyed on stable, language-independent canonical keys.
 * Use the canonical*Key() helpers to normalize raw Matomo labels before
 * calling the color methods.
 */
final class WidgetColorPalette
{
    /**
     * Generic fallback palette (indexed, used for unknown categories).
     *
     * @var array<int, string>
     */
    private const PALETTE = [
        '#4c6ef5',
        '#1098ad',
        '#2f9e44',
        '#e8590c',
        '#c2255c',
        '#7b61ff',
        '#495057',
        '#74b816',
    ];

    /**
     * Canonical colors for referrer type keys.
     *
     * @var array<string, string>
     */
    private const REFERRER_COLORS = [
        'direct'   => '#4c6ef5',
        'search'   => '#2f9e44',
        'social'   => '#e8590c',
        'website'  => '#1098ad',
        'campaign' => '#c2255c',
    ];

    /**
     * Maps Matomo typeReferrer integer to a canonical string key.
     * Reference: https://developer.matomo.org/api-reference/reporting-api#referrers
     *
     * @var array<int, string>
     */
    private const REFERRER_TYPE_MAP = [
        1 => 'direct',
        2 => 'search',
        3 => 'website',
        6 => 'campaign',
        7 => 'social',
    ];

    /**
     * Canonical colors for device type keys.
     *
     * @var array<string, string>
     */
    private const DEVICE_COLORS = [
        'desktop'  => '#4c6ef5',
        'mobile'   => '#e8590c',
        'tablet'   => '#2f9e44',
        'phablet'  => '#7b61ff',
    ];

    /**
     * Maps lowercase Matomo device type English labels to canonical keys.
     * Matomo device labels come from the DeviceDetector library and are
     * generally stable English strings regardless of server locale.
     *
     * @var array<string, string>
     */
    private const DEVICE_TYPE_MAP = [
        'desktop'               => 'desktop',
        'smartphone'            => 'mobile',
        'tablet'                => 'tablet',
        'phablet'               => 'phablet',
        'feature phone'         => 'mobile',
        'smart tv'              => 'tv',
        'tv'                    => 'tv',
        'console'               => 'console',
        'portable media player' => 'media',
        'car browser'           => 'car',
        'camera'                => 'camera',
    ];

    /**
     * Canonical colors for browser keys (lowercased browser name).
     *
     * @var array<string, string>
     */
    private const BROWSER_COLORS = [
        'chrome'          => '#4c6ef5',
        'firefox'         => '#e8590c',
        'safari'          => '#495057',
        'microsoft edge'  => '#1098ad',
        'edge'            => '#1098ad',
        'opera'           => '#c2255c',
        'samsung browser' => '#7b61ff',
        'samsung'         => '#7b61ff',
        'brave'           => '#f08c00',
    ];

    // -------------------------------------------------------------------------
    // Color accessors — accept canonical keys only
    // -------------------------------------------------------------------------

    public static function chartSeriesPrimary(): string
    {
        return '#1098ad';
    }

    /**
     * Returns the color for a canonical referrer key (e.g. 'direct', 'search').
     */
    public static function referrerType(string $canonicalKey): string
    {
        return self::REFERRER_COLORS[$canonicalKey] ?? self::fallbackColor($canonicalKey);
    }

    /**
     * Returns the color for a canonical device type key (e.g. 'desktop', 'mobile').
     */
    public static function deviceType(string $canonicalKey): string
    {
        return self::DEVICE_COLORS[$canonicalKey] ?? self::fallbackColor($canonicalKey);
    }

    /**
     * Returns the color for a canonical browser key (lowercased browser name).
     */
    public static function browser(string $canonicalKey): string
    {
        return self::BROWSER_COLORS[$canonicalKey] ?? self::fallbackColor($canonicalKey);
    }

    // -------------------------------------------------------------------------
    // Normalization helpers — convert raw Matomo data to canonical keys
    // -------------------------------------------------------------------------

    /**
     * Derives the canonical referrer key from Matomo's stable typeReferrer integer.
     * Falls back to 'other' for unknown values.
     */
    public static function canonicalReferrerKey(int $typeReferrer): string
    {
        return self::REFERRER_TYPE_MAP[$typeReferrer] ?? 'other';
    }

    /**
     * Derives the canonical device type key from a raw Matomo device label.
     * Matomo device labels are English strings from the DeviceDetector library.
     */
    public static function canonicalDeviceKey(string $rawLabel): string
    {
        $normalized = strtolower(trim($rawLabel));

        return self::DEVICE_TYPE_MAP[$normalized] ?? $normalized;
    }

    /**
     * Derives the canonical browser key from a raw Matomo browser label.
     * Browser names from Matomo are generally not translated.
     */
    public static function canonicalBrowserKey(string $rawLabel): string
    {
        return strtolower(trim($rawLabel));
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    private static function fallbackColor(string $key): string
    {
        $normalized = strtolower(trim($key));

        if ($normalized === '') {
            return '#868e96';
        }

        $hash = (int) sprintf('%u', crc32($normalized));

        return self::PALETTE[$hash % count(self::PALETTE)];
    }
}
