<?php

namespace Winter\Matomo\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use Illuminate\Support\Facades\Log;
use Throwable;
use Winter\Matomo\Classes\Exceptions\MatomoReportingException;
use Winter\Matomo\Classes\MatomoReportingService;
use Winter\Matomo\Classes\Traits\ReportWidgetConcerns;

/**
 * Native WinterCMS report widget that renders Matomo referrer types as a donut chart.
 */
class Referrers extends ReportWidgetBase
{
    use ReportWidgetConcerns;

    /**
     * Default widget alias used by WinterCMS dashboard internals.
     *
     * @var string
     */
    protected $defaultAlias = 'MatomoReferrersReportWidget';

    /**
     * Defines configurable properties shown in the dashboard widget settings.
     */
    public function defineProperties(): array
    {
        return [
            'title' => [
                'title' => 'backend::lang.dashboard.widget_title_label',
                'type' => 'string',
                'default' => 'winter.matomo::lang.reportwidgets.referrers.title_default',
                'required' => true,
            ],
            'period' => [
                'title' => 'winter.matomo::lang.reportwidgets.referrers.period',
                'description' => 'winter.matomo::lang.reportwidgets.referrers.period_desc',
                'type' => 'dropdown',
                'options' => 'winter.matomo::lang.reportwidgets.referrers.period_options',
                'default' => 'week',
                'required' => true,
            ],
            'date' => [
                'title' => 'winter.matomo::lang.reportwidgets.referrers.date',
                'description' => 'winter.matomo::lang.reportwidgets.referrers.date_desc',
                'type' => 'dropdown',
                'options' => 'winter.matomo::lang.reportwidgets.referrers.date_options',
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
     * Loads and normalizes Matomo data for the widget view.
     */
    protected function loadData(bool $bypassCache = false): void
    {
        $selectedPeriod = (string) $this->property('period', 'week');
        $selectedDate = (string) $this->property('date', 'last7');
        $selectedPeriodLabel = $this->translatedOptionLabel(
            'winter.matomo::lang.reportwidgets.referrers.period_options',
            $selectedPeriod
        );
        $selectedDateLabel = $this->translatedOptionLabel(
            'winter.matomo::lang.reportwidgets.referrers.date_options',
            $selectedDate
        );

        $this->vars['error'] = null;
        $this->vars['referrerTypes'] = [];
        $this->vars['totalVisits'] = 0;
        $this->vars['refreshButton'] = $this->renderRefreshButton();
        $this->vars['widgetMeta'] = $this->renderWidgetMeta([
            [
                'label' => (string) trans('winter.matomo::lang.reportwidgets.referrers.total_visits'),
                'value' => (string) ($this->vars['totalVisits'] ?? ''),
                'show' => !empty($this->vars['totalVisits']),
            ],
            [
                'label' => (string) trans('winter.matomo::lang.reportwidgets.referrers.selected_period'),
                'value' => (string) $selectedPeriodLabel,
            ],
            [
                'label' => (string) trans('winter.matomo::lang.reportwidgets.referrers.selected_date'),
                'value' => (string) $selectedDateLabel,
            ],
        ]);

        try {
            /** @var MatomoReportingService $service */
            $service = app(MatomoReportingService::class);

            if ($bypassCache) {
                $service->clearCache();
            }

            $response = $service->get('Referrers.getReferrerType', [
                'period' => $selectedPeriod,
                'date' => $selectedDate,
            ]);

            $referrerTypes = $this->normalizeReferrerTypes($response);

            $this->vars['referrerTypes'] = $referrerTypes;
            $this->vars['totalVisits'] = array_sum(array_column($referrerTypes, 'nb_visits'));
            $this->vars['widgetMeta'] = $this->renderWidgetMeta([
                [
                    'label' => (string) trans('winter.matomo::lang.reportwidgets.referrers.total_visits'),
                    'value' => (string) ($this->vars['totalVisits'] ?? ''),
                    'show' => !empty($this->vars['totalVisits']),
                ],
                [
                    'label' => (string) trans('winter.matomo::lang.reportwidgets.referrers.selected_period'),
                    'value' => (string) $selectedPeriodLabel,
                ],
                [
                    'label' => (string) trans('winter.matomo::lang.reportwidgets.referrers.selected_date'),
                    'value' => (string) $selectedDateLabel,
                ],
            ]);
        } catch (Throwable $exception) {
            $this->vars['error'] = $this->resolveUserErrorMessage($exception);

            if ($exception instanceof MatomoReportingException) {
                Log::warning('Referrers widget failed to load Matomo data.', [
                    'widget' => static::class,
                    'error_code' => $exception->errorCode(),
                    'severity' => $exception->severity(),
                    'retryable' => $exception->isRetryable(),
                    'error' => $exception->getMessage(),
                    'context' => $exception->context(),
                ]);
            } else {
                Log::error('Referrers widget failed with an unexpected exception.', [
                    'widget' => static::class,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }

    /**
     * Normalizes the Matomo referrer response into rows consumable by chart-pie.
     *
     * @param array $response Raw Matomo API response
     * @return array<int, array{label: string, nb_visits: int, color: string}>
     */
    protected function normalizeReferrerTypes(array $response): array
    {
        $aggregated = [];

        foreach ($this->flattenReferrerTypeRows($response) as $item) {
            $metricValue = (int) ($item['nb_visits'] ?? $item['nb_actions'] ?? 0);
            if ($metricValue <= 0) {
                continue;
            }

            $label = trim((string) ($item['label'] ?? ''));
            $label = $label !== ''
                ? $label
                : (string) trans('winter.matomo::lang.reportwidgets.referrers.types.unknown');

            if (!isset($aggregated[$label])) {
                $aggregated[$label] = [
                    'label' => $label,
                    'nb_visits' => 0,
                    'color' => $this->colorForReferrerType($label),
                ];
            }

            $aggregated[$label]['nb_visits'] += $metricValue;
        }

        $rows = array_values($aggregated);

        usort($rows, fn(array $a, array $b) => $b['nb_visits'] <=> $a['nb_visits']);

        return $rows;
    }

    /**
     * Flattens top-level or grouped Matomo referrer type responses into raw rows.
     *
     * @param array $response Raw Matomo API response
     * @return array<int, array<string, mixed>>
     */
    protected function flattenReferrerTypeRows(array $response): array
    {
        $rows = [];

        foreach ($response as $item) {
            if (!is_array($item)) {
                continue;
            }

            if ($this->isReferrerTypeRow($item)) {
                $rows[] = $item;
                continue;
            }

            foreach ($item as $nestedItem) {
                if (is_array($nestedItem) && $this->isReferrerTypeRow($nestedItem)) {
                    $rows[] = $nestedItem;
                }
            }
        }

        return $rows;
    }

    /**
     * Determines whether a response item looks like a referrer-type row.
     */
    protected function isReferrerTypeRow(array $item): bool
    {
        return array_key_exists('label', $item)
            && (array_key_exists('nb_visits', $item) || array_key_exists('nb_actions', $item));
    }

    /**
     * Maps common referrer labels to stable chart colors.
     */
    protected function colorForReferrerType(string $label): string
    {
        $normalized = strtolower(trim($label));

        return match (true) {
            str_contains($normalized, 'direct') => '#4c6ef5',
            str_contains($normalized, 'search') || str_contains($normalized, 'moteur') => '#2f9e44',
            str_contains($normalized, 'social') || str_contains($normalized, 'reseau') || str_contains($normalized, 'réseau') => '#e8590c',
            str_contains($normalized, 'website') || str_contains($normalized, 'site') => '#1098ad',
            str_contains($normalized, 'campaign') || str_contains($normalized, 'campagne') => '#c2255c',
            default => '#868e96',
        };
    }
}
