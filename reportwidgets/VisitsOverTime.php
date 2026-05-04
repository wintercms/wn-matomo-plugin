<?php

namespace Winter\Matomo\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use Illuminate\Support\Facades\Log;
use Throwable;
use Winter\Matomo\Classes\Exceptions\MatomoReportingException;
use Winter\Matomo\Classes\Exceptions\MatomoRequestTimeoutException;
use Winter\Matomo\Classes\MatomoReportingService;

/**
 * Native WinterCMS report widget that renders a Matomo visits evolution line chart.
 *
 * Retrieves daily visit counts via the Matomo Reporting API and passes them as
 * a WinterCMS native chart-line dataset (timestamp ms / value pairs).
 */
class VisitsOverTime extends ReportWidgetBase
{
    /**
     * Default widget alias used by WinterCMS dashboard internals.
     *
     * @var string
     */
    protected $defaultAlias = 'MatomoVisitsOverTimeReportWidget';

    /**
     * Defines configurable properties shown in the dashboard widget settings.
     */
    public function defineProperties(): array
    {
        return [
            'title' => [
                'title' => 'backend::lang.dashboard.widget_title_label',
                'type' => 'string',
                'default' => 'winter.matomo::lang.reportwidgets.visits_over_time.title_default',
                'required' => true,
            ],
            'days' => [
                'title' => 'winter.matomo::lang.reportwidgets.visits_over_time.days',
                'description' => 'winter.matomo::lang.reportwidgets.visits_over_time.days_desc',
                'type' => 'dropdown',
                'options' => 'winter.matomo::lang.reportwidgets.visits_over_time.days_options',
                'default' => '30',
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
     * Loads and normalizes Matomo data for the widget view.
     *
     * When $bypassCache is true, the reporting cache is cleared first to force
     * a fresh API fetch.
     */
    protected function loadData(bool $bypassCache = false): void
    {
        $days = (int) $this->property('days', 30);

        $this->vars['error'] = null;
        $this->vars['chartData'] = '';
        $this->vars['totalVisits'] = 0;
        $this->vars['days'] = $days;
        $this->vars['daysLabel'] = $this->translatedOptionLabel(
            'winter.matomo::lang.reportwidgets.visits_over_time.days_options',
            (string) $days
        );

        try {
            /** @var MatomoReportingService $service */
            $service = app(MatomoReportingService::class);

            if ($bypassCache) {
                $service->clearCache();
            }

            $response = $service->get('VisitsSummary.get', [
                'period' => 'day',
                'date'   => 'last' . $days,
            ]);

            [$chartData, $totalVisits] = $this->buildChartData($response);

            $this->vars['chartData'] = $chartData;
            $this->vars['totalVisits'] = $totalVisits;
        } catch (Throwable $exception) {
            $this->vars['error'] = $this->resolveUserErrorMessage($exception);

            if ($exception instanceof MatomoReportingException) {
                Log::warning('VisitsOverTime widget failed to load Matomo data.', [
                    'widget'     => static::class,
                    'error_code' => $exception->errorCode(),
                    'severity'   => $exception->severity(),
                    'retryable'  => $exception->isRetryable(),
                    'error'      => $exception->getMessage(),
                    'context'    => $exception->context(),
                ]);
            } else {
                Log::error('VisitsOverTime widget failed with an unexpected exception.', [
                    'widget' => static::class,
                    'error'  => $exception->getMessage(),
                ]);
            }
        }
    }

    /**
     * Converts a Matomo daily response into chart-line dataset string and total.
     *
     * Matomo returns either a flat array keyed by date (e.g. {"2026-04-28": {nb_visits: 12}})
     * or, for a single day, a flat metrics array. This helper normalises both.
     *
     * @param  array<string, mixed> $response
     * @return array{string, int}   [chartData string, totalVisits int]
     */
    protected function buildChartData(array $response): array
    {
        $pairs = [];
        $total = 0;

        // Single-day flat response — unlikely for period=day&date=lastN but handled for safety.
        if (array_key_exists('nb_visits', $response)) {
            $ts = strtotime('today') * 1000;
            $visits = (int) ($response['nb_visits'] ?? 0);
            $pairs[] = '[' . $ts . ', ' . $visits . ']';
            $total = $visits;

            return [implode(', ', $pairs), $total];
        }

        // Standard grouped-by-date response.
        foreach ($response as $dateStr => $metrics) {
            if (!is_string($dateStr) || !is_array($metrics)) {
                continue;
            }

            $ts = strtotime($dateStr);
            if ($ts === false) {
                continue;
            }

            $visits = (int) ($metrics['nb_visits'] ?? 0);
            $pairs[] = '[' . ($ts * 1000) . ', ' . $visits . ']';
            $total += $visits;
        }

        return [implode(', ', $pairs), $total];
    }

    /**
     * Converts technical exceptions to actionable user-facing messages.
     */
    protected function resolveUserErrorMessage(Throwable $exception): string
    {
        if ($exception instanceof MatomoRequestTimeoutException) {
            $context = $exception->context();
            $host = $this->extractHostFromExceptionContext($exception);
            $connectionError = (string) ($context['connection_error'] ?? '');

            if ($connectionError === 'dns_resolution' && $host !== null) {
                return (string) trans('winter.matomo::lang.reportwidgets.visits_summary.errors.host_unreachable', [
                    'host' => $host,
                ]);
            }

            if ($connectionError === 'connection_refused' && $host !== null) {
                return (string) trans('winter.matomo::lang.reportwidgets.visits_summary.errors.connection_refused', [
                    'host' => $host,
                ]);
            }
        }

        if ($exception instanceof MatomoReportingException) {
            return (string) trans($exception->userMessageKey());
        }

        return (string) trans('winter.matomo::lang.reportwidgets.visits_summary.errors.unexpected');
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
     * Resolves a translated options array and returns the label for a selected key.
     */
    protected function translatedOptionLabel(string $optionsLangKey, string $selectedValue): string
    {
        $options = trans($optionsLangKey);
        if (!is_array($options)) {
            return $selectedValue;
        }

        return (string) ($options[$selectedValue] ?? $selectedValue);
    }
}
