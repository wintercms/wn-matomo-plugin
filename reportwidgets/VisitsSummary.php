<?php

namespace Winter\Matomo\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use Illuminate\Support\Facades\Log;
use Throwable;
use Winter\Matomo\Classes\Exceptions\MatomoReportingException;
use Winter\Matomo\Classes\Helpers\ReportValueFormatter;
use Winter\Matomo\Classes\MatomoReportingService;
use Winter\Matomo\Classes\Traits\ReportWidgetConcerns;

/**
 * Native WinterCMS report widget that renders a Matomo visits summary.
 *
 * This widget retrieves analytics data via MatomoReportingService and exposes
 * normalized view variables for the backend partials.
 */
class VisitsSummary extends ReportWidgetBase
{
    use ReportWidgetConcerns;

    /**
     * Default widget alias used by WinterCMS dashboard internals.
     *
     * @var string
     */
    protected $defaultAlias = 'MatomoVisitsSummaryReportWidget';

    /**
     * Defines configurable properties shown in the dashboard widget settings.
     */
    public function defineProperties(): array
    {
        return array_merge([
            'title' => [
                'title'    => 'backend::lang.dashboard.widget_title_label',
                'type'     => 'string',
                'default'  => 'winter.matomo::lang.reportwidgets.visits_summary.title_default',
                'required' => true,
            ],
            'period' => [
                'title'       => 'winter.matomo::lang.reportwidgets.general.period',
                'description' => 'winter.matomo::lang.reportwidgets.general.period_desc',
                'type'        => 'dropdown',
                'options'     => 'winter.matomo::lang.reportwidgets.general.period_options',
                'default'     => 'week',
                'required'    => true,
            ],
            'date' => [
                'title'       => 'winter.matomo::lang.reportwidgets.general.date',
                'description' => 'winter.matomo::lang.reportwidgets.general.date_desc',
                'type'        => 'dropdown',
                'options'     => 'winter.matomo::lang.reportwidgets.general.date_options',
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
     * Loads and normalizes Matomo data for the widget view.
     *
     * When $bypassCache is true, the reporting cache is cleared first to force
     * a fresh API fetch.
     */
    protected function loadData(bool $bypassCache = false): void
    {
        $selectedPeriod = (string) $this->property('period', 'week');
        $selectedDate = (string) $this->property('date', 'last30');
        $selectedPeriodLabel = $this->translatedOptionLabel(
            'winter.matomo::lang.reportwidgets.general.period_options',
            $selectedPeriod
        );
        $selectedDateLabel = $this->translatedOptionLabel(
            'winter.matomo::lang.reportwidgets.general.date_options',
            $selectedDate
        );

        $this->vars['error'] = null;
        $this->vars['metrics'] = $this->emptyMetrics();
        $this->vars['refreshButton'] = $this->renderRefreshButton();
        $this->vars['widgetMeta'] = $this->renderWidgetMeta([
            [
                'label' => (string) trans('winter.matomo::lang.reportwidgets.general.selected_period'),
                'value' => (string) $selectedPeriodLabel,
            ],
            [
                'label' => (string) trans('winter.matomo::lang.reportwidgets.general.selected_date'),
                'value' => (string) $selectedDateLabel,
            ],
        ]);

        try {
            /** @var MatomoReportingService $service */
            $service = app(MatomoReportingService::class);

            $requestParams = [
                'period' => $selectedPeriod,
                'date' => $selectedDate,
            ];

            if ($bypassCache) {
                $service->clearCache(
                    $this->resolveCacheIdentifier($service, 'VisitsSummary.get', $requestParams, 'VisitsSummary')
                );
            }

            $response = $service->get('VisitsSummary.get', $requestParams, 'VisitsSummary');

            $metricsPayload = $this->extractMetricsPayload($response);

            $this->vars['metrics'] = [
                'nb_visits' => (int) ($metricsPayload['nb_visits'] ?? 0),
                'nb_uniq_visitors' => (int) ($metricsPayload['nb_uniq_visitors'] ?? 0),
                'nb_actions' => (int) ($metricsPayload['nb_actions'] ?? 0),
                'bounce_rate' => ReportValueFormatter::numericValue($metricsPayload['bounce_rate'] ?? 0),
                'nb_actions_per_visit' => ReportValueFormatter::numericValue($metricsPayload['nb_actions_per_visit'] ?? 0),
                'avg_time_on_site' => (int) ($metricsPayload['avg_time_on_site'] ?? 0),
            ];
        } catch (Throwable $exception) {
            $this->vars['error'] = $this->resolveUserErrorMessage($exception);

            if ($exception instanceof MatomoReportingException) {
                Log::warning('VisitsSummary widget failed to load Matomo data.', [
                    'widget' => static::class,
                    'error_code' => $exception->errorCode(),
                    'severity' => $exception->severity(),
                    'retryable' => $exception->isRetryable(),
                    'error' => $exception->getMessage(),
                    'context' => $exception->context(),
                ]);
            } else {
                Log::error('VisitsSummary widget failed with an unexpected exception.', [
                    'widget' => static::class,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }

    /**
     * Extracts and aggregates metrics from Matomo responses.
     *
     * Some responses are flat arrays (single bucket), while others are grouped by period/date (multiple buckets).
     * This helper normalizes both formats and aggregates multiple buckets into a single payload.
     *
     * @param array<string, mixed> $response
     * @return array<string, mixed>
     */
    protected function extractMetricsPayload(array $response): array
    {
        // If the response itself contains metrics, return it directly
        if (array_key_exists('nb_visits', $response)) {
            return $response;
        }

        // Collect all buckets that contain metrics
        $buckets = [];

        foreach ($response as $value) {
            if (is_array($value) && array_key_exists('nb_visits', $value)) {
                $buckets[] = $value;
            }
        }

        // If no buckets found, return empty
        if (empty($buckets)) {
            return [];
        }

        // If only one bucket, return it as-is
        if (count($buckets) === 1) {
            return $buckets[0];
        }

        // Multiple buckets: sum totals; weighted-average ratio/average fields by visits.
        $aggregated = [];
        $weightedNumerators = [
            'bounce_rate' => 0.0,
            'nb_actions_per_visit' => 0.0,
            'avg_time_on_site' => 0.0,
        ];
        $totalVisits = 0.0;

        foreach ($buckets as $bucket) {
            $bucketVisits = (float) ($bucket['nb_visits'] ?? 0);
            $totalVisits += $bucketVisits;

            foreach ($bucket as $key => $value) {
                if (is_numeric($value)) {
                    if (array_key_exists($key, $weightedNumerators)) {
                        $weightedNumerators[$key] += (float) $value * $bucketVisits;
                    } else {
                        if (!array_key_exists($key, $aggregated)) {
                            $aggregated[$key] = 0;
                        }
                        $aggregated[$key] += (float) $value;
                    }
                } elseif (!array_key_exists($key, $aggregated)) {
                    // Keep non-numeric fields from first bucket (representative value).
                    $aggregated[$key] = $value;
                }
            }
        }

        if ($totalVisits > 0) {
            $aggregated['bounce_rate'] = $weightedNumerators['bounce_rate'] / $totalVisits;
            $aggregated['nb_actions_per_visit'] = $weightedNumerators['nb_actions_per_visit'] / $totalVisits;
            $aggregated['avg_time_on_site'] = $weightedNumerators['avg_time_on_site'] / $totalVisits;
        }

        return $aggregated;
    }

    /**
     * Provides default metric values used before API data is loaded.
     *
     * @return array<string, int|float>
     */
    protected function emptyMetrics(): array
    {
        return [
            'nb_visits' => 0,
            'nb_uniq_visitors' => 0,
            'nb_actions' => 0,
            'bounce_rate' => 0.0,
            'nb_actions_per_visit' => 0.0,
            'avg_time_on_site' => 0,
        ];
    }
}
