<?php

namespace Winter\Matomo\Classes\Exceptions;

/**
 * Thrown when Matomo credentials are invalid or rejected.
 */
class MatomoAuthenticationException extends MatomoReportingException
{
    public function errorCode(): string
    {
        return 'matomo_authentication_error';
    }

    public function userMessageKey(): string
    {
        return 'winter.matomo::lang.reportwidgets.visits_summary.errors.authentication';
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
