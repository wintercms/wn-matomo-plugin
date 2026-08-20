<?php

namespace Winter\Matomo\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use Illuminate\Support\Facades\Log;
use Throwable;
use Winter\Matomo\Classes\Exceptions\MatomoReportingException;
use Winter\Matomo\Classes\Helpers\WidgetColorPalette;
use Winter\Matomo\Classes\MatomoReportingService;
use Winter\Matomo\Classes\Traits\ReportWidgetConcerns;

/**
 * Native WinterCMS report widget that renders Matomo device types and browser distribution as dual donut charts.
 */
class DevicesDetection extends ReportWidgetBase
{
    use ReportWidgetConcerns;

    /**
     * Default widget alias used by WinterCMS dashboard internals.
     *
     * @var string
     */
    protected $defaultAlias = 'MatomoDevicesDetectionReportWidget';

    /**
     * Defines configurable properties shown in the dashboard widget settings.
     */
    public function defineProperties(): array
    {
        return array_merge([
            'title' => [
                'title'    => 'backend::lang.dashboard.widget_title_label',
                'type'     => 'string',
                'default'  => 'winter.matomo::lang.reportwidgets.devices_detection.label',
                'required' => true,
            ],
            'date_range' => [
                'title'       => 'winter.matomo::lang.reportwidgets.general.date_range',
                'description' => 'winter.matomo::lang.reportwidgets.general.date_range_desc',
                'type'        => 'dropdown',
                'options'     => 'winter.matomo::lang.reportwidgets.general.date_range_options',
                'default'     => 'last30',
                'required'    => true,
            ],
        ], $this->getDisplayProperties());
    }

    /**
     * Loads shared CSS used by Matomo native report widgets.
     */
    protected function loadAssets(): void
    {
        $this->addCss('/plugins/winter/matomo/assets/css/reportwidgets.css');
    }

    /**
     * Renders the widget shell and initial content.
     */
    public function render(): string
    {
        return $this->makePartial('widget');
    }

    /**
     * Loads widget data asynchronously after placeholder render.
     *
     * @return array<string, string>
     */
    public function onLoad(): array
    {
        $this->loadData();

        return [
            '#' . $this->alias => $this->makePartial('report'),
        ];
    }

    /**
     * Handles AJAX refresh requests from the widget footer button.
     *
     * @return array<string, string>
     */
    public function onRefreshWidget(): array
    {
        $this->loadData(true);

        return [
            '#' . $this->alias => $this->makePartial('report'),
        ];
    }

    /**
     * Loads and normalizes Matomo data for the widget view via bulk request.
     */
    protected function loadData(bool $bypassCache = false): void
    {
        $selectedDateRange = (string) $this->property('date_range', 'last30');
        ['period' => $selectedPeriod, 'date' => $selectedDate] = $this->resolveDateRange($selectedDateRange);
        $selectedDateRangeLabel = $this->translatedOptionLabel(
            'winter.matomo::lang.reportwidgets.general.date_range_options',
            $selectedDateRange
        );

        $this->vars['error'] = null;
        $this->vars['deviceTypes'] = [];
        $this->vars['browsers'] = [];
        $this->vars['totalDeviceVisits'] = 0;
        $this->vars['totalBrowserVisits'] = 0;
        $this->vars['refreshButton'] = $this->renderRefreshButton();
        $this->vars['widgetMeta'] = $this->renderWidgetMeta([
            [
                'label' => (string) trans('winter.matomo::lang.reportwidgets.general.selected_date_range'),
                'value' => (string) $selectedDateRangeLabel,
            ],
        ]);

        try {
            /** @var MatomoReportingService $service */
            $service = app(MatomoReportingService::class);

            $deviceTypeParams = [
                'period' => $selectedPeriod,
                'date' => $selectedDate,
                'language' => 'en',
            ];
            $browserParams = [
                'period' => $selectedPeriod,
                'date' => $selectedDate,
                'language' => 'en',
            ];

            if ($bypassCache) {
                $service->clearCache($this->resolveCacheIdentifier($service, 'DevicesDetection.getType', $deviceTypeParams));
                $service->clearCache($this->resolveCacheIdentifier($service, 'DevicesDetection.getBrowsers', $browserParams));
            }

            // Fetch device types and browsers with separate requests
            $deviceTypeResponse = $service->get('DevicesDetection.getType', $deviceTypeParams);

            $browserResponse = $service->get('DevicesDetection.getBrowsers', $browserParams);

            // Process device types
            $deviceTypes = $this->normalizeDeviceTypes($deviceTypeResponse);
            $this->vars['deviceTypes'] = $deviceTypes;
            $this->vars['totalDeviceVisits'] = array_sum(array_column($deviceTypes, 'nb_visits'));

            // Process browsers
            $browsers = $this->normalizeBrowsers($browserResponse);
            $this->vars['browsers'] = $browsers;
            $this->vars['totalBrowserVisits'] = array_sum(array_column($browsers, 'nb_visits'));
        } catch (Throwable $exception) {
            $this->vars['error'] = $this->resolveUserErrorMessage($exception);

            if ($exception instanceof MatomoReportingException) {
                Log::warning('DevicesDetection widget failed to load Matomo data.', [
                    'widget' => static::class,
                    'error_code' => $exception->errorCode(),
                    'severity' => $exception->severity(),
                    'retryable' => $exception->isRetryable(),
                    'error' => $exception->getMessage(),
                    'context' => $exception->context(),
                ]);
            } else {
                Log::error('DevicesDetection widget failed with an unexpected exception.', [
                    'widget' => static::class,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }

    /**
     * Normalizes the Matomo device types response into rows for chart-pie.
     *
     * @param array $response Raw Matomo API response
     * @return array<int, array{label: string, nb_visits: int, color: string}>
     */
    protected function normalizeDeviceTypes(array $response): array
    {
        $aggregated = [];

        foreach ($this->flattenRows($response) as $item) {
            $metricValue = (int) ($item['nb_visits'] ?? $item['nb_actions'] ?? 0);
            if ($metricValue <= 0) {
                continue;
            }

            $rawLabel = trim((string) ($item['label'] ?? ''));
            $canonicalKey = WidgetColorPalette::canonicalDeviceKey($rawLabel);
            $label = $rawLabel !== ''
                ? $this->translateDeviceTypeLabel($canonicalKey)
                : (string) trans('winter.matomo::lang.reportwidgets.devices_detection.types.unknown');

            if (!isset($aggregated[$canonicalKey])) {
                $aggregated[$canonicalKey] = [
                    'label' => $label,
                    'nb_visits' => 0,
                    'color' => WidgetColorPalette::deviceType($canonicalKey),
                ];
            }

            $aggregated[$canonicalKey]['nb_visits'] += $metricValue;
        }

        $rows = array_values($aggregated);

        usort($rows, fn(array $a, array $b) => $b['nb_visits'] <=> $a['nb_visits']);

        return $rows;
    }

    /**
     * Normalizes the Matomo browsers response into rows for chart-pie.
     *
     * @param array $response Raw Matomo API response
     * @return array<int, array{label: string, nb_visits: int, color: string}>
     */
    protected function normalizeBrowsers(array $response): array
    {
        $aggregated = [];

        foreach ($this->flattenRows($response) as $item) {
            $metricValue = (int) ($item['nb_visits'] ?? $item['nb_actions'] ?? 0);
            if ($metricValue <= 0) {
                continue;
            }

            $rawLabel = trim((string) ($item['label'] ?? ''));
            $canonicalKey = WidgetColorPalette::canonicalBrowserKey($rawLabel);
            $label = $rawLabel !== ''
                ? $this->translateBrowserLabel($canonicalKey)
                : (string) trans('winter.matomo::lang.reportwidgets.devices_detection.browsers.unknown');

            if (!isset($aggregated[$canonicalKey])) {
                $aggregated[$canonicalKey] = [
                    'label' => $label,
                    'nb_visits' => 0,
                    'color' => WidgetColorPalette::browser($canonicalKey),
                ];
            }

            $aggregated[$canonicalKey]['nb_visits'] += $metricValue;
        }

        $rows = array_values($aggregated);

        usort($rows, fn(array $a, array $b) => $b['nb_visits'] <=> $a['nb_visits']);

        // Merge overflow beyond top 10 into an "Other" slice so the total stays accurate
        if (count($rows) > 10) {
            $topRows = array_slice($rows, 0, 10);
            $otherVisits = array_sum(array_column(array_slice($rows, 10), 'nb_visits'));

            $topRows[] = [
                'label' => $this->translateBrowserLabel('other'),
                'nb_visits' => $otherVisits,
                'color' => WidgetColorPalette::browser('other'),
            ];

            $rows = $topRows;
        }

        return $rows;
    }

    protected function translateDeviceTypeLabel(string $canonicalKey): string
    {
        $translationKey = 'winter.matomo::lang.reportwidgets.devices_detection.types.' . $canonicalKey;
        $translated = (string) trans($translationKey);

        if ($translated === $translationKey) {
            return ucwords(str_replace(['_', '-'], ' ', $canonicalKey));
        }

        return $translated;
    }

    protected function translateBrowserLabel(string $canonicalKey): string
    {
        $translationSlug = str_replace([' ', '-'], '_', $canonicalKey);
        $translationKey = 'winter.matomo::lang.reportwidgets.devices_detection.browsers.' . $translationSlug;
        $translated = (string) trans($translationKey);

        if ($translated === $translationKey) {
            return ucwords(str_replace(['_', '-'], ' ', $canonicalKey));
        }

        return $translated;
    }

    /**
     * Flattens top-level or grouped Matomo responses into raw rows.
     *
     * @param array $response Raw Matomo API response
     * @return array<int, array<string, mixed>>
     */
    protected function flattenRows(array $response): array
    {
        $rows = [];

        foreach ($response as $item) {
            if (!is_array($item)) {
                continue;
            }

            if ($this->isValidRow($item)) {
                $rows[] = $item;
                continue;
            }

            foreach ($item as $nestedItem) {
                if (is_array($nestedItem) && $this->isValidRow($nestedItem)) {
                    $rows[] = $nestedItem;
                }
            }
        }

        return $rows;
    }

    /**
     * Determines whether a response item is a valid data row.
     */
    protected function isValidRow(array $item): bool
    {
        return array_key_exists('label', $item)
            && (array_key_exists('nb_visits', $item) || array_key_exists('nb_actions', $item));
    }
}
