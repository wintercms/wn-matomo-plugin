<?php

namespace Winter\Matomo\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use Illuminate\Support\Facades\Log;
use Throwable;
use Winter\Matomo\Classes\Exceptions\MatomoReportingException;
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
        return [
            'title' => [
                'title' => 'backend::lang.dashboard.widget_title_label',
                'type' => 'string',
                'default' => 'winter.matomo::lang.reportwidgets.devices_detection.title_default',
                'required' => true,
            ],
            'period' => [
                'title' => 'winter.matomo::lang.reportwidgets.devices_detection.period',
                'description' => 'winter.matomo::lang.reportwidgets.devices_detection.period_desc',
                'type' => 'dropdown',
                'options' => 'winter.matomo::lang.reportwidgets.devices_detection.period_options',
                'default' => 'week',
                'required' => true,
            ],
            'date' => [
                'title' => 'winter.matomo::lang.reportwidgets.devices_detection.date',
                'description' => 'winter.matomo::lang.reportwidgets.devices_detection.date_desc',
                'type' => 'dropdown',
                'options' => 'winter.matomo::lang.reportwidgets.devices_detection.date_options',
                'default' => 'last7',
                'required' => true,
            ],
        ];
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
        $this->loadData();

        return $this->makePartial('widget');
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
            '#' . $this->getId('content') => $this->makePartial('content'),
        ];
    }

    /**
     * Loads and normalizes Matomo data for the widget view via bulk request.
     */
    protected function loadData(bool $bypassCache = false): void
    {
        $selectedPeriod = (string) $this->property('period', 'week');
        $selectedDate = (string) $this->property('date', 'last7');

        $this->vars['error'] = null;
        $this->vars['deviceTypes'] = [];
        $this->vars['browsers'] = [];
        $this->vars['totalDeviceVisits'] = 0;
        $this->vars['totalBrowserVisits'] = 0;
        $this->vars['selectedPeriod'] = $selectedPeriod;
        $this->vars['selectedDate'] = $selectedDate;
        $this->vars['selectedPeriodLabel'] = $this->translatedOptionLabel(
            'winter.matomo::lang.reportwidgets.devices_detection.period_options',
            $selectedPeriod
        );
        $this->vars['selectedDateLabel'] = $this->translatedOptionLabel(
            'winter.matomo::lang.reportwidgets.devices_detection.date_options',
            $selectedDate
        );
        $this->vars['refreshButton'] = $this->renderRefreshButton([
            'widgetId' => $this->getId(),
        ]);

        try {
            /** @var MatomoReportingService $service */
            $service = app(MatomoReportingService::class);

            if ($bypassCache) {
                $service->clearCache();
            }

            // Fetch device types and browsers with separate requests
            $deviceTypeResponse = $service->get('DevicesDetection.getType', [
                'period' => $selectedPeriod,
                'date' => $selectedDate,
            ]);

            $browserResponse = $service->get('DevicesDetection.getBrowsers', [
                'period' => $selectedPeriod,
                'date' => $selectedDate,
            ]);

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

            $label = trim((string) ($item['label'] ?? ''));
            $label = $label !== ''
                ? $label
                : (string) trans('winter.matomo::lang.reportwidgets.devices_detection.types.unknown');

            if (!isset($aggregated[$label])) {
                $aggregated[$label] = [
                    'label' => $label,
                    'nb_visits' => 0,
                    'color' => $this->colorForDeviceType($label),
                ];
            }

            $aggregated[$label]['nb_visits'] += $metricValue;
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

            $label = trim((string) ($item['label'] ?? ''));
            $label = $label !== ''
                ? $label
                : (string) trans('winter.matomo::lang.reportwidgets.devices_detection.browsers.unknown');

            if (!isset($aggregated[$label])) {
                $aggregated[$label] = [
                    'label' => $label,
                    'nb_visits' => 0,
                    'color' => $this->colorForBrowser($label),
                ];
            }

            $aggregated[$label]['nb_visits'] += $metricValue;
        }

        $rows = array_values($aggregated);

        usort($rows, fn(array $a, array $b) => $b['nb_visits'] <=> $a['nb_visits']);

        // Limit to top 10 browsers for readability
        return array_slice($rows, 0, 10);
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

    /**
     * Maps device type labels to stable chart colors.
     */
    protected function colorForDeviceType(string $label): string
    {
        $normalized = strtolower(trim($label));

        return match (true) {
            str_contains($normalized, 'desktop') || str_contains($normalized, 'ordinateur') => '#4c6ef5',
            str_contains($normalized, 'mobile') || str_contains($normalized, 'smartphone') => '#e8590c',
            str_contains($normalized, 'tablet') || str_contains($normalized, 'ipad') => '#2f9e44',
            default => '#868e96',
        };
    }

    /**
     * Maps browser names to stable chart colors.
     */
    protected function colorForBrowser(string $label): string
    {
        $normalized = strtolower(trim($label));

        return match (true) {
            str_contains($normalized, 'chrome') => '#4285f4',
            str_contains($normalized, 'firefox') => '#ff7139',
            str_contains($normalized, 'safari') => '#555555',
            str_contains($normalized, 'edge') => '#1098ad',
            str_contains($normalized, 'opera') => '#c2255c',
            str_contains($normalized, 'samsung') => '#1f77e6',
            default => '#868e96',
        };
    }
}
