<?php

namespace Winter\Matomo\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use Illuminate\Support\Facades\Log;
use Throwable;
use Winter\Matomo\Classes\Exceptions\MatomoReportingException;
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
        return [
            'title' => [
                'title' => 'backend::lang.dashboard.widget_title_label',
                'type' => 'string',
                'default' => 'winter.matomo::lang.reportwidgets.visits_summary.title_default',
                'required' => true,
            ],
            'period' => [
                'title' => 'winter.matomo::lang.reportwidgets.visits_summary.period',
                'description' => 'winter.matomo::lang.reportwidgets.visits_summary.period_desc',
                'type' => 'dropdown',
                'options' => 'winter.matomo::lang.reportwidgets.visits_summary.period_options',
                'default' => 'week',
                'required' => true,
            ],
            'date' => [
                'title' => 'winter.matomo::lang.reportwidgets.visits_summary.date',
                'description' => 'winter.matomo::lang.reportwidgets.visits_summary.date_desc',
                'type' => 'dropdown',
                'options' => 'winter.matomo::lang.reportwidgets.visits_summary.date_options',
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
        $selectedDate = (string) $this->property('date', 'last7');
        $selectedPeriodLabel = $this->translatedOptionLabel(
            'winter.matomo::lang.reportwidgets.visits_summary.period_options',
            $selectedPeriod
        );
        $selectedDateLabel = $this->translatedOptionLabel(
            'winter.matomo::lang.reportwidgets.visits_summary.date_options',
            $selectedDate
        );

        $this->vars['error'] = null;
        $this->vars['metrics'] = $this->emptyMetrics();
        $this->vars['refreshButton'] = $this->renderRefreshButton();
        $this->vars['widgetMeta'] = $this->renderWidgetMeta([
            [
                'label' => (string) trans('winter.matomo::lang.reportwidgets.visits_summary.selected_period'),
                'value' => (string) $selectedPeriodLabel,
            ],
            [
                'label' => (string) trans('winter.matomo::lang.reportwidgets.visits_summary.selected_date'),
                'value' => (string) $selectedDateLabel,
            ],
        ]);

        try {
            /** @var MatomoReportingService $service */
            $service = app(MatomoReportingService::class);

            if ($bypassCache) {
                $service->clearCache();
            }

            $response = $service->get('VisitsSummary.get', [
                'period' => $selectedPeriod,
                'date' => $selectedDate,
            ]);

            $metricsPayload = $this->extractMetricsPayload($response);

            $this->vars['metrics'] = [
                'nb_visits' => (int) ($metricsPayload['nb_visits'] ?? 0),
                'nb_uniq_visitors' => (int) ($metricsPayload['nb_uniq_visitors'] ?? 0),
                'nb_actions' => (int) ($metricsPayload['nb_actions'] ?? 0),
                'bounce_rate' => (string) ($metricsPayload['bounce_rate'] ?? '0%'),
                'nb_actions_per_visit' => (string) ($metricsPayload['nb_actions_per_visit'] ?? '0'),
                'avg_time_on_site' => $this->formatDuration((int) ($metricsPayload['avg_time_on_site'] ?? 0)),
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
     * Extracts the metrics object from Matomo responses.
     *
     * Some responses are flat arrays, while others are grouped by period/date.
     * This helper normalizes both formats.
     *
     * @param array<string, mixed> $response
     * @return array<string, mixed>
     */
    protected function extractMetricsPayload(array $response): array
    {
        if (array_key_exists('nb_visits', $response)) {
            return $response;
        }

        foreach ($response as $value) {
            if (is_array($value) && array_key_exists('nb_visits', $value)) {
                return $value;
            }
        }

        return [];
    }

    /**
     * Provides default metric values used before API data is loaded.
     *
     * @return array<string, int|string>
     */
    protected function emptyMetrics(): array
    {
        return [
            'nb_visits' => 0,
            'nb_uniq_visitors' => 0,
            'nb_actions' => 0,
            'bounce_rate' => '0%',
            'nb_actions_per_visit' => '0',
            'avg_time_on_site' => '00:00',
        ];
    }
}
