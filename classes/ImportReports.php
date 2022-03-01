<?php namespace Winter\Matomo;

use Winter\Storm\Router\UrlGenerator;

/**
 * WIP Class that interacts with the Matomo API to import available reports
 */
class ImportReports
{
    /**
     * @var string The Matomo instance to send requests to
     */
    protected $url;

    /**
     * @var string The Site ID to send requests for
     */
    protected $siteId;

    /**
     * @var string The token to use to authenticate requests
     */
    protected $token = '';

    /**
     * @var array The list of defined reports
     */
    protected $definedReports = [];

    public function __construct(string $url, string $siteId, string $token)
    {
        $this->url = $url;
        $this->siteId = $siteId;
        $this->token = $token;
    }

    public function makeRequest(array $params): string
    {
        $matomoServer = rtrim($this->url, '/');
        $siteId = $this->siteId;
        $authToken = $this->token;

        $url = UrlGenerator::buildUrl("$matomoServer/index.php", [
            'query' => array_merge([
                'module'     => 'API',
                'format'     => 'json',
                'idSite'     => $siteId,
                'token_auth' => $authToken,
            ], $params),
        ]);

        return file_get_contents($url);
    }

    /**
     * Get the description for the provided $module and $action
     */
    public function reportDocumentation(string $module, string $action, string $description = ""): string
    {
        $reportJson = $this->makeRequest([
            'method' => 'API.getProcessedReport',
            'date'   => 'yesterday',
            'period' => 'day',
            'apiModule' => $module,
            'apiAction' => $action,
            'filter_truncate' => '5',
            'language' => 'en',
        ]);

        $report = json_decode($reportJson, true);

        return $report["metadata"]["documentation"] ?? $description;
    }

    public function report($i, $tc, $ts, $tt, $d, $p)
    {
        static $cnt=0;
        $cnt += 1;

        $d = $this->reportDocumentation($p["module"], $p["action"], $d);
        if (strlen($ts)) {
            $ts = " - $ts";
        }
        if (strlen($tt)) {
            $tt = " - $tt";
        }

        $output = $i . ":\n";
        $output .= "   t: \"" . ($t=ucwords("$tc$ts$tt")) . "\"\n";
        $output .= "   d: \"" . (trim(strip_tags(str_replace("\"", "'", @$d)))) . "\"\n";
        $output .= "   m: \"" . $p["module"] . "\"\n";
        $output .= "   a: \"" . $p["action"] . "\"\n";

        $extra= "";
        foreach ($p as $key => $value) {
            switch($key) {
                case "module":
                case "action":
                break;
            default:
                $extra .= "&$key=$value";
            }
        }

        if (strlen($extra)) {
            $output .= "   e: \"" . $extra . "\"\n";
        }

        $output .= "\n";

        $this->definedReports[] = [
            "title" => ucwords("$tc$ts$tt"),
            "value" => $output,
        ];
    }

    public function getReports()
    {
        $json = $this->makeRequest([
            'method' => 'API.getReportPagesMetadata',
        ]);

        $reports = json_decode($json, true);

        foreach ($reports as &$report) {
            switch ($report["category"]["name"]) {
                case "About Matomo":
                case "Marketplace":
                case "Dashboard":
                    break;
                default:
                    // $this->report($report["uniqueId"], $report["category"]["name"], "", $report["name"], $report["category"]["help"], $report["parameters"]);
                    if (array_key_exists("widgets", $report)) {
                        foreach ($report["widgets"] as &$widget) {
                            $this->report(
                                $widget["uniqueId"],
                                $report["category"]["name"],
                                $report["subcategory"]["name"],
                                $widget["name"],
                                $report["subcategory"]["help"],
                                $widget["parameters"]
                            );
                            $this->definedReports[$widget["uniqueId"]] = true;
                        }
                    }
            }
        }
    }

    public function dunno()
    {
        $json = $this->makeRequest([
            'method' => 'API.getWidgetMetadata',
        ]);

        $reports = json_decode($json, true);

        foreach ($reports as &$report) {
            if (array_key_exists($report["uniqueId"], $this->definedReports)) {
                continue;
            }

            switch ($report["category"]["name"]) {
                case "About Matomo":
                case "Marketplace":
                    break;
                default:
                    $this->report(
                        $report["uniqueId"],
                        $report["category"]["name"],
                        $report["subcategory"]["name"],
                        $report["name"],
                        $report["category"]["help"] . " " . $report["subcategory"]["help"],
                        $report["parameters"]
                    );

                    if (array_key_exists("widgets", $report)) {
                        foreach ($report["widgets"] as &$widget) {
                            if (array_key_exists($widget["uniqueId"], $this->definedReports)) {
                                continue;
                            }

                            $this->report(
                                $widget["uniqueId"],
                                "WW " . $widget["category"]["name"],
                                $widget["subcategory"]["name"],
                                $widget["name"],
                                $widget["category"]["help"] . " " . $widget["subcategory"]["help"],
                                $widget["parameters"]
                            );
                        }
                    }
            }
        }
    }

    public function echoReports()
    {
        usort($this->definedReports, function ($item1, $item2) {
            return strcmp($item1['title'], $item2['title']);
        });

        foreach ($this->definedReports as &$report) {
            echo $report["value"];
        }
    }
}
