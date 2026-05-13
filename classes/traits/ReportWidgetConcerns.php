<?php

namespace Winter\Matomo\Classes\Traits;

use Throwable;
use Winter\Matomo\Classes\Exceptions\MatomoReportingException;
use Winter\Matomo\Classes\Exceptions\MatomoRequestTimeoutException;
use Winter\Matomo\Classes\MatomoReportingService;

/**
 * Shared behaviour for native Matomo report widgets.
 */
trait ReportWidgetConcerns
{
    /**
     * Converts a typed exception into an actionable user-facing error message.
     */
    protected function resolveUserErrorMessage(Throwable $exception): string
    {
        if ($exception instanceof MatomoRequestTimeoutException) {
            $context = $exception->context();
            $host = $this->extractHostFromExceptionContext($exception);
            $connectionError = (string) ($context['connection_error'] ?? '');

            if ($connectionError === 'dns_resolution' && $host !== null) {
                return (string) trans('winter.matomo::lang.reportwidgets.errors.host_unreachable', [
                    'host' => $host,
                ]);
            }

            if ($connectionError === 'connection_refused' && $host !== null) {
                return (string) trans('winter.matomo::lang.reportwidgets.errors.connection_refused', [
                    'host' => $host,
                ]);
            }
        }

        if ($exception instanceof MatomoReportingException) {
            return (string) trans($exception->userMessageKey());
        }

        return (string) trans('winter.matomo::lang.reportwidgets.errors.unexpected');
    }

    /**
     * Extracts a hostname from typed exception context if available.
     */
    protected function extractHostFromExceptionContext(MatomoReportingException $exception): ?string
    {
        $context = $exception->context();

        $host = $context['host'] ?? null;
        if (is_string($host) && $host !== '') {
            return $host;
        }

        $endpoint = $context['endpoint'] ?? null;
        if (!is_string($endpoint) || $endpoint === '') {
            return null;
        }

        $parsed = parse_url($endpoint, PHP_URL_HOST);

        return (is_string($parsed) && $parsed !== '') ? $parsed : null;
    }

    /**
     * Resolves the translated label for an option value from a lang options array.
     */
    protected function translatedOptionLabel(string $optionsLangKey, string|int $selectedValue): string
    {
        $options = trans($optionsLangKey);
        if (!is_array($options)) {
            return (string) $selectedValue;
        }

        return (string) ($options[$selectedValue] ?? $selectedValue);
    }

    /**
     * Resolves the canonical cache identifier for a widget request.
     *
     * @param array<string, mixed> $params
     */
    protected function resolveCacheIdentifier(MatomoReportingService $service, string $scope, array $params = []): string
    {
        return $service->getCacheIdentifier($scope, $params);
    }

    /**
     * Converts a duration in seconds to mm:ss format.
     */
    protected function formatDuration(int $seconds): string
    {
        $minutes = intdiv(max(0, $seconds), 60);
        $remainingSeconds = max(0, $seconds) % 60;

        return sprintf('%02d:%02d', $minutes, $remainingSeconds);
    }

    /**
     * Returns display control properties for use in defineProperties().
     *
     * Provides checkbox properties to control visibility of refresh button and widget metadata.
     *
     * @return array<string, array<string, mixed>>
     */
    protected function getDisplayProperties(): array
    {
        return [
            'show_refresh_button' => [
                'title' => 'winter.matomo::lang.reportwidgets.general.show_refresh_button',
                'type' => 'checkbox',
                'default' => true,
                'group' => 'winter.matomo::lang.reportwidgets.general.groups.display',
            ],
            'show_widget_meta' => [
                'title' => 'winter.matomo::lang.reportwidgets.general.show_widget_meta',
                'type' => 'checkbox',
                'default' => true,
                'group' => 'winter.matomo::lang.reportwidgets.general.groups.display',
            ],
        ];
    }

    /**
     * Render the refresh button partial
     *
     * @param bool $shouldRender Whether to render the button (defaults to property value)
     * @param array $data Additional data to pass to the partial
     * @return string
     */
    protected function renderRefreshButton(bool $shouldRender = null, array $data = []): string
    {
        // If not explicitly passed, check the property
        if ($shouldRender === null) {
            $shouldRender = (bool) $this->property('show_refresh_button', true);
        }

        if (!$shouldRender) {
            return '';
        }

        $defaultData = [
            'widgetId' => $this->getId(),
            'icon' => 'wn-icon-refresh',
            'label' => trans('winter.matomo::lang.reportwidgets.general.refresh'),
        ];

        return $this->makePartial(
            '$/winter/matomo/views/partials/_report_refresh_button',
            array_merge($defaultData, $data)
        );
    }

    /**
     * Render the refresh button partial (legacy compatibility).
     *
     * @param array $data Additional data to pass to the partial
     * @return string
     */
    /**
     * Render the widget meta partial.
     *
     * @param array<int, array{label: string, value: string|int|float|null, show?: bool}> $items
     * @param bool|null $shouldRender Whether to render the metadata (defaults to property value)
     * @return string
     */
    protected function renderWidgetMeta(array $items, bool $shouldRender = null): string
    {
        // If not explicitly passed, check the property
        if ($shouldRender === null) {
            $shouldRender = (bool) $this->property('show_widget_meta', true);
        }

        if (!$shouldRender) {
            return '';
        }

        $metaItems = $this->normalizeWidgetMetaItems($items);
        if ($metaItems === []) {
            return '';
        }

        return $this->makePartial('$/winter/matomo/views/partials/_report_widget_meta', [
            'items' => $metaItems,
        ]);
    }

    /**
     * Normalize widget meta items before rendering.
     *
     * @param array<int, array{label: string, value: string|int|float|null, show?: bool}> $items
     * @return array<int, array{label: string, value: string}>
     */
    protected function normalizeWidgetMetaItems(array $items): array
    {
        $metaItems = [];

        foreach ($items as $item) {
            $show = (bool) ($item['show'] ?? true);
            if (!$show) {
                continue;
            }

            $label = (string) ($item['label'] ?? '');
            $value = $item['value'] ?? null;

            if ($label === '' || $value === null || $value === '') {
                continue;
            }

            $metaItems[] = [
                'label' => $label,
                'value' => (string) $value,
            ];
        }

        return $metaItems;
    }
}
