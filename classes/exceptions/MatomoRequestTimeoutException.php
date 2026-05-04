<?php

namespace Winter\Matomo\Classes\Exceptions;

/**
 * Thrown when a Matomo API request times out or cannot connect.
 */
class MatomoRequestTimeoutException extends MatomoReportingException
{
    public function errorCode(): string
    {
        return 'matomo_request_timeout';
    }

    public function userMessageKey(): string
    {
        return 'winter.matomo::lang.reportwidgets.visits_summary.errors.timeout';
    }

    public function isRetryable(): bool
    {
        return true;
    }

    public function severity(): string
    {
        return 'warning';
    }
}
