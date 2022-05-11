<?php namespace Winter\Matomo\Classes;

use Cache;
use Carbon\Carbon;
use Winter\Storm\Support\Str;
use Winter\Storm\Network\Http;
use Winter\Storm\Router\UrlGenerator;
use SystemException;

/**
 * Matomo Reporting API Simple PHP Wrapper
 * Inspired by https://github.com/VisualAppeal/Matomo-PHP-API/blob/master/src/Matomo.php
 * @see https://developer.matomo.org/api-reference/reporting-api
 * @author Luke Towers
 */
class ReportingAPI
{
    const PERIOD_DAY = 'day';
    const PERIOD_WEEK = 'week';
    const PERIOD_MONTH = 'month';
    const PERIOD_YEAR = 'year';
    const PERIOD_RANGE = 'range';
    const PERIOD_ALL = 'all';

    const DATE_TODAY = 'today';
    const DATE_YESTERDAY = 'yesterday';
    const DATE_LAST_WEEK = 'lastWeek';
    const DATE_LAST_MONTH = 'lastMonth';
    const DATE_LAST_YEAR = 'lastYear';

    const FORMAT_XML = 'xml';
    const FORMAT_JSON = 'json';
    const FORMAT_CSV = 'csv';
    const FORMAT_TSV = 'tsv';
    const FORMAT_HTML = 'html';
    const FORMAT_RSS = 'rss';
    const FORMAT_PHP = 'php';

    //
    // Connection Properties
    //

    /**
     * The base URL to the Matomo instance to make API requests to
     */
    protected string $serverUrl;

    /**
     * The Matomo Site ID to make API requests against
     */
    protected int|null $siteId;

    /**
     * The authentication token used to authenticate API requests
     * @see https://developer.matomo.org/api-reference/reporting-api#authenticate-to-the-api-via-token_auth-parameter
     */
    protected string $authToken;

    //
    // Filter Properties
    //

    /**
     * The currently set period
     */
    protected string $period = 'month';

    /**
     * The currently set date
     */
    protected string $date = 'lastMonth';

    /**
     * The start of the current date range
     */
    protected ?string $rangeStart;

    /**
     * The end of the current date rage
     */
    protected ?string $rangeEnd;

    /**
     * The currently set format
     */
    protected string $format = 'php';

    /**
     * The currently set filter limit
     */
    protected int $filterLimit = 100;

    /**
     * The currently set language to request
     */
    protected string $language = 'en';

    //
    // Internal
    //

    /**
     * The amount of time the current request should be cached for.
     * 0 will remember forever, -1 will disable the cache;
     */
    protected int $cacheTtl = 60 * 60 * 24 * 2;

    /**
     * Create a new instance
     */
    public function __construct(string $serverUrl, string $authToken, int $siteId = null)
    {
        $this->serverUrl = $serverUrl;
        $this->authToken = $authToken;
        $this->siteId = $siteId;
    }

    //
    // Accessors & Mutators
    //

    /**
     * Get the currently set serverUrl
     */
    public function getServerUrl(): string
    {
        return $this->serverUrl;
    }

    /**
     * Set the serverUrl property
     */
    public function setServerUrl(string $value): ReportingAPI
    {
        $this->serverUrl = $value;
        return $this;
    }

    /**
     * Get the currently set siteId
     */
    public function getSiteId(): ?int
    {
        return $this->siteId;
    }

    /**
     * Set the siteId property
     */
    public function setSiteId(int $value): ReportingAPI
    {
        $this->siteId = $value;
        return $this;
    }

    /**
     * Get the currently set authToken
     */
    public function getAuthToken(): string
    {
        return $this->authToken;
    }

    /**
     * Set the authToken property
     */
    public function setAuthToken(string $value): ReportingAPI
    {
        $this->authToken = $value;
        return $this;
    }


    /**
     * Get the currently set period property value
     */
    public function getPeriod(): string
    {
        return $this->period;
    }

    /**
     * Set the period property value
     */
    public function setPeriod(string $value): ReportingAPI
    {
        if ($value === static::PERIOD_ALL) {
            $this->setRange('2008-11-27');
        } else {
            $this->period = $value;
        }

        return $this;
    }

    /**
     * Get the date range comma separated
     */
    public function getRange(): string
    {
        if (empty($this->rangeEnd)) {
            return $this->rangeStart;
        } else {
            return $this->rangeStart . ',' . $this->rangeEnd;
        }
    }

    /**
     * Set the date range
     *
     * @param string|null|Carbon|DateTime $rangeStart e.g. 2012-02-10 (YYYY-mm-dd) or last5(lastX), previous12(previousY)...
     * @param string|null $rangeEnd e.g. 2012-02-12. Leave this parameter empty to request all data from
     *                         $rangeStart until now
     * @return $this
     */
    public function setRange(string|Carbon|DateTime $rangeStart = null, string|Carbon|DateTime $rangeEnd = null): ReportingAPI
    {
        // Normalize the inputs
        if (!is_null($rangeStart)) {
            $rangeStart = Carbon::parse($rangeStart)->format('Y-m-d');
        }
        if (!is_null($rangeEnd)) {
            $rangeEnd = Carbon::parse($rangeEnd)->format('Y-m-d');
        }

        // Set the required filter values
        $this->setPeriod(static::PERIOD_RANGE);
        $this->date = '';
        $this->rangeStart = $rangeStart;
        $this->rangeEnd = $rangeEnd;

        if (is_null($rangeEnd)) {
            if (
                Str::startsWith($rangeStart, 'last')
                || Str::startsWith($rangeStart, 'previous')
            ) {
                $this->setDate($rangeStart);
            } else {
                $this->rangeEnd = static::DATE_TODAY;
            }
        }

        return $this;
    }

    /**
     * Get the currently set date property value
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * Set the date property value
     */
    public function setDate(string $value): ReportingAPI
    {
        $this->date = $value;
        $this->rangeStart = null;
        $this->rangeEnd = null;

        return $this;
    }

    /**
     * Get the currently set format property value
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * Set the format property value
     */
    public function setFormat(string $value): ReportingAPI
    {
        $this->format = $value;
        return $this;
    }

    /**
     * Get the currently set filterLimit property value
     */
    public function getFilterLimit(): int
    {
        return $this->filterLimit;
    }

    /**
     * Set the filterLimit property value
     */
    public function setFilterLimit(int $value): ReportingAPI
    {
        $this->filterLimit = $value;
        return $this;
    }

    /**
     * Get the currently set language property value
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * Set the language property value
     */
    public function setLanguage(string $value): ReportingAPI
    {
        $this->language = $value;
        return $this;
    }

    /**
     * Set the cache TTL
     */
    public function setCacheTtl(int $value): ReportingAPI
    {
        $this->cacheTtl = $value;
        return $this;
    }

    //
    // Main Methods
    //

    /**
     * Reset all filter properties
     */
    public function reset(): ReportingAPI
    {
        $this->date = static::DATE_LAST_MONTH;
        $this->period = static::PERIOD_MONTH;
        $this->rangeStart = null;
        $this->rangeEnd = null;

        return $this;
    }

    /**
     * Get a cloned instance of the API to make requests without
     * affecting the filters on the current instance
     */
    public function clone(): ReportingAPI
    {
        return clone $this;
    }

    /**
     * Make a call to the Matomo Reporting API
     * @return mixed
     */
    public function call(string $method, array $params = [], bool $bypassCache = false)
    {
        $defaultParams = [
            'module' => 'API',
            'method' => $method,
            'token_auth' => $this->authToken,
            'idSite' => $this->siteId,
            'period' => $this->period,
            'format' => ($this->format === 'php' ? 'json' : $this->format),
            'date'   => $this->date,
            'filter_limit' => $this->filterLimit,
            'language' => $this->language,
        ];

        if (!empty($this->rangeStart) && !empty($this->rangeEnd)) {
            $defaultParams['date'] = $this->getRange();
        } elseif (!empty($this->date)) {
            $defaultParams['date'] = $this->date;
        } else {
            throw new InvalidArgumentException('Specify a date or a date range!');
        }

        $url = UrlGenerator::buildUrl($this->serverUrl, [
            'query' => array_merge($defaultParams, $params),
        ]);

        $cacheKey = 'winter.matomo.reporting_api.request.' . md5($url);

        if ($bypassCache) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($url) {
            $response = Http::get($url);

            if (empty($response->body) || $response->code !== 200) {
                throw new SystemException("The API call failed.");
            }

            $data = $response->body;
            if ($this->format === 'php') {
                $data = json_decode($data, flags: JSON_THROW_ON_ERROR);

                if (isset($data->result) && $data->result === 'error') {
                    throw new SystemException("The API call failed with the following message: {$data->message}");
                }
            }

            return $data;
        });
    }

    //
    // Helper methods
    //

    /**
     * Get an event's action ID (idSubtable) from its label. Returns 0 if the requested
     * event action could not be found with the current filters
     */
    public function getEventActionId(string $label): int
    {
        // The idsubdatatable is based on the current filters applied so this call
        // must be made with the same filters as any subsequent calls
        $actions = $this->call('Events.getAction', ['showColumns' => 'label,idsubdatatable']);

        foreach ($actions as $action) {
            if ($action->label === $label) {
                return (int) $action->idsubdatatable;
            }
        }

        return 0;
    }

    /**
     * Get event names from the action ID
     */
    public function getEventNamesFromActionId(int $idSubtable, array $params = []): array
    {
        if ($idSubtable === 0) {
            return [];
        }
        return $this->call('Events.getNameFromActionId', array_merge($params, ['idSubtable' => $idSubtable]));
    }

    /**
     * Get the event names in the current reporting period associated with the provided action label
     */
    public function getEventNamesFromAction(string $label, array $params = []): array
    {
        return $this->getEventNamesFromActionId($this->getEventActionId($label), $params);
    }

    /**
     * Get an event's category ID (idSubtable) from its label. Returns 0 if the requested
     * event action could not be found with the current filters
     */
    public function getEventCategoryId(string $label): int
    {
        // The idsubdatatable is based on the current filters applied so this call
        // must be made with the same filters as any subsequent calls
        $categories = $this->call('Events.getCategory', ['showColumns' => 'label,idsubdatatable']);

        foreach ($categories as $category) {
            if ($category->label === $label) {
                return (int) $category->idsubdatatable;
            }
        }

        return 0;
    }

    /**
     * Get event names from the category ID
     */
    public function getEventNamesFromCategoryId(int $idSubtable, array $params = []): array
    {
        if ($idSubtable === 0) {
            return [];
        }
        return $this->call('Events.getNameFromCategoryId', array_merge($params, ['idSubtable' => $idSubtable]));
    }

    /**
     * Get the event names in the current reporting period associated with the provided category label
     */
    public function getEventNamesFromCategory(string $label, array $params = []): array
    {
        return $this->getEventNamesFromCategoryId($this->getEventCategoryId($label), $params);
    }
}
