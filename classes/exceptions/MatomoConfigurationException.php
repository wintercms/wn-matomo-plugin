<?php

namespace Winter\Matomo\Classes\Exceptions;

/**
 * Thrown when Matomo reporting configuration is incomplete or invalid.
 */
class MatomoConfigurationException extends MatomoReportingException
{
    public function errorCode(): string
    {
        return 'matomo_configuration_error';
    }

    public function userMessageKey(): string
    {
        return 'winter.matomo::lang.reportwidgets.errors.configuration';
    }

    public function isRetryable(): bool
    {
        return false;
    }

    public function severity(): string
    {
        return 'warning';
    }
}
