<?php namespace Winter\Matomo\Components;

use Config;
use BackendAuth;
use Cms\Classes\ComponentBase;
use Cms\Models\MaintenanceSetting;

/**
 * Tracker component
 */
class Tracker extends ComponentBase
{
    /**
     * @var string The URL to the Matomo instance to send tracking data to
     */
    public $serverUrl = '';

    /**
     * @var string the Site ID to send tracking data for
     */
    public $siteId = '';

    /**
     * @var bool Flag for the current state of the tracker component
     */
    public $enabled = true;

    /**
     * Get the component's details
     */
    public function componentDetails(): array
    {
        return [
            'name'        => 'winter.matomo::lang.components.tracker.name',
            'description' => 'winter.matomo::lang.components.tracker.description',
            'author'      => 'Winter CMS',
            'icon'        => 'icon-area-chart',
        ];
    }

    public function onRun(): void
    {
        $this->serverUrl = rtrim(Config::get('winter.matomo::server'), '/');
        $this->siteId = Config::get('winter.matomo::site_id');

        // Disable the tracker when authenticated backend users are detected
        if (
            !Config::get('winter.matomo::track_backend_users', false)
            && BackendAuth::getUser()
        ) {
            $this->enabled = false;
        }

        // Disable the tracker when in maintenance mode
        if (
            !Config::get('winter.matomo::track_maintenance_mode', false)
            && $this->isMaintenanceModeEnabled()
            // && (
            //     app()->maintenanceMode()->active()
            //     || $this->isMaintenanceModeEnabled()
            // )
        ) {
            $this->enabled = false;
        }
    }

    /**
     * isMaintenanceModeEnabled will check if maintenance mode is currently enabled.
     * Static page logic should be disabled when this occurs.
     */
    protected function isMaintenanceModeEnabled(): bool
    {
        return MaintenanceSetting::isConfigured() &&
            MaintenanceSetting::get('is_enabled', false) &&
            !BackendAuth::getUser();
    }
}
