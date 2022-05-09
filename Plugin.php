<?php namespace Winter\Matomo;

use Config;
use System\Classes\PluginBase;
use Winter\Matomo\Classes\ReportingAPI;

/**
 * Matomo Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'winter.matomo::lang.plugin.name',
            'description' => 'winter.matomo::lang.plugin.description',
            'author'      => 'Winter CMS',
            'icon'        => 'icon-area-chart'
        ];
    }

    public function boot()
    {
        $this->app->scoped(ReportingAPI::class, function () {
            return new ReportingAPI(
                Config::get('winter.matomo::server'),
                Config::get('winter.matomo::auth_token'),
                Config::get('winter.matomo::site_id')
            );
        });
    }

    /**
     * Registers any frontend components implemented in this plugin.
     */
    public function registerComponents(): array
    {
        return [
            \Winter\Matomo\Components\Tracker::class => 'matomoTracker',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return [
            'winter.matomo.site.view' => [
                'tab' => 'winter.matomo::lang.plugin.name',
                'label' => 'winter.matomo::lang.permissions.site.view',
            ],
        ];
    }

    /**
     * Registers ReportWidgets provided by this plugin
     */
    public function registerReportWidgets(): array
    {
        return [
            \Winter\Matomo\ReportWidgets\EmbeddedDashboard::class => [
                'label' => 'winter.matomo::lang.reportwidgets.embedded_dashboard.title_default',
                'context' => 'dashboard',
                'permissions' => [
                    'winter.matomo.site.view',
                ],
            ],
            \Winter\Matomo\ReportWidgets\EmbeddedWidget::class => [
                'label' => 'winter.matomo::lang.reportwidgets.embedded_widget.label',
                'context' => 'dashboard',
                'permissions' => [
                    'winter.matomo.site.view',
                ],
            ],
        ];
    }
}
