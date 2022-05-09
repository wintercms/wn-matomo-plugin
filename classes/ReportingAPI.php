<?php namespace Winter\Matomo\Classes;

use Cache;
use Winter\Storm\Router\UrlGenerator;

/**
 * Matomo Reporting API Simple PHP Wrapper
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

    /**
     * The currently set period
     */
    protected string $period = static::PERIOD_MONTH;

    /**
     * The currently set date
     */
    protected string $date = static::DATE_LAST_MONTH;

    /**
     * The currently set format
     */
    protected string $format = 'json';

    /**
     * The currently set filter limit
     */
    protected int $filterLimit = 100;

    /**
     * The currently set language to request
     */
    protected string $language = 'en';

    /**
     * Create a new instance
     */
    public function __construct(string $serverUrl, string $authToken, int $siteId = null)
    {
        $this->serverUrl = $serverUrl;
        $this->authToken = $authToken;
        $this->siteId = $siteId;
    }

    /**
     * Reset all filter properties
     */
    public function reset(): ReportingAPI
    {
        $this->date = static::DATE_LAST_MONTH;
        $this->period = static::PERIOD_MONTH;

        return $this;
    }

    /**
     * Make a call to the Matomo Reporting API
     */
    public function call(string $method, array $params = [])
    {
        $url = UrlGenerator::buildUrl($this->serverUrl, [
            'query' => array_merge([
                'module' => 'API',
                'method' => $method,
                'token_auth' => $this->authToken,
                'idSite' => $this->siteId,
                'period' => $this->period,
                'format' => $this->format,
                'filter_limit' => $this->filterLimit,
                'language' => $this->language,
            ], $params),
        ]);

        // @TODO: Finish implementing class
    }
}
