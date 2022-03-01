<?php namespace Winter\Matomo\ReportWidgets;

use Url;
use Arr;
use Lang;
use Yaml;
use Config;
use Backend\Classes\ReportWidgetBase;

/**
 * Embedded Matomo Widget Report Widget
 */
class EmbeddedWidget extends ReportWidgetBase
{
    /**
     * @var string The default alias to use for this widget
     */
    protected $defaultAlias = 'EmbeddedMatomoWidgetReportWidget';

    /**
     * Defines the widget's properties
     */
    public function defineProperties(): array
    {
        $reportOptions = [];
        $availableReports = Yaml::parseFile(plugins_path('winter/matomo/reportwidgets/embeddedwidget/reports.yaml'));
        foreach ($availableReports as $reportId => $config) {
            $reportOptions[$reportId] = Lang::get("winter.matomo::lang.reportwidgets.embedded_widget.reports.{$reportId}.title");
        }

        return [
            'report' => [
                'title' => 'winter.matomo::lang.reportwidgets.embedded_widget.report',
                'description' => 'winter.matomo::lang.reportwidgets.embedded_widget.report_desc',
                'type' => 'dropdown',
                'options' => $reportOptions,
                'default' => array_key_first($reportOptions),
                'required' => true,
            ],
            'period' => [
                'title' => 'winter.matomo::lang.reportwidgets.general.period',
                'description' => 'winter.matomo::lang.reportwidgets.general.period_desc',
                'type' => 'dropdown',
                'options'     => [
                    'last7'   => Lang::get('winter.matomo::lang.reportwidgets.general.period_last_x_days', ['x' => 7]),
                    'last30'  => Lang::get('winter.matomo::lang.reportwidgets.general.period_last_x_days', ['x' => 30]),
                    'last360' => Lang::get('winter.matomo::lang.reportwidgets.general.period_last_x_days', ['x' => 360]),
                ],
                'default' => 'last30',
                'required' => true,
            ],
            'displayAs' => [
                'title' => 'winter.matomo::lang.reportwidgets.embedded_widget.displayAs',
                'description' => 'winter.matomo::lang.reportwidgets.embedded_widget.displayAs_desc',
                'type' => 'dropdown',
                'options'     => [
                    'default' => Lang::get('winter.matomo::lang.reportwidgets.embedded_widget.displayAs_options.default'),
                    'table' => Lang::get('winter.matomo::lang.reportwidgets.embedded_widget.displayAs_options.table'),
                    'tableAllColumns' => Lang::get('winter.matomo::lang.reportwidgets.embedded_widget.displayAs_options.tableAllColumns'),
                    'tableGoals' => Lang::get('winter.matomo::lang.reportwidgets.embedded_widget.displayAs_options.tableGoals'),
                    'cloud' => Lang::get('winter.matomo::lang.reportwidgets.embedded_widget.displayAs_options.cloud'),
                    'graphPie' => Lang::get('winter.matomo::lang.reportwidgets.embedded_widget.displayAs_options.graphPie'),
                    'graphVerticalBar' => Lang::get('winter.matomo::lang.reportwidgets.embedded_widget.displayAs_options.graphVerticalBar'),
                    'graphEvolution' => Lang::get('winter.matomo::lang.reportwidgets.embedded_widget.displayAs_options.graphEvolution'),
                ],
                'default' => 'default',
                'required' => true,
            ],
            'rows' => [
                'title' => 'winter.matomo::lang.reportwidgets.embedded_widget.rows',
                'description' => 'winter.matomo::lang.reportwidgets.embedded_widget.rows_desc',
                'type' => 'dropdown',
                'options'     => [
                    '5' => '5',
                    '10' => '10',
                    '25' => '25',
                    '50' => '50',
                    '100' => '100',
                    '500' => '500',
                    'all' => 'all'
                ],
                'default' => '10',
                'required' => false,
            ],
        ];
    }

    /**
     * Adds widget specific asset files. Use $this->addJs() and $this->addCss()
     * to register new assets to include on the page.
     */
    protected function loadAssets(): void
    {
        $this->addJs('/plugins/winter/matomo/assets/js/iFrameResizer.js');
    }

    /**
     * Prepares the report widget view data
     */
    public function prepareVars(): void
    {
        // @TODO: Store this in config, should be changed on a per region basis\
        //        controlled by the clients choice of region
        $matomoServer = rtrim(Config::get('winter.matomo::server'), '/');

        // @TODO: Store this either with the user project connection or with the project
        // since it is shared with anyone who access to this widget and changing it would be
        // annoying, and we need to make sure that the token has limited access, we should probably
        // sync project users between matomo & the project
        $authToken = Config::get('winter.matomo::auth_token');

        // @TODO: Pull from the project
        $siteId = Config::get('winter.matomo::site_id');

        $reportId = $this->property('report');
        $reportConfig = Arr::get(
            Yaml::parseFile(plugins_path('winter/matomo/reportwidgets/embeddedwidget/reports.yaml')),
            $reportId,
        );

        if (empty($reportConfig)) {
            throw new \Exception("The $reportId report is not supported.");
        }

        // Adjust the title
        $this->vars['title'] = Lang::get("winter.matomo::lang.reportwidgets.embedded_widget.reports.$reportId.title");
        $this->vars['description'] = Lang::get("winter.matomo::lang.reportwidgets.embedded_widget.reports.$reportId.description");

        // Generate the base data required for the iframe URL
        $query = [
            'date'              => $this->property('period'),
            'idSite'            => $siteId,
            'token_auth'        => $authToken,
            'module'            => 'Widgetize',
            'action'            => 'iframe',
            'disableLink'       => 1,
            'widget'            => 1,
            'moduleToWidgetize' => $reportConfig['module'],
            'actionToWidgetize' => $reportConfig['action'],
            'period'            => 'range',
            'filterLimit'       => $this->property('rows', 10),
        ];

        // Modify the display of the widget if requested
        if ($this->property('displayAs', 'default') !== 'default') {
            $query['viewDataTable'] = $this->property('displayAs');
        }

        // Merge any extra paramaters required for the selected widget
        if (!empty($reportConfig['extraParams'])) {
            $extra = [];
            parse_str($reportConfig['extraParams'], $extra);
            array_merge($query, $extra);
        }

        $this->vars['iframeUrl'] = Url::buildUrl("$matomoServer/index.php", [
            'query' => $query,
        ]);
    }

    /**
     * Renders the widget's primary contents.
     */
    public function render(): string
    {
        try {
            $this->prepareVars();
        } catch (Throwable $ex) {
            $this->vars['error'] = $ex->getMessage();
        }

        return $this->makePartial('widget');
    }
}
