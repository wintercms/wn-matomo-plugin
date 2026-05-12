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
 * Native WinterCMS report widget that renders top pages from Matomo analytics.
 *
 * This widget retrieves top pages data via MatomoReportingService and exposes
 * normalized view variables for the backend partials.
 */
class TopPages extends ReportWidgetBase
{
    use ReportWidgetConcerns;

    /**
     * Default widget alias used by WinterCMS dashboard internals.
     *
     * @var string
     */
    protected $defaultAlias = 'MatomoTopPagesReportWidget';

    /**
     * Defines configurable properties shown in the dashboard widget settings.
     */
    public function defineProperties(): array
    {
        return array_merge([
            'title' => [
                'title' => 'backend::lang.dashboard.widget_title_label',
                'type' => 'string',
                'default' => 'winter.matomo::lang.reportwidgets.top_pages.title_default',
                'required' => true,
            ],
            'period' => [
                'title' => 'winter.matomo::lang.reportwidgets.top_pages.period',
                'description' => 'winter.matomo::lang.reportwidgets.top_pages.period_desc',
                'type' => 'dropdown',
                'options' => 'winter.matomo::lang.reportwidgets.top_pages.period_options',
                'default' => 'week',
                'required' => true,
            ],
            'date' => [
                'title' => 'winter.matomo::lang.reportwidgets.top_pages.date',
                'description' => 'winter.matomo::lang.reportwidgets.top_pages.date_desc',
                'type' => 'dropdown',
                'options' => 'winter.matomo::lang.reportwidgets.top_pages.date_options',
                'default' => 'last7',
                'required' => true,
            ],
            'limit' => [
                'title' => 'winter.matomo::lang.reportwidgets.top_pages.limit',
                'description' => 'winter.matomo::lang.reportwidgets.top_pages.limit_desc',
                'type' => 'dropdown',
                'options' => 'winter.matomo::lang.reportwidgets.top_pages.limit_options',
                'default' => 10,
                'required' => true,
            ],
            'view_mode' => [
                'title' => 'winter.matomo::lang.reportwidgets.top_pages.view_mode',
                'description' => 'winter.matomo::lang.reportwidgets.top_pages.view_mode_desc',
                'type' => 'dropdown',
                'options' => 'winter.matomo::lang.reportwidgets.top_pages.view_mode_options',
                'default' => 'flat',
                'required' => true,
            ],
            'exclude_low_pop' => [
                'title' => 'winter.matomo::lang.reportwidgets.top_pages.exclude_low_pop',
                'description' => 'winter.matomo::lang.reportwidgets.top_pages.exclude_low_pop_desc',
                'type' => 'checkbox',
                'default' => false,
            ],
            'exclude_low_pop_value' => [
                'title' => 'winter.matomo::lang.reportwidgets.top_pages.exclude_low_pop_value',
                'description' => 'winter.matomo::lang.reportwidgets.top_pages.exclude_low_pop_value_desc',
                'type' => 'string',
                'default' => '1',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'winter.matomo::lang.reportwidgets.top_pages.exclude_low_pop_value_validation',
            ],
        ], $this->getDisplayProperties());
    }

    /**
     * Loads shared CSS used by Matomo native report widgets.
     */
    protected function loadAssets(): void
    {
        $this->addCss('/plugins/winter/matomo/assets/css/reportwidgets.css');
        $this->addJs('/plugins/winter/matomo/assets/js/toppages-hierarchy.js');
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
        $selectedLimit = (int) $this->property('limit', 10);
        $selectedViewMode = (string) $this->property('view_mode', 'flat');
        $excludeLowPop = (bool) $this->property('exclude_low_pop', false);
        $excludeLowPopValue = (int) $this->property('exclude_low_pop_value', 1);
        $selectedPeriodLabel = $this->translatedOptionLabel(
            'winter.matomo::lang.reportwidgets.top_pages.period_options',
            $selectedPeriod
        );
        $selectedDateLabel = $this->translatedOptionLabel(
            'winter.matomo::lang.reportwidgets.top_pages.date_options',
            $selectedDate
        );
        $selectedLimitLabel = $this->translatedOptionLabel(
            'winter.matomo::lang.reportwidgets.top_pages.limit_options',
            $selectedLimit
        );
        $selectedViewModeLabel = $this->translatedOptionLabel(
            'winter.matomo::lang.reportwidgets.top_pages.view_mode_options',
            $selectedViewMode
        );

        $this->vars['error'] = null;
        $this->vars['pages'] = [];
        $this->vars['selectedLimit'] = $selectedLimit;
        $this->vars['selectedViewMode'] = $selectedViewMode;
        $this->vars['refreshButton'] = $this->renderRefreshButton();
        $this->vars['widgetMeta'] = $this->renderWidgetMeta([
            [
                'label' => (string) trans('winter.matomo::lang.reportwidgets.top_pages.selected_period'),
                'value' => (string) $selectedPeriodLabel,
            ],
            [
                'label' => (string) trans('winter.matomo::lang.reportwidgets.top_pages.selected_date'),
                'value' => (string) $selectedDateLabel,
            ],
            [
                'label' => (string) trans('winter.matomo::lang.reportwidgets.top_pages.selected_limit'),
                'value' => (string) $selectedLimitLabel,
            ],
            [
                'label' => (string) trans('winter.matomo::lang.reportwidgets.top_pages.selected_view_mode'),
                'value' => (string) $selectedViewModeLabel,
            ],
        ]);

        try {
            /** @var MatomoReportingService $service */
            $service = app(MatomoReportingService::class);

            if ($bypassCache) {
                $service->clearCache();
            }

            $requestParams = [
                'period' => $selectedPeriod,
                'date' => $selectedDate,
                'filter_limit' => $selectedLimit,
            ];

            if ($selectedViewMode === 'hierarchical') {
                $requestParams['flat'] = 0;
                $requestParams['expanded'] = 1;
            } else {
                $requestParams['flat'] = 1;
            }

            if ($excludeLowPop) {
                $requestParams['filter_excludelowpop'] = 'nb_visits';
                $requestParams['filter_excludelowpop_value'] = $excludeLowPopValue;
            }

            $response = $service->get('Actions.getPageUrls', $requestParams);

            $this->vars['pages'] = $this->normalizePagesData($response, $selectedViewMode);
        } catch (Throwable $exception) {
            $this->vars['error'] = $this->resolveUserErrorMessage($exception);

            if ($exception instanceof MatomoReportingException) {
                Log::warning('TopPages widget failed to load Matomo data.', [
                    'widget' => static::class,
                    'error_code' => $exception->errorCode(),
                    'severity' => $exception->severity(),
                    'retryable' => $exception->isRetryable(),
                    'error' => $exception->getMessage(),
                    'context' => $exception->context(),
                ]);
            } else {
                Log::error('TopPages widget failed with an unexpected exception.', [
                    'widget' => static::class,
                    'exception' => $exception->getMessage(),
                ]);
            }
        }
    }

    /**
     * Normalizes Matomo response data according to the selected display mode.
     *
     * @param array $response Raw Matomo API response
     * @param string $viewMode Selected display mode (flat or hierarchical)
     * @return array Normalized pages data for rendering
     */
    protected function normalizePagesData(array $response, string $viewMode): array
    {
        if ($viewMode === 'hierarchical') {
            return $this->normalizeHierarchicalPagesData($response);
        }

        return $this->normalizeFlatPagesData($response);
    }

    /**
     * Normalizes Matomo page rows into a flat, aggregated page list.
     *
     * @param array $response Raw Matomo API response
     * @return array Aggregated flat pages data
     */
    protected function normalizeFlatPagesData(array $response): array
    {
        $rows = $this->flattenPageRows($response);

        if ($rows === []) {
            return [];
        }

        $aggregated = [];

        foreach ($rows as $pageData) {
            $key = (string) ($pageData['segment'] ?? $pageData['url'] ?? $pageData['label'] ?? '');
            if ($key === '') {
                continue;
            }

            $pageUrl = (string) ($pageData['url'] ?? $pageData['label'] ?? '');
            $entryVisits = (int) ($pageData['entry_nb_visits'] ?? 0);
            $avgTimeOnPage = (float) ($pageData['avg_time_on_page'] ?? $pageData['avg_time_on_site'] ?? 0);

            if (!isset($aggregated[$key])) {
                $aggregated[$key] = [
                    'url' => $pageUrl,
                    'nb_visits' => 0,
                    'bounce_count' => 0,
                    'entry_nb_visits' => 0,
                    'avg_time_on_page_total' => 0.0,
                    'avg_time_on_page_weight' => 0,
                    'bounce_rate' => (string) ($pageData['bounce_rate'] ?? '0%'),
                ];
            }

            $aggregated[$key]['url'] = $pageUrl ?: $aggregated[$key]['url'];
            $aggregated[$key]['nb_visits'] += (int) ($pageData['nb_visits'] ?? 0);
            $aggregated[$key]['bounce_count'] += (int) ($pageData['entry_bounce_count'] ?? 0);
            $aggregated[$key]['entry_nb_visits'] += $entryVisits;
            $aggregated[$key]['avg_time_on_page_total'] += $avgTimeOnPage * max(1, $entryVisits);
            $aggregated[$key]['avg_time_on_page_weight'] += max(1, $entryVisits);
        }

        $pages = array_map(function (array $page) {
            $averageSeconds = $page['avg_time_on_page_weight'] > 0
                ? (int) round($page['avg_time_on_page_total'] / $page['avg_time_on_page_weight'])
                : 0;

            return [
                'url' => $this->stripDomainFromUrl($page['url']),
                'nb_visits' => $page['nb_visits'],
                'bounce_rate' => $this->formatBounceRate(
                    $page['bounce_count'],
                    $page['entry_nb_visits'],
                    $page['bounce_rate']
                ),
                'avg_time_on_site' => $averageSeconds,
            ];
        }, $aggregated);

        usort($pages, fn(array $a, array $b) => $b['nb_visits'] <=> $a['nb_visits']);

        return $pages;
    }

    /**
     * Normalizes Matomo grouped response into a hierarchical page tree.
     *
     * @param array $response Raw Matomo API response
     * @return array Hierarchical pages data ready for rendering
     */
    protected function normalizeHierarchicalPagesData(array $response): array
    {
        $tree = [];

        foreach ($response as $groupData) {
            if (!is_array($groupData)) {
                continue;
            }

            $isTopLevelPageRow = $this->isHierarchicalPageRow($groupData);

            if ($isTopLevelPageRow) {
                $node = $this->buildHierarchicalPageNode($groupData);
                $tree = $this->mergeHierarchicalNodes($tree, $node);
                continue;
            }

            foreach ($groupData as $pageData) {
                if (!is_array($pageData)) {
                    continue;
                }

                $node = $this->buildHierarchicalPageNode($pageData);
                $tree = $this->mergeHierarchicalNodes($tree, $node);
            }
        }

        return $this->formatHierarchicalPages($this->sortHierarchicalPages(array_values($tree)));
    }

    /**
     * Builds a normalized hierarchical node from a raw Matomo page row.
     *
     * @param array $pageData Raw page row from Matomo response
     * @return array Normalized page node including children
     */
    protected function buildHierarchicalPageNode(array $pageData): array
    {
        $key = (string) ($pageData['segment'] ?? $pageData['url'] ?? $pageData['label'] ?? '');
        $label = (string) ($pageData['label'] ?? $pageData['url'] ?? '');
        $pageUrl = (string) ($pageData['url'] ?? $label);
        $entryVisits = (int) ($pageData['entry_nb_visits'] ?? 0);
        $avgTimeOnPage = (float) ($pageData['avg_time_on_page'] ?? $pageData['avg_time_on_site'] ?? 0);

        $children = [];
        if (!empty($pageData['subtable']) && is_array($pageData['subtable'])) {
            foreach ($pageData['subtable'] as $childPage) {
                if (!is_array($childPage)) {
                    continue;
                }

                $childNode = $this->buildHierarchicalPageNode($childPage);
                $children[$childNode['key']] = $childNode;
            }
        }

        return [
            'key' => $key,
            'label' => $label,
            'url' => $pageUrl,
            'segment' => $key,
            'nb_visits' => (int) ($pageData['nb_visits'] ?? 0),
            'bounce_count' => (int) ($pageData['entry_bounce_count'] ?? 0),
            'entry_nb_visits' => $entryVisits,
            'avg_time_on_page_total' => $avgTimeOnPage * max(1, $entryVisits),
            'avg_time_on_page_weight' => max(1, $entryVisits),
            'bounce_rate' => (string) ($pageData['bounce_rate'] ?? '0%'),
            'children' => $children,
        ];
    }

    /**
     * Applies computed display fields recursively to hierarchical pages.
     *
     * @param array $pages Hierarchical pages tree
    * @return array Hierarchical pages tree with computed display values
     */
    protected function formatHierarchicalPages(array $pages): array
    {
        foreach ($pages as &$page) {
            $page['avg_time_on_site'] = $page['avg_time_on_page_weight'] > 0
                ? (int) round($page['avg_time_on_page_total'] / $page['avg_time_on_page_weight'])
                : 0;

            $page['bounce_rate'] = $this->formatBounceRate(
                $page['bounce_count'],
                $page['entry_nb_visits'],
                $page['bounce_rate']
            );

            if (!empty($page['children'])) {
                $page['children'] = $this->formatHierarchicalPages(array_values($page['children']));
            }
        }

        return $pages;
    }

    /**
     * Merges a hierarchical node into an existing tree by key.
     *
     * @param array $tree Existing hierarchical tree keyed by node key
     * @param array $node Node to merge into the tree
     * @return array Updated hierarchical tree
     */
    protected function mergeHierarchicalNodes(array $tree, array $node): array
    {
        $key = $node['key'];

        if (!isset($tree[$key])) {
            $tree[$key] = $node;
            return $tree;
        }

        $existing = $tree[$key];

        $tree[$key] = [
            'key' => $key,
            'label' => $existing['label'] !== '' ? $existing['label'] : $node['label'],
            'url' => $existing['url'] !== '' ? $existing['url'] : $node['url'],
            'segment' => $existing['segment'] !== '' ? $existing['segment'] : $node['segment'],
            'nb_visits' => $existing['nb_visits'] + $node['nb_visits'],
            'bounce_count' => $existing['bounce_count'] + $node['bounce_count'],
            'entry_nb_visits' => $existing['entry_nb_visits'] + $node['entry_nb_visits'],
            'avg_time_on_page_total' => $existing['avg_time_on_page_total'] + $node['avg_time_on_page_total'],
            'avg_time_on_page_weight' => $existing['avg_time_on_page_weight'] + $node['avg_time_on_page_weight'],
            'bounce_rate' => $this->formatBounceRate(
                $existing['bounce_count'] + $node['bounce_count'],
                $existing['entry_nb_visits'] + $node['entry_nb_visits'],
                $node['bounce_rate']
            ),
            'children' => $this->mergeHierarchicalChildren($existing['children'], $node['children']),
        ];

        return $tree;
    }

    /**
     * Merges child nodes recursively for a hierarchical parent node.
     *
     * @param array $existingChildren Existing child nodes keyed by key
     * @param array $newChildren Incoming child nodes keyed by key
     * @return array Merged child nodes
     */
    protected function mergeHierarchicalChildren(array $existingChildren, array $newChildren): array
    {
        foreach ($newChildren as $childKey => $childNode) {
            if (!isset($existingChildren[$childKey])) {
                $existingChildren[$childKey] = $childNode;
                continue;
            }

            $existingChildren = $this->mergeHierarchicalNodes($existingChildren, $childNode);
        }

        return $existingChildren;
    }

    /**
     * Sorts hierarchical pages recursively by visits in descending order.
     *
     * @param array $pages Hierarchical pages tree
     * @return array Sorted hierarchical pages tree
     */
    protected function sortHierarchicalPages(array $pages): array
    {
        usort($pages, fn(array $a, array $b) => $b['nb_visits'] <=> $a['nb_visits']);

        foreach ($pages as &$page) {
            if (!empty($page['children'])) {
                $page['children'] = $this->sortHierarchicalPages(array_values($page['children']));
            }
        }

        return $pages;
    }

    /**
     * Flattens Matomo page rows from either a top-level list or a grouped response.
     *
     * @param array $response
     * @return array
     */
    protected function flattenPageRows(array $response): array
    {
        $rows = [];

        foreach ($response as $item) {
            if (!is_array($item)) {
                continue;
            }

            if ($this->isPageRow($item)) {
                $rows[] = $item;
                continue;
            }

            foreach ($item as $nestedItem) {
                if (is_array($nestedItem) && $this->isPageRow($nestedItem)) {
                    $rows[] = $nestedItem;
                }
            }
        }

        return $rows;
    }

    /**
     * Renders hierarchical pages as table rows with expandable children.
     *
     * @param array $pages Hierarchical pages to render
     * @param int $level Current tree depth level
     * @param string|null $parentId Parent row identifier for child rows
     * @param string|null $widgetToken Stable token used to namespace row IDs per widget instance
     * @return string Rendered HTML rows
     */
    protected function renderHierarchicalRows(array $pages, int $level = 0, ?string $parentId = null, ?string $widgetToken = null): string
    {
        $html = '';
        $widgetToken = $widgetToken ?: preg_replace('/[^a-z0-9_-]/i', '-', (string) $this->getId());

        foreach ($pages as $page) {
            $rowId = 'mp-' . md5($widgetToken . '|' . ($page['segment'] ?? $page['url'] ?? $page['label'] ?? uniqid('', true)));
            $hasChildren = !empty($page['children']);
            $rowClasses = ['matomo-page-row'];

            if ($parentId !== null) {
                $rowClasses[] = 'child-of-' . $parentId;
                $rowClasses[] = 'matomo-page-row-hidden';
            }

            $html .= '<tr id="' . $rowId . '" class="' . implode(' ', $rowClasses) . '">';
            $html .= '<td class="label-cell" style="--matomo-page-level: ' . $level . ';">';

            if ($hasChildren) {
                $html .= '<button type="button" class="btn btn-link matomo-page-toggle" data-target="' . $rowId . '" aria-expanded="false" onclick="return (window.WinterMatomoTopPages && window.WinterMatomoTopPages.toggleHierarchy) ? window.WinterMatomoTopPages.toggleHierarchy(this) : false;"><i class="icon icon-2xs icon-plus"></i></button> ';
            }

            $html .= '<span class="matomo-top-pages-url-text" title="' . e($page['label']) . '">' . e($page['label']) . '</span>';
            $html .= '</td>';
            $html .= '<td class="matomo-sticky-metric matomo-sticky-metric-visits">' . e(ReportValueFormatter::integer($page['nb_visits'])) . '</td>';
            $html .= '<td class="matomo-sticky-metric matomo-sticky-metric-bounce">' . e(ReportValueFormatter::percentage($page['bounce_rate'])) . '</td>';
            $html .= '<td class="matomo-sticky-metric matomo-sticky-metric-time">' . e(ReportValueFormatter::duration($page['avg_time_on_site'])) . '</td>';
            $html .= '</tr>';

            if ($hasChildren) {
                $html .= $this->renderHierarchicalRows(array_values($page['children']), $level + 1, $rowId, $widgetToken);
            }
        }

        return $html;
    }

    /**
     * Determines whether a response array represents a single Matomo page row.
     *
     * @param array $item
     * @return bool
     */
    protected function isPageRow(array $item): bool
    {
        return array_key_exists('label', $item)
            && (array_key_exists('nb_visits', $item) || array_key_exists('nb_hits', $item));
    }

    /**
     * Determines whether an array can be treated as a hierarchical page row.
     *
     * Hierarchical payloads may include top-level rows with children (`subtable`) even
     * when metric fields are sparse, so this check is broader than isPageRow().
     *
     * @param array $item
     * @return bool
     */
    protected function isHierarchicalPageRow(array $item): bool
    {
        return $this->isPageRow($item)
            || array_key_exists('subtable', $item)
            || (array_key_exists('label', $item) && array_key_exists('url', $item));
    }

    /**
     * Formats bounce rate values from raw response fields.
     *
     * @param int $bounceCount
     * @param int $entryVisits
     * @param string $fallback
     * @return string
     */
    protected function formatBounceRate(int $bounceCount, int $entryVisits, string $fallback): float
    {
        if ($entryVisits > 0) {
            return (float) round($bounceCount / $entryVisits * 100, 0);
        }

        return ReportValueFormatter::numericValue($fallback);
    }

    /**
     * Strips the domain (scheme + host) from a URL, returning only the path and query.
     *
     * @param string $url The full URL
     * @return string URL without scheme and host
     */
    protected function stripDomainFromUrl(string $url): string
    {
        $parsed = parse_url($url);

        if ($parsed === false || empty($parsed['path'])) {
            return $url;
        }

        $result = $parsed['path'];

        if (!empty($parsed['query'])) {
            $result .= '?' . $parsed['query'];
        }

        if (!empty($parsed['fragment'])) {
            $result .= '#' . $parsed['fragment'];
        }

        return $result;
    }
}
