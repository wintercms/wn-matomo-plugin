<?php namespace Winter\Matomo\Components;

use Config;
use BackendAuth;
use Cms\Classes\ComponentBase;

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
        if (BackendAuth::getUser()) {
            $this->enabled = false;
        }
    }
}
