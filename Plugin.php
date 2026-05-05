<?php

namespace Winter\Matomo;

use Illuminate\Support\Facades\Config;
use System\Classes\PluginBase;
use Winter\Matomo\Classes\MatomoReportingService;
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
    public function pluginDetails(): array
    {
        return [
            'name'        => 'winter.matomo::lang.plugin.name',
            'description' => 'winter.matomo::lang.plugin.description',
            'author'      => 'Winter CMS',
            'icon'        => 'icon-area-chart',
        ];
    }

    public function boot(): void
    {
        $this->app->scoped(MatomoReportingService::class, function () {
            return new MatomoReportingService(
                Config::get('winter.matomo::server'),
                Config::get('winter.matomo::auth_token'),
                (int) Config::get('winter.matomo::site_id'),
                (int) Config::get('winter.matomo::cache_ttl', 900),
                (int) Config::get('winter.matomo::http_timeout', 10),
                (bool) Config::get('winter.matomo::verify_ssl', true)
            );
        });

        $this->app->scoped(ReportingAPI::class, function () {
            $api = new ReportingAPI(
                Config::get('winter.matomo::server'),
                Config::get('winter.matomo::auth_token'),
                Config::get('winter.matomo::site_id')
            );

            $api->setCacheTtl((int) Config::get('winter.matomo::reportingapi_cache_ttl', Config::get('winter.matomo::cache_ttl', 900)));

            return $api;
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
            \Winter\Matomo\ReportWidgets\VisitsSummary::class => [
                'label' => 'winter.matomo::lang.reportwidgets.visits_summary.label',
                'context' => 'dashboard',
                'permissions' => [
                    'winter.matomo.site.view',
                ],
            ],
            \Winter\Matomo\ReportWidgets\VisitsOverTime::class => [
                'label' => 'winter.matomo::lang.reportwidgets.visits_over_time.label',
                'context' => 'dashboard',
                'permissions' => [
                    'winter.matomo.site.view',
                ],
            ],
            \Winter\Matomo\ReportWidgets\TopPages::class => [
                'label' => 'winter.matomo::lang.reportwidgets.top_pages.label',
                'context' => 'dashboard',
                'permissions' => [
                    'winter.matomo.site.view',
                ],
            ],
            \Winter\Matomo\ReportWidgets\Referrers::class => [
                'label' => 'winter.matomo::lang.reportwidgets.referrers.label',
                'context' => 'dashboard',
                'permissions' => [
                    'winter.matomo.site.view',
                ],
            ],
        ];
    }
}
