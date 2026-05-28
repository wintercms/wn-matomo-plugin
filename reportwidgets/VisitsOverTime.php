<?php

namespace Winter\Matomo\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use Illuminate\Support\Facades\Log;
use Throwable;
use Winter\Matomo\Classes\Exceptions\MatomoReportingException;
use Winter\Matomo\Classes\Helpers\ReportValueFormatter;
use Winter\Matomo\Classes\Helpers\WidgetColorPalette;
use Winter\Matomo\Classes\MatomoReportingService;
use Winter\Matomo\Classes\Traits\ReportWidgetConcerns;

/**
 * Native WinterCMS report widget that renders a Matomo visits evolution line chart.
 *
 * Retrieves daily visit counts via the Matomo Reporting API and passes them as
 * a WinterCMS native chart-line dataset (timestamp ms / value pairs).
 */
class VisitsOverTime extends ReportWidgetBase
{
    use ReportWidgetConcerns;

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
        return array_merge([
            'title' => [
                'title'    => 'backend::lang.dashboard.widget_title_label',
                'type'     => 'string',
                'default'  => 'winter.matomo::lang.reportwidgets.visits_over_time.label',
                'required' => true,
            ],
            'date' => [
                'title'       => 'winter.matomo::lang.reportwidgets.visits_over_time.days',
                'description' => 'winter.matomo::lang.reportwidgets.visits_over_time.days_desc',
                'type'        => 'dropdown',
                'options'     => 'winter.matomo::lang.reportwidgets.general.date_options',
                'default'     => 'last30',
                'required'    => true,
            ],
            'metric_nb_visits' => [
                'title'   => 'winter.matomo::lang.reportwidgets.visits_over_time.metrics.nb_visits',
                'type'    => 'checkbox',
                'default' => true,
                'group'   => 'winter.matomo::lang.reportwidgets.general.groups.metrics',
            ],
            'metric_nb_pageviews' => [
                'title'   => 'winter.matomo::lang.reportwidgets.visits_over_time.metrics.nb_pageviews',
                'type'    => 'checkbox',
                'default' => true,
                'group'   => 'winter.matomo::lang.reportwidgets.general.groups.metrics',
            ],
            'metric_nb_actions' => [
                'title'   => 'winter.matomo::lang.reportwidgets.visits_over_time.metrics.nb_actions',
                'type'    => 'checkbox',
                'default' => false,
                'group'   => 'winter.matomo::lang.reportwidgets.general.groups.metrics',
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
        // $days          = (string) $this->property('days', 'last30');
        $showVisits    = (bool) $this->property('metric_nb_visits', true);
        $showPageviews = (bool) $this->property('metric_nb_pageviews', true);
        $showHits      = (bool) $this->property('metric_nb_actions', false);
        $selectedDate = (string) $this->property('date', 'last30');
        $selectedDateLabel = $this->translatedOptionLabel(
            'winter.matomo::lang.reportwidgets.general.date_options',
            $selectedDate
        );

        // $daysLabel = $this->translatedOptionLabel(
        //     'winter.matomo::lang.reportwidgets.general.date_options',
        //     (string) $days
        // );

        $this->vars['error']         = null;
        $this->vars['chartDatasets'] = [];
        $this->vars['chartOptions']  = $this->buildChartOptions();
        $this->vars['totalVisits']   = 0;
        $this->vars['refreshButton'] = $this->renderRefreshButton();
        $this->vars['widgetMeta']    = $this->renderWidgetMeta([
            ['label' => (string) trans('winter.matomo::lang.reportwidgets.general.date_label'), 'value' => (string) $selectedDateLabel],
        ]);

        try {
            /** @var MatomoReportingService $service */
            $service = app(MatomoReportingService::class);

            $summaryParams = [
                'period' => 'day',
                'date'   => $selectedDate,
            ];
            $actionsParams = [
                'period' => 'day',
                'date'   => $selectedDate,
            ];

            if ($bypassCache) {
                if ($showVisits || $showHits) {
                    $service->clearCache($this->resolveCacheIdentifier($service, 'VisitsSummary.get', $summaryParams));
                }

                if ($showPageviews) {
                    $service->clearCache($this->resolveCacheIdentifier($service, 'Actions.get', $actionsParams));
                }
            }

            $datasets  = [];
            $metaItems = [];

            if ($showVisits || $showHits) {
                $summaryResponse = $service->get('VisitsSummary.get', $summaryParams);

                if ($showVisits) {
                    [$data, $total] = $this->buildDatasetFromResponse($summaryResponse, 'nb_visits');
                    $datasets[] = [
                        'data'  => $data,
                        'color' => WidgetColorPalette::metric('nb_visits'),
                        'label' => (string) trans('winter.matomo::lang.reportwidgets.visits_over_time.metrics.nb_visits'),
                    ];
                    $this->vars['totalVisits'] = $total;
                    $metaItems[] = [
                        'label' => (string) trans('winter.matomo::lang.reportwidgets.visits_over_time.total_visits'),
                        'value' => ReportValueFormatter::integer($total),
                        'show'  => !empty($total),
                    ];
                }

                if ($showHits) {
                    [$data, $total] = $this->buildDatasetFromResponse($summaryResponse, 'nb_actions');
                    $datasets[] = [
                        'data'  => $data,
                        'color' => WidgetColorPalette::metric('nb_actions'),
                        'label' => (string) trans('winter.matomo::lang.reportwidgets.visits_over_time.metrics.nb_actions'),
                    ];
                    $metaItems[] = [
                        'label' => (string) trans('winter.matomo::lang.reportwidgets.visits_over_time.total_hits'),
                        'value' => ReportValueFormatter::integer($total),
                        'show'  => !empty($total),
                    ];
                }
            }

            if ($showPageviews) {
                $actionsResponse = $service->get('Actions.get', $actionsParams);
                [$data, $total] = $this->buildDatasetFromResponse($actionsResponse, 'nb_pageviews');
                $datasets[] = [
                    'data'  => $data,
                    'color' => WidgetColorPalette::metric('nb_pageviews'),
                    'label' => (string) trans('winter.matomo::lang.reportwidgets.visits_over_time.metrics.nb_pageviews'),
                ];
                $metaItems[] = [
                    'label' => (string) trans('winter.matomo::lang.reportwidgets.visits_over_time.total_pageviews'),
                    'value' => ReportValueFormatter::integer($total),
                    'show'  => !empty($total),
                ];
            }

            $this->vars['chartDatasets'] = $datasets;
            $this->vars['widgetMeta']    = $this->renderWidgetMeta(array_merge(
                $metaItems,
                [['label' => (string) trans('winter.matomo::lang.reportwidgets.general.date_label'), 'value' => (string) $selectedDateLabel]],
            ));
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
     * Converts a Matomo daily response into a chart-line dataset string and total for one metric.
     *
     * Matomo returns either a flat array keyed by date (e.g. {"2026-04-28": {nb_visits: 12}})
     * or, for a single day, a flat metrics array. This helper normalises both.
     *
     * @param  array<string, mixed> $response
     * @return array{string, int}   [chartData string, total int]
     */
    protected function buildDatasetFromResponse(array $response, string $metricKey): array
    {
        $pairs = [];
        $total = 0;

        // Single-day flat response — unlikely for period=day&date=lastN but handled for safety.
        if (array_key_exists($metricKey, $response)) {
            $ts    = strtotime('today') * 1000;
            $value = (int) ($response[$metricKey] ?? 0);

            return ['[' . $ts . ', ' . $value . ']', $value];
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

            $value  = (int) ($metrics[$metricKey] ?? 0);
            $pairs[] = '[' . ($ts * 1000) . ', ' . $value . ']';
            $total  += $value;
        }

        return [implode(', ', $pairs), $total];
    }

    /**
     * Builds chart-line options with a localized tooltip format.
     *
     * Generates JavaScript literal syntax compatible with Flot's ocJSON parser.
     *
     * @return string JavaScript object literal payload (for data-chart-options)
     */
    protected function buildChartOptions(): string
    {
        $tooltipContent = (string) trans('winter.matomo::lang.reportwidgets.visits_over_time.chart.tooltip_content');
        if ($tooltipContent === 'winter.matomo::lang.reportwidgets.visits_over_time.chart.tooltip_content') {
            $tooltipContent = '%s | %x: <strong>%y</strong>';
        }

        $monthNames = trans('winter.matomo::lang.reportwidgets.general.calendar.monthNamesShort');
        if (!is_array($monthNames) || count($monthNames) !== 12) {
            $monthNames = trans('system::lang.client.datepicker.months');
        }
        if (!is_array($monthNames) || count($monthNames) !== 12) {
            $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        }

        $dayNames = trans('winter.matomo::lang.reportwidgets.general.calendar.weekdaysShort');
        if (!is_array($dayNames) || count($dayNames) !== 7) {
            $dayNames = trans('system::lang.client.datepicker.weekdaysShort');
        }
        if (!is_array($dayNames) || count($dayNames) !== 7) {
            $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        }

        $options = [
            'xaxis' => [
                'mode' => 'time',
                'tickLength' => 5,
                'monthNames' => array_values($monthNames),
                'dayNames' => array_values($dayNames),
            ],

            'tooltip' => true,
            'tooltipOpts' => [
                'defaultTheme' => false,
                'content' => $tooltipContent,
            ],
        ];

        $options = json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return str_replace('"', "'", $options);
    }
}
