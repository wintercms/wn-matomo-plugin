<?php

namespace Winter\Matomo\Classes;

/**
 * Centralized color palette and semantic mappings for Matomo widgets.
 */
final class WidgetColorPalette
{
    /**
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

    public static function chartSeriesPrimary(): string
    {
        return '#1098ad';
    }

    public static function referrerType(string $label): string
    {
        return self::semanticColor($label, [
            'direct' => '#4c6ef5',
            'search' => '#2f9e44',
            'moteur' => '#2f9e44',
            'social' => '#e8590c',
            'reseau' => '#e8590c',
            'réseau' => '#e8590c',
            'website' => '#1098ad',
            'site' => '#1098ad',
            'campaign' => '#c2255c',
            'campagne' => '#c2255c',
        ]);
    }

    public static function deviceType(string $label): string
    {
        return self::semanticColor($label, [
            'desktop' => '#4c6ef5',
            'ordinateur' => '#4c6ef5',
            'mobile' => '#e8590c',
            'smartphone' => '#e8590c',
            'tablet' => '#2f9e44',
            'ipad' => '#2f9e44',
            'phablet' => '#7b61ff',
            'phablette' => '#7b61ff',
        ]);
    }

    public static function browser(string $label): string
    {
        return self::semanticColor($label, [
            'chrome' => '#4c6ef5',
            'firefox' => '#e8590c',
            'safari' => '#495057',
            'edge' => '#1098ad',
            'opera' => '#c2255c',
            'samsung' => '#7b61ff',
            'brave' => '#f08c00',
        ]);
    }

    /**
     * @param array<string, string> $semanticMap
     */
    private static function semanticColor(string $label, array $semanticMap): string
    {
        $normalized = strtolower(trim($label));

        foreach ($semanticMap as $needle => $color) {
            if (str_contains($normalized, $needle)) {
                return $color;
            }
        }

        return self::fallbackColor($label);
    }

    private static function fallbackColor(string $label): string
    {
        $normalized = strtolower(trim($label));

        if ($normalized === '') {
            return '#868e96';
        }

        $hash = (int) sprintf('%u', crc32($normalized));

        return self::PALETTE[$hash % count(self::PALETTE)];
    }
}
