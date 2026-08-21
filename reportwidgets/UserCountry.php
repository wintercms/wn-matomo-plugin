<?php

namespace Winter\Matomo\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use Illuminate\Support\Facades\Log;
use Throwable;
use Winter\Matomo\Classes\Exceptions\MatomoReportingException;
use Winter\Matomo\Classes\MatomoReportingService;
use Winter\Matomo\Classes\Traits\ReportWidgetConcerns;

/**
 * Native WinterCMS report widget that renders top countries from Matomo analytics.
 */
class UserCountry extends ReportWidgetBase
{
    use ReportWidgetConcerns;

    /**
     * Default widget alias used by WinterCMS dashboard internals.
     *
     * @var string
     */
    protected $defaultAlias = 'MatomoUserCountryReportWidget';

    /**
     * Defines configurable properties shown in the dashboard widget settings.
     */
    public function defineProperties(): array
    {
        return array_merge([
            'title' => [
                'title'    => 'backend::lang.dashboard.widget_title_label',
                'type'     => 'string',
                'default'  => 'winter.matomo::lang.reportwidgets.user_country.label',
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
            'limit' => [
                'title'       => 'winter.matomo::lang.reportwidgets.general.limit',
                'description' => 'winter.matomo::lang.reportwidgets.user_country.limit_desc',
                'type'        => 'dropdown',
                'options'     => 'winter.matomo::lang.reportwidgets.user_country.limit_options',
                'default'     => 10,
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
     */
    protected function loadData(bool $bypassCache = false): void
    {
        $selectedDateRange = (string) $this->property('date_range', 'last30');
        ['period' => $selectedPeriod, 'date' => $selectedDate] = $this->resolveDateRange($selectedDateRange);
        $selectedLimit = (int) $this->property('limit', 10);
        $selectedDateRangeLabel = $this->translatedOptionLabel(
            'winter.matomo::lang.reportwidgets.general.date_range_options',
            $selectedDateRange
        );
        $selectedLimitLabel = $this->translatedOptionLabel(
            'winter.matomo::lang.reportwidgets.user_country.limit_options',
            $selectedLimit
        );

        $this->vars['error'] = null;
        $this->vars['countries'] = [];
        $this->vars['refreshButton'] = $this->renderRefreshButton();
        $this->vars['widgetMeta'] = $this->renderWidgetMeta([
            [
                'label' => (string) trans('winter.matomo::lang.reportwidgets.general.selected_date_range'),
                'value' => (string) $selectedDateRangeLabel,
            ],
            [
                'label' => (string) trans('winter.matomo::lang.reportwidgets.general.selected_limit'),
                'value' => (string) $selectedLimitLabel,
            ],
        ]);

        try {
            /** @var MatomoReportingService $service */
            $service = app(MatomoReportingService::class);

            $requestParams = [
                'period' => $selectedPeriod,
                'date' => $selectedDate,
                'filter_limit' => $selectedLimit,
            ];

            if ($bypassCache) {
                $service->clearCache($this->resolveCacheIdentifier($service, 'UserCountry.getCountry', $requestParams));
            }

            $response = $service->get('UserCountry.getCountry', $requestParams);

            $this->vars['countries'] = $this->normalizeCountries($response, $selectedLimit);
        } catch (Throwable $exception) {
            $this->vars['error'] = $this->resolveUserErrorMessage($exception);

            if ($exception instanceof MatomoReportingException) {
                Log::warning('UserCountry widget failed to load Matomo data.', [
                    'widget' => static::class,
                    'error_code' => $exception->errorCode(),
                    'severity' => $exception->severity(),
                    'retryable' => $exception->isRetryable(),
                    'error' => $exception->getMessage(),
                    'context' => $exception->context(),
                ]);
            } else {
                Log::error('UserCountry widget failed with an unexpected exception.', [
                    'widget' => static::class,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }

    /**
     * Normalizes Matomo country rows into a sorted top list.
     *
     * @param array $response Raw Matomo API response
     * @return array<int, array{country: string, country_code: string, country_flag: string, nb_visits: int}>
     */
    protected function normalizeCountries(array $response, int $limit): array
    {
        $aggregated = [];

        foreach ($this->flattenCountryRows($response) as $item) {
            $visits = (int) ($item['nb_visits'] ?? 0);
            if ($visits <= 0) {
                continue;
            }

            $country = trim((string) ($item['label'] ?? ''));
            if ($country === '') {
                $country = (string) trans('winter.matomo::lang.reportwidgets.user_country.unknown_country');
            }

            $countryCode = $this->extractCountryCode($item);
            $key = $countryCode !== '' ? $countryCode : mb_strtolower($country);

            if (!isset($aggregated[$key])) {
                $aggregated[$key] = [
                    'country' => $country,
                    'country_code' => $countryCode,
                    'country_flag' => $this->flagForCountryCode($countryCode),
                    'nb_visits' => 0,
                ];
            }

            $aggregated[$key]['nb_visits'] += $visits;
        }

        $rows = array_values($aggregated);
        usort($rows, fn(array $a, array $b) => $b['nb_visits'] <=> $a['nb_visits']);

        return array_slice($rows, 0, max(1, $limit));
    }

    /**
     * Flattens top-level or grouped Matomo country responses into rows.
     *
     * @param array $response Raw Matomo API response
     * @return array<int, array<string, mixed>>
     */
    protected function flattenCountryRows(array $response): array
    {
        $rows = [];

        foreach ($response as $item) {
            if (!is_array($item)) {
                continue;
            }

            if ($this->isCountryRow($item)) {
                $rows[] = $item;
                continue;
            }

            foreach ($item as $nestedItem) {
                if (is_array($nestedItem) && $this->isCountryRow($nestedItem)) {
                    $rows[] = $nestedItem;
                }
            }
        }

        return $rows;
    }

    /**
     * Determines whether a response item looks like a country row.
     */
    protected function isCountryRow(array $item): bool
    {
        return array_key_exists('label', $item)
            && array_key_exists('nb_visits', $item);
    }

    /**
     * Extracts ISO-3166 alpha-2 code from Matomo row fields.
     */
    protected function extractCountryCode(array $item): string
    {
        $directCode = trim((string) ($item['code'] ?? ''));
        if ($this->isAlpha2CountryCode($directCode)) {
            return strtoupper($directCode);
        }

        $segment = (string) ($item['segment'] ?? '');
        if (preg_match('/countryCode==([A-Za-z]{2})/', $segment, $matches) === 1) {
            return strtoupper($matches[1]);
        }

        return '';
    }

    /**
     * Builds a flag emoji for a two-letter country code.
     */
    protected function flagForCountryCode(string $countryCode): string
    {
        if (!$this->isAlpha2CountryCode($countryCode)) {
            return (string) trans('winter.matomo::lang.reportwidgets.user_country.unknown_flag');
        }

        $letters = strtoupper($countryCode);
        $first = ord($letters[0]) + 127397;
        $second = ord($letters[1]) + 127397;

        return mb_chr($first, 'UTF-8') . mb_chr($second, 'UTF-8');
    }

    /**
     * Validates an ISO-3166 alpha-2 style code.
     */
    protected function isAlpha2CountryCode(string $value): bool
    {
        return preg_match('/^[A-Za-z]{2}$/', $value) === 1;
    }
}
