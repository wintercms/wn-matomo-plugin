<?php

namespace Winter\Matomo\Classes\Exceptions;

/**
 * Thrown when Matomo reporting configuration is incomplete or invalid.
 */
class MatomoConfigurationException extends MatomoReportingException
{
    protected const ERROR_CODE = 'matomo_configuration_error';
    protected const USER_MESSAGE_KEY = 'winter.matomo::lang.reportwidgets.errors.configuration';
    protected const IS_RETRYABLE = false;
    protected const SEVERITY = 'warning';
}
