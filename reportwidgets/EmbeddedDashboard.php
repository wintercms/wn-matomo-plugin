<?php namespace Winter\Matomo\ReportWidgets;

use Url;
use Lang;
use Config;
use Throwable;
use Backend\Classes\ReportWidgetBase;

/**
 * Embedded Dashboard Report Widget
 */
class EmbeddedDashboard extends ReportWidgetBase
{
    /**
     * @var string The default alias to use for this widget
     */
    protected $defaultAlias = 'EmbeddedMatomoDashboardReportWidget';

    /**
     * Defines the widget's properties
     */
    public function defineProperties(): array
    {
        return [
            'title' => [
                'title'    => 'backend::lang.dashboard.widget_title_label',
                'description' => 'winter.core::lang.reportwidgets.general.title_desc',
                'type'     => 'string',
                'default'  => 'winter.matomo::lang.reportwidgets.embedded_dashboard.title_default',
                'required' => true,
            ],
            'period' => [
                'title' => 'winter.matomo::lang.reportwidgets.embedded_dashboard.period',
                'description' => 'winter.matomo::lang.reportwidgets.embedded_dashboard.period_desc',
                'type' => 'dropdown',
                'options'     => [
                    'last7'   => Lang::get('winter.matomo::lang.reportwidgets.embedded_dashboard.period_last_x_days', ['x' => 7]),
                    'last30'  => Lang::get('winter.matomo::lang.reportwidgets.embedded_dashboard.period_last_x_days', ['x' => 30]),
                    'last360' => Lang::get('winter.matomo::lang.reportwidgets.embedded_dashboard.period_last_x_days', ['x' => 360]),
                ],
                'default' => 'last30',
                'required' => true,
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

        $this->vars['iframeUrl'] = Url::buildUrl("$matomoServer/index.php", [
            'query' => [
                'date'              => $this->property('period'),
                'idSite'            => $siteId,
                'token_auth'        => $authToken,
                'module'            => 'Widgetize',
                'action'            => 'iframe',
                'moduleToWidgetize' => 'Dashboard',
                'actionToWidgetize' => 'index',
                'period'            => 'range',
            ],
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
